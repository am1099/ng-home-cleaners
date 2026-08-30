<?php

namespace Tests\Feature;

use App\Enums\CleaningFrequency;
use App\Enums\PropertyType;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Livewire\EstimateWizard;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\CustomerMatcher;
use App\Services\QuoteRequestService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrmLeadsCustomersTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        $this->admin = User::factory()->create();
    }

    public function test_web_lead_appears_in_admin_leads_index_and_is_searchable(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $regular->id)
            ->set('frequency', CleaningFrequency::Weekly->value)
            ->set('propertyType', PropertyType::Flat->value)
            ->set('bedrooms', 1)
            ->set('floors', 1)
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('arrivalWindow', 'flexible')
            ->set('firstName', 'Alex')
            ->set('lastName', 'Taylor')
            ->set('phone', '07503651476')
            ->set('email', 'alex.crm@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham')
            ->call('submit');

        $lead = QuoteRequest::query()->firstOrFail();

        $this->actingAs($this->admin)
            ->get('/admin/quote-requests')
            ->assertOk()
            ->assertSee($lead->reference, false)
            ->assertSee('Alex Taylor', false);

        $this->actingAs($this->admin)
            ->get('/admin/quote-requests?tableSearch='.urlencode($lead->reference))
            ->assertOk()
            ->assertSee($lead->reference, false);

        $this->actingAs($this->admin)
            ->get(route('filament.admin.resources.quote-requests.view', ['record' => $lead]))
            ->assertOk()
            ->assertSee($lead->guide_estimate_headline, false)
            ->assertSee('Submitted estimate breakdown', false);
    }

    public function test_admin_can_update_status_and_final_quote(): void
    {
        $lead = $this->createWebLead();

        $this->actingAs($this->admin);

        $lead->update([
            'status' => QuoteRequestStatus::QuoteSent,
            'final_quote_amount_pence' => 12500,
            'internal_notes' => 'Quoted after walkthrough video.',
        ]);

        $lead->refresh();

        $this->assertSame(QuoteRequestStatus::QuoteSent, $lead->status);
        $this->assertSame(12500, $lead->final_quote_amount_pence);
        $this->assertNotNull($lead->quote_sent_at);
        $this->assertNotNull($lead->contacted_at);
        $this->assertSame('Quoted after walkthrough video.', $lead->internal_notes);
    }

    public function test_admin_can_create_phone_manual_lead(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        $lead = app(QuoteRequestService::class)->createManual([
            'source' => QuoteRequestSource::Phone,
            'service_id' => $service->id,
            'first_name' => 'Jordan',
            'last_name' => 'Lee',
            'phone' => '07503651476',
            'email' => 'jordan@example.com',
            'postcode' => 'NG5 2BB',
            'address_line1' => '12 Sherwood Road',
            'city' => 'Nottingham',
            'preferred_date' => now()->addDays(5)->toDateString(),
            'arrival_window' => 'morning',
            'property_type' => PropertyType::House->value,
            'bedrooms' => 3,
            'floors' => 2,
            'internal_notes' => 'Called about deep clean before guests.',
            'status' => QuoteRequestStatus::Contacted,
        ]);

        $this->assertSame(QuoteRequestSource::Phone, $lead->source);
        $this->assertSame(QuoteRequestStatus::Contacted, $lead->status);
        $this->assertNotNull($lead->contacted_at);
        $this->assertSame('Jordan', $lead->first_name);
        $this->assertDatabaseHas('customers', [
            'email' => 'jordan@example.com',
            'phone_normalized' => '07503651476',
        ]);

        $this->actingAs($this->admin)
            ->get(route('filament.admin.resources.quote-requests.view', ['record' => $lead]))
            ->assertOk()
            ->assertSee('NG-', false)
            ->assertSee('Called about deep clean before guests.', false);
    }

    public function test_customer_matcher_does_not_merge_conflicting_email_on_same_phone(): void
    {
        Customer::query()->create([
            'first_name' => 'Existing',
            'last_name' => 'Person',
            'phone_normalized' => '07503651476',
            'phone_display' => '07503 651476',
            'email' => 'existing@example.com',
        ]);

        $matcher = app(CustomerMatcher::class);

        $this->assertNull(
            $matcher->findMatch('different@example.com', '07503651476'),
        );

        $service = app(QuoteRequestService::class);
        $service->reconcileCustomer([
            'first_name' => 'New',
            'last_name' => 'Person',
            'phone' => '07503651476',
            'email' => 'different@example.com',
        ]);

        $this->assertDatabaseCount('customers', 2);
    }

    public function test_customer_page_shows_related_leads(): void
    {
        $lead = $this->createWebLead();
        $customer = $lead->customer;

        $this->assertTrue(
            $customer->quoteRequests()->where('reference', $lead->reference)->exists(),
        );

        $this->actingAs($this->admin)
            ->get(route('filament.admin.resources.customers.view', ['record' => $customer]))
            ->assertOk()
            ->assertSee($customer->fullName(), false)
            ->assertSee('Bookings', false)
            ->assertSee('Leads', false);
    }

    public function test_mark_status_helper_sets_timestamps(): void
    {
        $lead = $this->createWebLead();
        $lead->markStatus(QuoteRequestStatus::Won);

        $lead->refresh();

        $this->assertSame(QuoteRequestStatus::Won, $lead->status);
        $this->assertNotNull($lead->contacted_at);
        $this->assertNotNull($lead->quote_sent_at);
        $this->assertNotNull($lead->won_at);
    }

    private function createWebLead(): QuoteRequest
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $regular->id)
            ->set('frequency', CleaningFrequency::Weekly->value)
            ->set('propertyType', PropertyType::Flat->value)
            ->set('bedrooms', 1)
            ->set('floors', 1)
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('arrivalWindow', 'flexible')
            ->set('firstName', 'Alex')
            ->set('lastName', 'Taylor')
            ->set('phone', '07503651476')
            ->set('email', 'alex.crm@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham')
            ->call('submit');

        return QuoteRequest::query()->firstOrFail();
    }
}
