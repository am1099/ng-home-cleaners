<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\ArrivalWindow;
use App\Enums\BookingStatus;
use App\Filament\Support\MoneyInput;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Services\BookingClashDetector;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Booking')
                ->columns(3)
                ->schema([
                    Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'email')
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => $record->fullName().' ('.$record->phone_display.')')
                        ->searchable(['first_name', 'last_name', 'email', 'phone_display', 'phone_normalized'])
                        ->preload()
                        ->required()
                        ->columnSpan(1),
                    Select::make('quote_request_id')
                        ->label('Source lead')
                        ->relationship('quoteRequest', 'reference')
                        ->getOptionLabelFromRecordUsing(fn (QuoteRequest $record): string => $record->reference.' — '.$record->fullName())
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpan(1),
                    Select::make('service_id')
                        ->label('Service')
                        ->relationship('service', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(1),
                    Select::make('status')
                        ->options(BookingStatus::options())
                        ->required()
                        ->default(BookingStatus::Scheduled->value),
                    DatePicker::make('booking_date')
                        ->label('Booking date')
                        ->required()
                        ->live(),
                    Select::make('arrival_window')
                        ->label('Arrival window')
                        ->options(ArrivalWindow::options())
                        ->required()
                        ->live(),
                    TextInput::make('address_line1')
                        ->label('Address line 1')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),
                    TextInput::make('address_line2')
                        ->label('Address line 2')
                        ->maxLength(255),
                    TextInput::make('city')
                        ->maxLength(255),
                    TextInput::make('postcode')
                        ->required()
                        ->maxLength(20),
                    TextInput::make('expected_duration_minutes')
                        ->label('Expected duration (minutes)')
                        ->numeric()
                        ->minValue(15)
                        ->maxValue(1440)
                        ->nullable(),
                    MoneyInput::make('agreed_price_pence', 'Agreed price')
                        ->required(),
                    Placeholder::make('clash_warning')
                        ->label('Schedule clash')
                        ->content(function (Get $get, ?Booking $record): string {
                            $date = $get('booking_date');
                            $window = $get('arrival_window');

                            if (blank($date) || blank($window)) {
                                return 'Select a date and arrival window to check for clashes.';
                            }

                            $warning = app(BookingClashDetector::class)->warningMessage(
                                $date,
                                $window,
                                $record?->id,
                            );

                            return $warning ?? 'No conflicting bookings detected for this day and arrival window.';
                        })
                        ->extraAttributes(fn (Get $get, ?Booking $record): array => [
                            'class' => filled($get('booking_date'))
                                && filled($get('arrival_window'))
                                && app(BookingClashDetector::class)->warningMessage(
                                    $get('booking_date'),
                                    $get('arrival_window'),
                                    $record?->id,
                                )
                                ? 'text-warning-600 dark:text-warning-400 font-medium'
                                : 'text-gray-500',
                        ])
                        ->columnSpanFull(),
                    Textarea::make('internal_notes')
                        ->label('Internal notes')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
