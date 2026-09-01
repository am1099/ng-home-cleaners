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

    public function test_whatsapp_message_asks_for_a_walkthrough_video(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
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
            'property_type' => 'flat',
            'bedrooms' => 1,
            'floors' => 1,
            'selections_snapshot' => [],
            'pricing_snapshot' => [],
            'guide_estimate_headline' => 'From £95',
            'submitted_at' => now(),
        ]);

        $service = app(QuoteRequestService::class);
        $message = $service->whatsappMessage($lead);
        $url = $service->whatsappUrl($lead);

        $this->assertStringContainsString('walkthrough video', $message);
        $this->assertStringContainsString('kitchen, bathrooms, main rooms', $message);
        $this->assertStringContainsString('NG-TEST-WA', $url);
        $this->assertStringContainsString(rawurlencode('walkthrough video'), $url);
    }

    public function test_whatsapp_message_mentions_saved_photos_when_present(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $customer = Customer::query()->create([
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone_normalized' => '07503651476',
            'phone_display' => '07503 651476',
            'email' => 'alex.photos@example.com',
        ]);

        $lead = QuoteRequest::query()->create([
            'reference' => 'NG-TEST-PH',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'source' => QuoteRequestSource::Web,
            'status' => QuoteRequestStatus::New,
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone' => '07503651476',
            'email' => 'alex.photos@example.com',
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
            'property_photo_paths' => ['quote-requests/NG-TEST-PH/one.jpg'],
            'submitted_at' => now(),
        ]);

        $message = app(QuoteRequestService::class)->whatsappMessage($lead);

        $this->assertStringContainsString('Photos uploaded with the form are already saved', $message);
    }
}
