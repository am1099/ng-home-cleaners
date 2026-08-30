<?php

namespace App\Filament\Widgets;

use App\Enums\ArrivalWindow;
use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingBookingsWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Upcoming bookings';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->with(['customer:id,first_name,last_name', 'service:id,name'])
                    ->where('status', BookingStatus::Scheduled)
                    ->whereDate('booking_date', '>=', now()->toDateString())
                    ->orderBy('booking_date')
                    ->orderBy('arrival_window')
                    ->limit(8),
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('booking_date')
                    ->label('Date')
                    ->date('D j M')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->state(fn (Booking $record): string => $record->customer?->fullName() ?? '—')
                    ->url(fn (Booking $record): string => BookingResource::getUrl('view', ['record' => $record])),
                TextColumn::make('service.name')
                    ->label('Service')
                    ->placeholder('—'),
                TextColumn::make('arrival_window')
                    ->label('Arrival')
                    ->formatStateUsing(fn (ArrivalWindow $state): string => $state->shortLabel()),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BookingStatus $state): string => $state->label())
                    ->color(fn (BookingStatus $state): string => $state->color()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Booking $record): string => BookingResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('No upcoming bookings')
            ->emptyStateDescription('Scheduled jobs from today onwards will appear here.');
    }
}
