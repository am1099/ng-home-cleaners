<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Enums\EmailTemplateKey;
use App\Models\EmailTemplate;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Template')
                ->schema([
                    TextInput::make('name')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('key')
                        ->label('System key')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn (mixed $state): string => $state instanceof EmailTemplateKey
                            ? $state->value
                            : (string) $state),
                    Placeholder::make('description')
                        ->label('When it sends')
                        ->content(fn (?EmailTemplate $record): string => $record?->description ?? '—'),
                    Placeholder::make('placeholders')
                        ->label('Available placeholders')
                        ->content(function (?EmailTemplate $record): string {
                            $key = $record?->key;

                            if (! $key instanceof EmailTemplateKey) {
                                return '—';
                            }

                            return collect($key->placeholders())
                                ->map(fn (string $placeholder): string => '{{'.$placeholder.'}}')
                                ->join(', ');
                        }),
                ]),

            Section::make('Email copy')
                ->schema([
                    TextInput::make('subject')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Use placeholders such as {{reference}} or {{business_name}}.'),
                    TextInput::make('heading')
                        ->label('Heading')
                        ->maxLength(255)
                        ->helperText('Shown as the main title inside the email.'),
                    Textarea::make('body')
                        ->required()
                        ->rows(16)
                        ->helperText('Supports basic Markdown (**bold**, lists). Blank lines create paragraphs. Dynamic lead details (for internal alerts) and action buttons still append automatically where needed.'),
                ]),
        ]);
    }
}
