<?php

namespace Tests\Feature;

use App\Enums\BookingConfirmationEquipment;
use App\Enums\BookingConfirmationParking;
use App\Enums\BookingStatus;
use App\Livewire\BookingConfirmationForm;
use App\Mail\InternalBookingConfirmationMail;
use App\Models\Booking;
use App\Models\BookingConfirmation;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class BookingConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_page_is_noindex_and_creates_a_crm_record(): void
    {
        Mail::fake();

        $this->get(route('booking.confirm'))
            ->assertOk()
            ->assertSee('Almost there.', false)
            ->assertSee('name="robots" content="noindex,nofollow"', false);

        Livewire::test(BookingConfirmationForm::class)
            ->set('fullName', 'Jane Smith')
            ->set('address', '42 Example Road, Nottingham, NG7 1AA')
            ->set('accessInstructions', 'I will be home to let you in.')
            ->set('parking', BookingConfirmationParking::OnSite->value)
            ->set('equipmentPreference', BookingConfirmationEquipment::BringOwn->value)
            ->set('notes', 'Cat will be shut in the bedroom.')
            ->set('valuablesSecured', true)
            ->set('paymentSent', true)
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertSee("We've got it.", false);

        $this->assertDatabaseHas('booking_confirmations', [
            'full_name' => 'Jane Smith',
            'parking' => BookingConfirmationParking::OnSite->value,
        ]);

        Mail::assertSent(InternalBookingConfirmationMail::class);
    }

    public function test_honeypot_does_not_store_a_confirmation(): void
    {
        Mail::fake();

        Livewire::test(BookingConfirmationForm::class)
            ->set('fullName', 'Bot')
            ->set('address', '1 Road, NG1 1AA')
            ->set('accessInstructions', 'Key under mat')
            ->set('parking', BookingConfirmationParking::None->value)
            ->set('equipmentPreference', BookingConfirmationEquipment::CustomerProvides->value)
            ->set('valuablesSecured', true)
            ->set('paymentSent', true)
            ->set('website', 'https://spam.example')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertSame(0, BookingConfirmation::query()->count());
        Mail::assertNothingSent();
    }

    public function test_submission_links_a_matching_customer_and_single_booking(): void
    {
        Mail::fake();

        $customer = Customer::query()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone_normalized' => '447500000099',
            'phone_display' => '07500 000099',
            'email' => 'jane.smith@example.com',
            'postcode' => 'NG7 1AA',
            'address_line1' => '42 Example Road',
            'city' => 'Nottingham',
        ]);

        $service = Service::factory()->create();
        $booking = Booking::query()->create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'address_line1' => '42 Example Road',
            'city' => 'Nottingham',
            'postcode' => 'NG7 1AA',
            'booking_date' => now()->addWeek()->toDateString(),
            'arrival_window' => 'morning',
            'agreed_price_pence' => 12000,
            'status' => BookingStatus::Scheduled,
        ]);

        Livewire::test(BookingConfirmationForm::class)
            ->set('fullName', 'Jane Smith')
            ->set('address', '42 Example Road, Nottingham, NG7 1AA')
            ->set('accessInstructions', 'Neighbour has the key.')
            ->set('parking', BookingConfirmationParking::PaidOrPermit->value)
            ->set('equipmentPreference', BookingConfirmationEquipment::CustomerProvides->value)
            ->set('valuablesSecured', true)
            ->set('paymentSent', true)
            ->call('submit');

        $confirmation = BookingConfirmation::query()->firstOrFail();

        $this->assertSame($customer->id, $confirmation->customer_id);
        $this->assertSame($booking->id, $confirmation->booking_id);
    }

    public function test_admin_can_open_booking_confirmations(): void
    {
        $user = User::factory()->create();
        $confirmation = BookingConfirmation::query()->create([
            'full_name' => 'Alex Taylor',
            'address' => '1 Test Street, NG1 1AA',
            'access_instructions' => 'I will be in.',
            'parking' => BookingConfirmationParking::None,
            'equipment_preference' => BookingConfirmationEquipment::BringOwn,
            'valuables_confirmed_at' => now(),
            'payment_confirmed_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/admin/booking-confirmations')
            ->assertOk()
            ->assertSee('Alex Taylor', false);

        $this->actingAs($user)
            ->get('/admin/booking-confirmations/'.$confirmation->getKey())
            ->assertOk()
            ->assertSee('I will be in.', false);
    }
}
