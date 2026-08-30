<?php

namespace App\Filament\Resources\Services\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqsRelationManager extends RelationManager
{
    protected static string $relationship = 'faqs';

    protected static ?string $title = 'FAQs';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('question')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('answer')
                ->required()
                ->rows(3)
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
                TextColumn::make('question')
                    ->searchable()
                    ->wrap()
                    ->limit(80),
                TextColumn::make('answer')
                    ->searchable()
                    ->wrap()
                    ->limit(120),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->toolbarActions([
                CreateAction::make()
                    ->label('Add FAQ')
                    ->modalHeading('Add FAQ')
                    ->modalWidth(Width::Medium)
                    ->createAnother(false)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sort_order'] = (int) ($this->getOwnerRecord()->faqs()->max('sort_order') ?? 0) + 1;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit FAQ')
                    ->modalWidth(Width::Medium),
                DeleteAction::make(),
            ]);
    }
}
