<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Filament\Support\MoneyInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customer')
                ->columns(2)
                ->schema([
                    TextInput::make('customer_name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('customer_email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('customer_phone')
                        ->label('Phone')
                        ->tel()
                        ->maxLength(255),
                    TextInput::make('billing_address_line1')
                        ->label('Address line 1')
                        ->maxLength(255),
                    TextInput::make('billing_address_line2')
                        ->label('Address line 2')
                        ->maxLength(255),
                    TextInput::make('billing_city')
                        ->label('Town / city')
                        ->maxLength(255),
                    TextInput::make('billing_postcode')
                        ->label('Postcode')
                        ->maxLength(20),
                ]),

            Section::make('Dates & discount')
                ->columns(3)
                ->schema([
                    DatePicker::make('issue_date')
                        ->label('Issue date')
                        ->helperText('Optional until you issue the invoice.'),
                    DatePicker::make('due_date')
                        ->label('Due date'),
                    MoneyInput::make('discount_pence', 'Discount')
                        ->required(false)
                        ->default(0),
                ]),

            Section::make('Line items')
                ->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->orderColumn('sort_order')
                        ->reorderable()
                        ->defaultItems(1)
                        ->addActionLabel('Add line item')
                        ->schema([
                            TextInput::make('description')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(6),
                            TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->default(1)
                                ->minValue(0.01)
                                ->step(0.01)
                                ->columnSpan(3),
                            MoneyInput::make('unit_price_pence', 'Unit price')
                                ->columnSpan(3),
                        ])
                        ->columns(12)
                        ->columnSpanFull()
                        ->helperText('Line totals are calculated automatically when you save (quantity × unit price).'),
                ]),

            Section::make('Notes & payment terms')
                ->columns(1)
                ->schema([
                    Textarea::make('payment_terms')
                        ->label('Payment terms')
                        ->rows(2),
                    Textarea::make('payment_instructions')
                        ->label('Payment instructions')
                        ->rows(3),
                    Textarea::make('notes')
                        ->rows(3),
                ]),
        ]);
    }
}
