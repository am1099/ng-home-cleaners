<?php

namespace App\Livewire;

use App\Actions\DispatchBookingConfirmationNotifications;
use App\Enums\BookingConfirmationEquipment;
use App\Enums\BookingConfirmationParking;
use App\Enums\BookingConfirmationStatus;
use App\Models\BookingConfirmation;
use App\Models\SiteSetting;
use App\Services\BookingConfirmationMatcher;
use App\Services\SeoService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.quote')]
#[Title('Confirm your booking')]
class BookingConfirmationForm extends Component
{
    public string $fullName = '';

    public string $address = '';

    public string $accessInstructions = '';

    public string $parking = '';

    public string $equipmentPreference = '';

    public string $notes = '';

    public bool $valuablesSecured = false;

    public bool $paymentSent = false;

    public string $website = '';

    public bool $submitted = false;

    public function submit(
        BookingConfirmationMatcher $matcher,
        DispatchBookingConfirmationNotifications $notifications,
    ): void {
        if (filled($this->website)) {
            $this->submitted = true;

            return;
        }

        $key = 'booking-confirm:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'submit' => 'Too many confirmation attempts from this connection. Please wait a few minutes and try again.',
            ]);
        }

        RateLimiter::hit($key, 60);

        $validated = $this->validate([
            'fullName' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:500'],
            'accessInstructions' => ['required', 'string', 'max:1000'],
            'parking' => ['required', Rule::enum(BookingConfirmationParking::class)],
            'equipmentPreference' => ['required', Rule::enum(BookingConfirmationEquipment::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'valuablesSecured' => ['accepted'],
            'paymentSent' => ['accepted'],
        ]);

        $match = $matcher->match($validated['fullName'], $validated['address']);
        $now = now();

        $confirmation = BookingConfirmation::query()->create([
            'full_name' => $validated['fullName'],
            'address' => $validated['address'],
            'access_instructions' => $validated['accessInstructions'],
            'parking' => $validated['parking'],
            'equipment_preference' => $validated['equipmentPreference'],
            'notes' => filled($validated['notes'] ?? null) ? $validated['notes'] : null,
            'valuables_confirmed_at' => $now,
            'payment_confirmed_at' => $now,
            'customer_id' => $match['customer']?->id,
            'booking_id' => $match['booking']?->id,
            'status' => BookingConfirmationStatus::New,
            'ip_address' => request()->ip(),
            'submitted_at' => $now,
        ]);

        $notifications->handle($confirmation);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.booking-confirmation', [
            'whatsappHref' => SiteSetting::instance()->whatsappLink(),
        ])->layoutData([
            'seo' => app(SeoService::class)->forBookingConfirmation(),
        ]);
    }
}
