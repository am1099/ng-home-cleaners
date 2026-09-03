<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Pricing\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Carbon;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Summary')
                ->columns(3)
                ->schema([
                    TextEntry::make('invoice_number')
                        ->label('Invoice')
                        ->state(fn (Invoice $record): string => $record->displayNumber())
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large)
                        ->copyable(fn (Invoice $record): bool => filled($record->invoice_number)),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn ($state, Invoice $record): string => $record->isOverdue()
                            ? 'Overdue'
                            : ($record->status instanceof InvoiceStatus ? $record->status->label() : '—'))
                        ->color(fn ($state, Invoice $record): string => $record->isOverdue()
                            ? 'danger'
                            : ($record->status instanceof InvoiceStatus ? $record->status->color() : 'gray')),
                    TextEntry::make('issue_date')
                        ->label('Issued')
                        ->date('d M Y')
                        ->placeholder('—'),
                    TextEntry::make('due_date')
                        ->label('Due')
                        ->date('d M Y')
                        ->placeholder('—')
                        ->color(fn (Invoice $record): string => $record->isOverdue() ? 'danger' : 'gray'),
                    TextEntry::make('booking_reference')
                        ->label('Booking')
                        ->url(fn (Invoice $record): ?string => $record->booking_id
                            ? BookingResource::getUrl('view', ['record' => $record->booking_id])
                            : null)
                        ->placeholder('—'),
                    TextEntry::make('service_name')
                        ->label('Service')
                        ->placeholder('—'),
                    TextEntry::make('void_reason')
                        ->label('Void reason')
                        ->placeholder('—')
                        ->visible(fn (Invoice $record): bool => $record->isVoid())
                        ->columnSpanFull(),
                ]),

            Section::make('Customer')
                ->columns(3)
                ->schema([
                    TextEntry::make('customer_name')
                        ->label('Name')
                        ->url(fn (Invoice $record): ?string => $record->customer_id
                            ? CustomerResource::getUrl('view', ['record' => $record->customer_id])
                            : null),
                    TextEntry::make('customer_email')
                        ->label('Email')
                        ->placeholder('—')
                        ->copyable(),
                    TextEntry::make('customer_phone')
                        ->label('Phone')
                        ->placeholder('—'),
                    TextEntry::make('billing_address')
                        ->label('Address')
                        ->state(fn (Invoice $record): string => $record->billingAddressDisplay() ?: '—')
                        ->columnSpanFull(),
                ]),

            Section::make('Line items')
                ->schema([
                    RepeatableEntry::make('items')
                        ->schema([
                            TextEntry::make('description')
                                ->label('Description')
                                ->columnSpan(6),
                            TextEntry::make('quantity')
                                ->label('Qty')
                                ->columnSpan(2),
                            TextEntry::make('unit_price_pence')
                                ->label('Unit')
                                ->formatStateUsing(fn (int $state): string => Money::formatPenceExact($state))
                                ->columnSpan(2),
                            TextEntry::make('line_total_pence')
                                ->label('Total')
                                ->formatStateUsing(fn (int $state): string => Money::formatPenceExact($state))
                                ->columnSpan(2),
                        ])
                        ->columns(12)
                        ->contained(false),
                ]),

            Section::make('Totals')
                ->columns(3)
                ->schema([
                    TextEntry::make('subtotal')
                        ->label('Subtotal')
                        ->state(fn (Invoice $record): string => $record->subtotalDisplay()),
                    TextEntry::make('discount')
                        ->label('Discount')
                        ->state(fn (Invoice $record): string => $record->discountDisplay()),
                    TextEntry::make('vat')
                        ->label(fn (Invoice $record): string => $record->vat_registered
                            ? 'VAT'.(filled($record->vat_rate_percent) ? ' '.$record->vat_rate_percent.'%' : '')
                            : 'VAT')
                        ->state(fn (Invoice $record): string => $record->vat_registered
                            ? $record->vatDisplay()
                            : 'Not registered'),
                    TextEntry::make('total')
                        ->label('Total')
                        ->state(fn (Invoice $record): string => $record->totalDisplay())
                        ->weight(FontWeight::Bold),
                    TextEntry::make('paid')
                        ->label('Paid')
                        ->state(fn (Invoice $record): string => $record->paidDisplay()),
                    TextEntry::make('amount_due')
                        ->label('Amount due')
                        ->state(fn (Invoice $record): string => $record->amountDueDisplay())
                        ->weight(FontWeight::SemiBold)
                        ->color(fn (Invoice $record): string => $record->outstandingPence() > 0 ? 'warning' : 'success'),
                ]),

            Section::make('Payments')
                ->description('Payments recorded against the linked booking.')
                ->schema([
                    RepeatableEntry::make('booking.payments')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('paid_date')
                                ->label('Date')
                                ->date('d M Y')
                                ->columnSpan(2),
                            TextEntry::make('type')
                                ->label('Type')
                                ->badge()
                                ->formatStateUsing(fn (PaymentType $state): string => $state->label())
                                ->columnSpan(2),
                            TextEntry::make('method')
                                ->label('Method')
                                ->formatStateUsing(fn (PaymentMethod $state): string => $state->label())
                                ->columnSpan(2),
                            TextEntry::make('amount_pence')
                                ->label('Amount')
                                ->formatStateUsing(fn (int $state): string => Money::formatPenceExact($state))
                                ->columnSpan(2),
                            TextEntry::make('reference')
                                ->label('Reference')
                                ->placeholder('—')
                                ->columnSpan(4),
                        ])
                        ->columns(12)
                        ->contained(false)
                        ->placeholder('No payments recorded yet.'),
                ])
                ->visible(fn (Invoice $record): bool => $record->booking_id !== null),

            Section::make('Email history')
                ->schema([
                    RepeatableEntry::make('deliveries')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Queued')
                                ->dateTime('d M Y H:i')
                                ->columnSpan(3),
                            TextEntry::make('recipient_email')
                                ->label('To')
                                ->columnSpan(4),
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->formatStateUsing(fn (InvoiceDeliveryStatus $state): string => $state->label())
                                ->color(fn (InvoiceDeliveryStatus $state): string => $state->color())
                                ->columnSpan(2),
                            TextEntry::make('sent_at')
                                ->label('Sent')
                                ->dateTime('d M Y H:i')
                                ->placeholder('—')
                                ->columnSpan(3),
                            TextEntry::make('error_summary')
                                ->label('Error')
                                ->placeholder('—')
                                ->visible(fn (InvoiceDelivery $record): bool => $record->status === InvoiceDeliveryStatus::Failed)
                                ->columnSpanFull(),
                        ])
                        ->columns(12)
                        ->contained(false)
                        ->placeholder('No emails queued yet.'),
                ]),

            Section::make('Activity')
                ->schema([
                    TextEntry::make('activity')
                        ->hiddenLabel()
                        ->html()
                        ->state(fn (Invoice $record): string => self::activityHtml($record))
                        ->columnSpanFull(),
                ]),

            Section::make('Notes')
                ->columns(1)
                ->schema([
                    TextEntry::make('payment_terms')
                        ->label('Terms')
                        ->placeholder('—')
                        ->prose(),
                    TextEntry::make('payment_instructions')
                        ->label('Payment info')
                        ->placeholder('—')
                        ->prose(),
                    TextEntry::make('notes')
                        ->label('Notes')
                        ->placeholder('—')
                        ->prose(),
                ]),
        ]);
    }

    private static function activityHtml(Invoice $record): string
    {
        $events = collect();

        $events->push([
            'at' => $record->created_at,
            'label' => 'Draft created',
        ]);

        if ($record->issued_at) {
            $events->push([
                'at' => $record->issued_at,
                'label' => 'Issued as '.$record->displayNumber(),
            ]);
        }

        foreach ($record->deliveries as $delivery) {
            /** @var InvoiceDelivery $delivery */
            $label = match ($delivery->status) {
                InvoiceDeliveryStatus::Queued => 'Email queued to '.$delivery->recipient_email,
                InvoiceDeliveryStatus::Sent => 'Email sent to '.$delivery->recipient_email,
                InvoiceDeliveryStatus::Failed => 'Email failed for '.$delivery->recipient_email,
            };

            $events->push([
                'at' => $delivery->sent_at ?? $delivery->failed_at ?? $delivery->created_at,
                'label' => $label,
            ]);
        }

        if ($record->paid_at) {
            $events->push([
                'at' => $record->paid_at,
                'label' => 'Marked paid',
            ]);
        }

        if ($record->voided_at) {
            $events->push([
                'at' => $record->voided_at,
                'label' => 'Voided'.(filled($record->void_reason) ? ' — '.$record->void_reason : ''),
            ]);
        }

        $lines = $events
            ->filter(fn (array $event): bool => $event['at'] instanceof Carbon)
            ->sortByDesc(fn (array $event): int => $event['at']->getTimestamp())
            ->map(function (array $event): string {
                /** @var Carbon $at */
                $at = $event['at'];

                return '<li><span class="text-gray-500 dark:text-gray-400">'.$at->format('d M Y H:i').'</span> — '.e($event['label']).'</li>';
            })
            ->implode('');

        if ($lines === '') {
            return '<p class="text-gray-500">No activity yet.</p>';
        }

        return '<ul class="list-disc space-y-1 ps-5 text-sm">'.$lines.'</ul>';
    }
}
