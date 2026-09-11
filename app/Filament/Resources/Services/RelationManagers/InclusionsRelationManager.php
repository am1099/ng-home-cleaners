<?php

namespace App\Filament\Resources\Services\RelationManagers;

use App\Models\Service;
use App\Models\ServiceInclusion;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class InclusionsRelationManager extends RelationManager
{
    protected static string $relationship = 'inclusions';

    protected static ?string $title = 'Inclusions';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('content')
                ->label('Item')
                ->required()
                ->maxLength(255)
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
                TextColumn::make('content')
                    ->label('Item')
                    ->searchable()
                    ->wrap(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->toolbarActions([
                Action::make('addFromLibrary')
                    ->label('Add from library')
                    ->icon('heroicon-o-queue-list')
                    ->modalHeading('Add inclusions from library')
                    ->modalDescription('Tick checklist items already used on any service. New wording still uses “Add inclusion”.')
                    ->modalWidth(Width::Large)
                    ->modalSubmitActionLabel('Add selected')
                    ->visible(fn (): bool => $this->libraryOptions()->isNotEmpty())
                    ->fillForm(fn (): array => ['items' => []])
                    ->form([
                        CheckboxList::make('items')
                            ->label('Existing checklist items')
                            ->options(fn (): array => $this->libraryOptions()->all())
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(1)
                            ->required()
                            ->helperText('Already attached to this service are hidden.'),
                    ])
                    ->action(function (array $data): void {
                        /** @var Service $service */
                        $service = $this->getOwnerRecord();
                        $selected = array_values(array_filter(
                            array_map('strval', $data['items'] ?? []),
                        ));

                        if ($selected === []) {
                            return;
                        }

                        $existing = $service->inclusions()
                            ->pluck('content')
                            ->map(fn (string $content): string => mb_strtolower(trim($content)))
                            ->all();

                        $sort = (int) ($service->inclusions()->max('sort_order') ?? 0);
                        $added = 0;

                        foreach ($selected as $content) {
                            $content = trim($content);

                            if ($content === '') {
                                continue;
                            }

                            if (in_array(mb_strtolower($content), $existing, true)) {
                                continue;
                            }

                            $sort++;
                            $service->inclusions()->create([
                                'content' => $content,
                                'sort_order' => $sort,
                            ]);
                            $existing[] = mb_strtolower($content);
                            $added++;
                        }

                        Notification::make()
                            ->title($added === 1 ? '1 inclusion added' : "{$added} inclusions added")
                            ->success()
                            ->send();
                    }),
                CreateAction::make()
                    ->label('Add inclusion')
                    ->modalHeading('Add inclusion')
                    ->modalWidth(Width::Medium)
                    ->createAnother(false)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sort_order'] = (int) ($this->getOwnerRecord()->inclusions()->max('sort_order') ?? 0) + 1;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit inclusion')
                    ->modalWidth(Width::Medium),
                DeleteAction::make(),
            ]);
    }

    /**
     * Unique inclusion wording from all services, excluding items already on this service.
     *
     * @return Collection<string, string>
     */
    private function libraryOptions(): Collection
    {
        /** @var Service $service */
        $service = $this->getOwnerRecord();

        $alreadyOnService = $service->inclusions()
            ->pluck('content')
            ->map(fn (string $content): string => mb_strtolower(trim($content)))
            ->all();

        return ServiceInclusion::query()
            ->orderBy('content')
            ->pluck('content')
            ->map(fn (string $content): string => trim($content))
            ->filter()
            ->unique(fn (string $content): string => mb_strtolower($content))
            ->reject(fn (string $content): bool => in_array(mb_strtolower($content), $alreadyOnService, true))
            ->mapWithKeys(fn (string $content): array => [$content => $content]);
    }
}
