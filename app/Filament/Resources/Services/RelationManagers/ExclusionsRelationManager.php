<?php

namespace App\Filament\Resources\Services\RelationManagers;

use App\Models\Service;
use App\Models\ServiceExclusion;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class ExclusionsRelationManager extends RelationManager
{
    protected static string $relationship = 'exclusions';

    protected static ?string $title = 'Exclusions';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('task')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('note')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->description(null)
            ->paginated(false)
            ->columns([
                TextColumn::make('task')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('note')
                    ->limit(40)
                    ->wrap()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->toolbarActions([
                Action::make('addFromLibrary')
                    ->label('Add from library')
                    ->icon('heroicon-o-queue-list')
                    ->modalHeading('Add exclusions from library')
                    ->modalDescription('Tick tasks already used on any service. Matching notes are copied; edit after if needed.')
                    ->modalWidth(Width::FiveExtraLarge)
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalSubmitActionLabel('Add selected')
                    ->visible(fn (): bool => $this->libraryOptions()->isNotEmpty())
                    ->fillForm(fn (): array => ['tasks' => []])
                    ->form([
                        CheckboxList::make('tasks')
                            ->label('Existing exclusion tasks')
                            ->options(fn (): array => $this->libraryOptions()->all())
                            ->descriptions(fn (): array => $this->libraryNotes()->all())
                            ->searchable()
                            ->bulkToggleable()
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->extraAttributes([
                                'class' => 'max-h-[min(28rem,55vh)] overflow-y-auto overscroll-contain pe-1',
                            ])
                            ->required()
                            ->helperText('Already attached to this service are hidden. Scroll the list on smaller laptop screens.'),
                    ])
                    ->action(function (array $data): void {
                        /** @var Service $service */
                        $service = $this->getOwnerRecord();
                        $selected = array_values(array_filter(
                            array_map('strval', $data['tasks'] ?? []),
                        ));

                        if ($selected === []) {
                            return;
                        }

                        $existing = $service->exclusions()
                            ->pluck('task')
                            ->map(fn (string $task): string => mb_strtolower(trim($task)))
                            ->all();

                        $notesByTask = $this->libraryNotesByTask();
                        $sort = (int) ($service->exclusions()->max('sort_order') ?? 0);
                        $added = 0;

                        foreach ($selected as $task) {
                            $task = trim($task);

                            if ($task === '') {
                                continue;
                            }

                            if (in_array(mb_strtolower($task), $existing, true)) {
                                continue;
                            }

                            $sort++;
                            $service->exclusions()->create([
                                'task' => $task,
                                'note' => $notesByTask[mb_strtolower($task)] ?? null,
                                'sort_order' => $sort,
                            ]);
                            $existing[] = mb_strtolower($task);
                            $added++;
                        }

                        Notification::make()
                            ->title($added === 1 ? '1 exclusion added' : "{$added} exclusions added")
                            ->success()
                            ->send();
                    }),
                CreateAction::make()
                    ->label('Add exclusion')
                    ->modalHeading('Add exclusion')
                    ->modalWidth(Width::Medium)
                    ->createAnother(false)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sort_order'] = (int) ($this->getOwnerRecord()->exclusions()->max('sort_order') ?? 0) + 1;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit exclusion')
                    ->modalWidth(Width::Medium),
                DeleteAction::make(),
            ]);
    }

    /**
     * @return Collection<string, string>
     */
    private function libraryOptions(): Collection
    {
        /** @var Service $service */
        $service = $this->getOwnerRecord();

        $alreadyOnService = $service->exclusions()
            ->pluck('task')
            ->map(fn (string $task): string => mb_strtolower(trim($task)))
            ->all();

        return ServiceExclusion::query()
            ->orderBy('task')
            ->get(['task', 'note'])
            ->unique(fn (ServiceExclusion $exclusion): string => mb_strtolower(trim($exclusion->task)))
            ->reject(fn (ServiceExclusion $exclusion): bool => in_array(
                mb_strtolower(trim($exclusion->task)),
                $alreadyOnService,
                true,
            ))
            ->mapWithKeys(function (ServiceExclusion $exclusion): array {
                $task = trim($exclusion->task);

                return [$task => $task];
            });
    }

    /**
     * @return Collection<string, string>
     */
    private function libraryNotes(): Collection
    {
        return $this->libraryOptions()
            ->mapWithKeys(function (string $task): array {
                $note = $this->libraryNotesByTask()[mb_strtolower($task)] ?? null;

                return [$task => filled($note) ? (string) $note : 'No note stored yet'];
            });
    }

    /**
     * @return array<string, string|null>
     */
    private function libraryNotesByTask(): array
    {
        $notes = [];

        foreach (ServiceExclusion::query()->orderBy('id')->get(['task', 'note']) as $exclusion) {
            $key = mb_strtolower(trim($exclusion->task));

            if (! array_key_exists($key, $notes)) {
                $notes[$key] = $exclusion->note;
            }
        }

        return $notes;
    }
}
