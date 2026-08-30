<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Filament\Support\MoneyInput;
use App\Models\Booking;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Payment')
                ->columns(3)
                ->schema([
                    Select::make('booking_id')
                        ->label('Booking')
                        ->relationship('booking', 'reference')
                        ->getOptionLabelFromRecordUsing(fn (Booking $record): string => $record->reference.' — '.($record->customer?->fullName() ?? 'Customer').' ('.$record->agreedDisplay().')')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(2),
                    Select::make('type')
                        ->options(PaymentType::options())
                        ->required()
                        ->live()
                        ->helperText('Refunds are stored as money out. Adjustments may be positive or negative.'),
                    MoneyInput::signed('amount_pence', 'Amount')
                        ->helperText(fn (Get $get): string => match (PaymentType::tryFrom((string) $get('type'))) {
                            PaymentType::Refund => 'Enter the refund amount as a positive figure; it will be recorded as money out.',
                            PaymentType::Adjustment => 'Use a negative amount for a credit/refund-style adjustment.',
                            default => 'Enter the amount received.',
                        }),
                    Select::make('method')
                        ->options(PaymentMethod::options())
                        ->required(),
                    DatePicker::make('paid_date')
                        ->label('Paid date')
                        ->required()
                        ->default(now()),
                    TextInput::make('reference')
                        ->maxLength(255)
                        ->columnSpan(2),
                    Textarea::make('notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
