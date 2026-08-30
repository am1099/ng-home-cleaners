<?php

namespace App\Filament\Resources\Bookings\RelationManagers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Filament\Support\MoneyInput;
use App\Models\Booking;
use App\Models\Payment;
use App\Pricing\Money;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->options(PaymentType::options())
                ->required()
                ->live()
                ->helperText('Refunds are stored as money out. Adjustments may be positive or negative.'),
            MoneyInput::signed('amount_pence', 'Amount')
                ->helperText(fn ($get): string => match (PaymentType::tryFrom((string) $get('type'))) {
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
                ->maxLength(255),
            Textarea::make('notes')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->columns([
                TextColumn::make('paid_date')->date('d M Y')->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (PaymentType $state): string => $state->label()),
                TextColumn::make('method')
                    ->formatStateUsing(fn (PaymentMethod $state): string => $state->label()),
                TextColumn::make('amount_pence')
                    ->label('Amount')
                    ->formatStateUsing(fn (int $state): string => Money::formatPence($state))
                    ->alignEnd(),
                TextColumn::make('reference')->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $type = PaymentType::from($data['type']);
                        $data['amount_pence'] = Booking::normalizePaymentAmountPence($type, (int) $data['amount_pence']);

                        return $data;
                    })
                    ->after(function (Payment $record): void {
                        $this->warnIfOverpaid($record->booking);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $type = PaymentType::from($data['type']);
                        $data['amount_pence'] = Booking::normalizePaymentAmountPence($type, (int) $data['amount_pence']);

                        return $data;
                    })
                    ->after(function (Payment $record): void {
                        $this->warnIfOverpaid($record->booking);
                    }),
                DeleteAction::make(),
            ]);
    }

    private function warnIfOverpaid(?Booking $booking): void
    {
        if (! $booking) {
            return;
        }

        $booking->refresh();

        if (! $booking->isOverpaid()) {
            return;
        }

        Notification::make()
            ->title('Overpayment warning')
            ->body('Total paid ('.$booking->paidDisplay().') exceeds the agreed price ('.$booking->agreedDisplay().') by '.Money::formatPence($booking->overpaidPence()).'.')
            ->warning()
            ->persistent()
            ->send();
    }
}
