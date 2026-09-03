<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Invoices\Support\InvoiceActions;
use App\Filament\Support\ResponsiveRecordTable;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        $table = ResponsiveRecordTable::configure(
            $table,
            tableColumns: self::tableColumns(),
            cardColumns: self::cardColumns(),
        );

        return $table
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        ...InvoiceStatus::options(),
                        'overdue' => 'Overdue',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        if ($value === 'overdue') {
                            return $query
                                ->whereIn('status', [
                                    InvoiceStatus::Issued->value,
                                    InvoiceStatus::Sent->value,
                                ])
                                ->whereDate('due_date', '<', now()->toDateString());
                        }

                        return $query->where('status', $value);
                    }),
                Filter::make('issue_date')
                    ->form([
                        DatePicker::make('from')->label('Issue date from'),
                        DatePicker::make('until')->label('Issue date until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('issue_date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('issue_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View'),
                ActionGroup::make([
                    InvoiceActions::editDraft(),
                    InvoiceActions::preview()->label(fn (Invoice $record): string => $record->isDraft()
                        ? 'Preview draft'
                        : 'Preview PDF'),
                    InvoiceActions::issue(),
                    InvoiceActions::download(),
                    InvoiceActions::send(),
                    InvoiceActions::void(),
                    InvoiceActions::deleteDraft(),
                ])
                    ->label('More')
                    ->icon(Heroicon::EllipsisVertical)
                    ->button()
                    ->outlined()
                    ->size('sm'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No invoices yet')
            ->emptyStateDescription('Create an invoice from a confirmed booking.')
            ->emptyStateActions([
                Action::make('viewBookings')
                    ->label('View bookings')
                    ->url(BookingResource::getUrl('index')),
                CreateAction::make()
                    ->label('Create invoice'),
            ]);
    }

    /**
     * @return array<int, TextColumn>
     */
    private static function tableColumns(): array
    {
        return [
            TextColumn::make('invoice_number')
                ->label('Invoice')
                ->state(fn (Invoice $record): string => $record->displayNumber())
                ->searchable(['invoice_number', 'customer_name', 'customer_email', 'booking_reference'])
                ->sortable()
                ->wrapHeader(false)
                ->url(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record])),
            TextColumn::make('customer_name')
                ->label('Customer')
                ->searchable()
                ->wrapHeader(false)
                ->url(fn (Invoice $record): ?string => $record->customer_id
                    ? CustomerResource::getUrl('view', ['record' => $record->customer_id])
                    : null),
            TextColumn::make('booking_reference')
                ->label('Booking')
                ->searchable()
                ->wrapHeader(false)
                ->url(fn (Invoice $record): ?string => $record->booking_id
                    ? BookingResource::getUrl('view', ['record' => $record->booking_id])
                    : null),
            TextColumn::make('issue_date')
                ->label('Issued')
                ->date('d M Y')
                ->placeholder('—')
                ->sortable()
                ->wrapHeader(false),
            TextColumn::make('due_date')
                ->label('Due')
                ->date('d M Y')
                ->placeholder('—')
                ->sortable()
                ->wrapHeader(false),
            TextColumn::make('total')
                ->label('Total')
                ->state(fn (Invoice $record): string => $record->totalDisplay())
                ->alignEnd()
                ->wrapHeader(false),
            TextColumn::make('paid')
                ->label('Paid')
                ->state(fn (Invoice $record): string => $record->paidDisplay())
                ->alignEnd()
                ->wrapHeader(false),
            TextColumn::make('outstanding')
                ->label('Outstanding')
                ->state(fn (Invoice $record): string => $record->outstandingDisplay())
                ->alignEnd()
                ->wrapHeader(false),
            TextColumn::make('status')
                ->badge()
                ->wrapHeader(false)
                ->formatStateUsing(fn ($state, Invoice $record): string => $record->isOverdue()
                    ? 'Overdue'
                    : ($record->status instanceof InvoiceStatus ? $record->status->label() : '—'))
                ->color(fn ($state, Invoice $record): string => $record->isOverdue()
                    ? 'danger'
                    : ($record->status instanceof InvoiceStatus ? $record->status->color() : 'gray')),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function cardColumns(): array
    {
        return [
            ResponsiveRecordTable::stack([
                Split::make([
                    TextColumn::make('invoice_number')
                        ->state(fn (Invoice $record): string => $record->displayNumber())
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large)
                        ->url(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record]))
                        ->grow(false),
                    TextColumn::make('status')
                        ->badge()
                        ->formatStateUsing(fn ($state, Invoice $record): string => $record->isOverdue()
                            ? 'Overdue'
                            : ($record->status instanceof InvoiceStatus ? $record->status->label() : '—'))
                        ->color(fn ($state, Invoice $record): string => $record->isOverdue()
                            ? 'danger'
                            : ($record->status instanceof InvoiceStatus ? $record->status->color() : 'gray'))
                        ->grow(false),
                ]),
                TextColumn::make('customer_name')
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (Invoice $record): string => collect([
                        $record->booking_reference,
                        $record->totalDisplay(),
                    ])->filter()->implode(' · ')),
                ResponsiveRecordTable::meta(
                    TextColumn::make('due_date')
                        ->date('d M Y')
                        ->placeholder('No due date')
                        ->formatStateUsing(fn ($state, Invoice $record): string => $record->due_date
                            ? 'Due '.$record->due_date->format('d M Y').' · Outstanding '.$record->outstandingDisplay()
                            : 'Outstanding '.$record->outstandingDisplay()),
                ),
            ]),
        ];
    }
}
