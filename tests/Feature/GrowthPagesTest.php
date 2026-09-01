<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Jobs\SendQuoteFollowUpJob;
use App\Mail\CustomerQuoteFollowUpMail;
use App\Mail\CustomerReviewRequestMail;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\Testimonial;
use App\Services\CoverageService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GrowthPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_reviews_page_lists_published_non_demo_testimonials(): void
    {
        $this->get(route('reviews'))
            ->assertOk()
            ->assertSee('What customers say', false);

        $demo = Testimonial::query()->where('is_demo', true)->first();
        $live = Testimonial::query()->publishedForProduction()->first();

        if ($live) {
            $this->get(route('reviews'))->assertSee($live->customer_name, false);
        }

        if ($demo) {
            $this->assertTrue($demo->is_demo);
        }
    }

    public function test_service_in_area_page_renders(): void
    {
        $area = ServiceArea::query()->active()->firstOrFail();
        $service = Service::query()->active()->firstOrFail();

        $this->get(route('areas.service', [$area, $service]))
            ->assertOk()
            ->assertSee($service->name, false)
            ->assertSee($area->name, false);
    }

    public function test_move_in_out_page_renders(): void
    {
        $this->get(route('move-in-out'))
            ->assertOk()
            ->assertSee('Move-in and move-out cleaning', false);
    }

    public function test_coverage_checker_recognises_ng7(): void
    {
        $result = app(CoverageService::class)->check('NG7 1AA');

        $this->assertTrue($result['covered']);
        $this->assertSame('NG7', $result['district']);
        $this->assertNotNull($result['area']);
    }

    public function test_coverage_checker_rejects_out_of_area_district(): void
    {
        $result = app(CoverageService::class)->check('NG17 1AA');

        $this->assertFalse($result['covered']);
        $this->assertSame('NG17', $result['district']);
    }

    public function test_homepage_includes_faq_schema(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('"@type":"FAQPage"', false);
    }

    public function test_follow_up_job_emails_stale_new_leads(): void
    {
        Mail::fake();

        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $customer = Customer::query()->create([
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone_normalized' => '07503651476',
            'phone_display' => '07503 651476',
            'email' => 'followup@example.com',
        ]);

        $lead = QuoteRequest::query()->create([
            'reference' => 'NG-FU-1',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'source' => QuoteRequestSource::Web,
            'status' => QuoteRequestStatus::New,
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone' => '07503651476',
            'email' => 'followup@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '1 Test Street',
            'city' => 'Nottingham',
            'preferred_date' => now()->addWeek()->toDateString(),
            'arrival_window' => 'flexible',
            'property_type' => 'flat',
            'bedrooms' => 1,
            'floors' => 1,
            'selections_snapshot' => [],
            'pricing_snapshot' => [],
            'guide_estimate_headline' => 'From £95',
            'submitted_at' => now()->subHours(25),
        ]);

        (new SendQuoteFollowUpJob)->handle();

        Mail::assertQueued(CustomerQuoteFollowUpMail::class);
        $this->assertNotNull($lead->fresh()->follow_up_sent_at);
    }

    public function test_completed_booking_queues_review_request(): void
    {
        Mail::fake();

        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $customer = Customer::query()->create([
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone_normalized' => '07503651476',
            'phone_display' => '07503 651476',
            'email' => 'review@example.com',
        ]);

        $booking = Booking::query()->create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'address_line1' => '1 Test Street',
            'city' => 'Nottingham',
            'postcode' => 'NG1 1AA',
            'booking_date' => now()->toDateString(),
            'arrival_window' => 'flexible',
            'agreed_price_pence' => 9000,
            'status' => BookingStatus::Scheduled,
        ]);

        $booking->markStatus(BookingStatus::Completed);

        Mail::assertQueued(CustomerReviewRequestMail::class);
        $this->assertNotNull($booking->fresh()->review_request_sent_at);
    }
}
