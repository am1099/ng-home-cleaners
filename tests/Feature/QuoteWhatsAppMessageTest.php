<?php

namespace Tests\Feature;

use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Services\QuoteRequestService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteWhatsAppMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_whatsapp_message_is_a_short_booking_intro(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();
        $customer = Customer::query()->create([
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone_normalized' => '07503651476',
            'phone_display' => '07503 651476',
            'email' => 'alex.wa@example.com',
        ]);

        $lead = QuoteRequest::query()->create([
            'reference' => 'NG-TEST-WA',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'source' => QuoteRequestSource::Web,
            'status' => QuoteRequestStatus::New,
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone' => '07503651476',
            'email' => 'alex.wa@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '1 Test Street',
            'city' => 'Nottingham',
            'preferred_date' => now()->addWeek()->toDateString(),
            'arrival_window' => 'flexible',
            'property_type' => 'house',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'floors' => 2,
            'selections_snapshot' => [],
            'pricing_snapshot' => [],
            'guide_estimate_headline' => '£225',
            'guide_single_price_pence' => 22500,
            'is_numeric_estimate' => true,
            'submitted_at' => now(),
        ]);

        $quoteService = app(QuoteRequestService::class);
        $message = $quoteService->whatsappMessage($lead);
        $url = $quoteService->whatsappUrl($lead);

        $this->assertSame(
            "Hi NG Home Cleaners— I'd like to BOOK: Deep clean — 2 bed, 1 bath (£225, about 2½ hrs). When are you next available?",
            $message,
        );
        $this->assertStringNotContainsString('walkthrough video', $message);
        $this->assertStringNotContainsString('NG-TEST-WA', $message);
        $this->assertStringContainsString(rawurlencode("I'd like to BOOK: Deep clean"), $url);
    }

    public function test_whatsapp_message_works_without_a_numeric_price(): void
    {
        $service = Service::query()->where('slug', 'office-commercial')->firstOrFail();

        $lead = QuoteRequest::query()->create([
            'reference' => 'NG-TEST-PH',
            'service_id' => $service->id,
            'source' => QuoteRequestSource::Web,
            'status' => QuoteRequestStatus::New,
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone' => '07503651476',
            'email' => 'alex.photos@example.com',
            'postcode' => 'NG1 1AA',
            'property_type' => 'house',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'floors' => 2,
            'selections_snapshot' => [],
            'pricing_snapshot' => [],
            'guide_estimate_headline' => 'Priced per visit',
            'submitted_at' => now(),
        ]);

        $message = app(QuoteRequestService::class)->whatsappMessage($lead);

        $this->assertSame(
            "Hi NG Home Cleaners— I'd like to BOOK: Office & commercial — 2 bed, 1 bath. When are you next available?",
            $message,
        );
    }
}
