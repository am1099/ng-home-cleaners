<?php

namespace Tests\Feature;

use App\Enums\ServiceIcon;
use App\Filament\Pages\BookingCalendar;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Support\UkPostcode;
use Database\Seeders\CmsSeeder;
use Database\Seeders\CrmDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingCalendarUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        $this->seed(CrmDemoSeeder::class);
    }

    public function test_calendar_shows_colour_coded_bookings_and_postcode_pill(): void
    {
        $user = User::factory()->create();
        $booking = Booking::query()->where('status', '!=', 'cancelled')->with('service')->firstOrFail();
        $district = UkPostcode::district($booking->postcode) ?? 'NG1';

        $this->actingAs($user)
            ->get('/admin/booking-calendar?month='.$booking->booking_date->format('Y-m'))
            ->assertOk()
            ->assertSee($district, false)
            ->assertSee($booking->customer?->fullName() ?? '', false);
    }

    public function test_calendar_filters_by_service_and_postcode(): void
    {
        $user = User::factory()->create();
        $booking = Booking::query()->where('status', '!=', 'cancelled')->with(['service', 'customer'])->firstOrFail();
        $district = UkPostcode::district($booking->postcode);

        Livewire::actingAs($user)
            ->test(BookingCalendar::class, [
                'month' => $booking->booking_date->format('Y-m'),
            ])
            ->set('serviceId', $booking->service_id)
            ->set('postcode', $district)
            ->assertSee($booking->customer?->fullName() ?? '');
    }

    public function test_calendar_opens_booking_preview_modal(): void
    {
        $user = User::factory()->create();
        $booking = Booking::query()->where('status', '!=', 'cancelled')->with('customer')->firstOrFail();

        Livewire::actingAs($user)
            ->test(BookingCalendar::class, [
                'month' => $booking->booking_date->format('Y-m'),
            ])
            ->call('openBooking', $booking->id)
            ->assertSet('previewBookingId', $booking->id)
            ->assertSee($booking->reference)
            ->assertSee('Open booking');
    }

    public function test_service_icon_options_exclude_icons_used_by_other_services(): void
    {
        $house = Service::query()->where('icon', ServiceIcon::House)->firstOrFail();
        $options = ServiceIcon::filamentOptions(ServiceIcon::Sparkles->value);

        $this->assertArrayNotHasKey(ServiceIcon::House->value, $options);
        $this->assertArrayHasKey(ServiceIcon::Sparkles->value, $options);
        $this->assertArrayHasKey(ServiceIcon::PaintBrush->value, $options);
        $this->assertNotSame($house->icon, ServiceIcon::Sparkles);
    }

    public function test_uk_postcode_district_extraction(): void
    {
        $this->assertSame('NG1', UkPostcode::district('NG1 1AA'));
        $this->assertSame('NG16', UkPostcode::district('ng16 2bb'));
        $this->assertNull(UkPostcode::district(null));
    }
}
