<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Actions\CreateInvoiceFromBooking;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Booking;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected static ?string $title = 'Create invoice';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Booking')
                ->description('Choose a booking that does not already have an active invoice. A draft will be prepared from the booking details.')
                ->schema([
                    Select::make('booking_id')
                        ->label('Booking')
                        ->options(fn (): array => Booking::query()
                            ->with(['customer', 'service', 'invoices'])
                            ->where('status', '!=', BookingStatus::Cancelled->value)
                            ->whereDoesntHave('invoices', fn ($query) => $query->where('status', '!=', InvoiceStatus::Void->value))
                            ->latest('booking_date')
                            ->limit(200)
                            ->get()
                            ->mapWithKeys(fn (Booking $booking): array => [
                                $booking->id => $booking->reference.' — '.($booking->customer?->fullName() ?? 'Customer')
                                    .' · '.($booking->service?->name ?? 'Service')
                                    .' · '.$booking->agreedDisplay(),
                            ])
                            ->all())
                        ->searchable()
                        ->required()
                        ->helperText('Only bookings without a draft or issued invoice are listed.'),
                ]),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $booking = Booking::query()->findOrFail($data['booking_id']);

        try {
            return app(CreateInvoiceFromBooking::class)->handle($booking, Auth::user());
        } catch (InvalidArgumentException $exception) {
            Notification::make()
                ->title('Could not create invoice')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            throw new Halt;
        }
    }

    protected function getRedirectUrl(): string
    {
        $record = $this->getRecord();

        if ($record->exists && method_exists($record, 'isDraft') && $record->isDraft()) {
            return InvoiceResource::getUrl('edit', ['record' => $record]);
        }

        if ($record->exists) {
            return InvoiceResource::getUrl('view', ['record' => $record]);
        }

        return InvoiceResource::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Draft invoice created';
    }
}
