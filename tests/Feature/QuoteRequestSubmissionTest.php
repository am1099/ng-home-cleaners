<?php

namespace Tests\Feature;

use App\Enums\ArrivalWindow;
use App\Enums\CleaningFrequency;
use App\Enums\PropertyType;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Livewire\EstimateWizard;
use App\Mail\CustomerQuoteAcknowledgementMail;
use App\Mail\InternalQuoteRequestMail;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\User;
use App\Notifications\NewQuoteRequestNotification;
use App\Pricing\EstimateInputFactory;
use App\Pricing\PricingEngine;
use App\Services\QuoteRequestService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class QuoteRequestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        $this->admin = User::factory()->create();

        Mail::fake();
        Notification::fake();
    }

    public function test_web_submission_persists_lead_and_dispatches_notifications(): void
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
            ->set('email', 'alex@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham')
            ->call('submit')
            ->assertRedirect(route('quote.confirmation', 'NG-1001'));

        $this->assertDatabaseCount('quote_requests', 1);
        $this->assertDatabaseCount('customers', 1);

        $quoteRequest = QuoteRequest::query()->firstOrFail();

        $this->assertSame('NG-1001', $quoteRequest->reference);
        $this->assertSame(QuoteRequestSource::Web, $quoteRequest->source);
        $this->assertSame(QuoteRequestStatus::New, $quoteRequest->status);
        $this->assertNull($quoteRequest->whatsapp_initiated_at);
        $this->assertSame('Alex', $quoteRequest->first_name);
        $this->assertSame('alex@example.com', $quoteRequest->email);

        $expected = app(PricingEngine::class)->calculate(
            EstimateInputFactory::make(
                service: $regular,
                propertyType: PropertyType::Flat,
                bedrooms: 1,
                bathrooms: 1,
                wcs: 0,
                kitchens: 1,
                receptionRooms: 1,
                floors: 1,
                extraRoomSlugs: [],
                frequency: CleaningFrequency::Weekly,
                propertyStatus: null,
                conditionFlagValues: [],
                addonIds: [],
                postcode: 'NG1 1AA',
                preferredDate: now()->addWeek()->toDateString(),
                conditionNotes: '',
                parkingNotes: '',
                accessNotes: '',
                arrivalWindow: ArrivalWindow::Flexible,
            ),
        );

        $this->assertSame($expected->displayHeadline, $quoteRequest->guide_estimate_headline);
        $this->assertSame($expected->snapshot, $quoteRequest->pricing_snapshot);

        Mail::assertQueued(InternalQuoteRequestMail::class, function (InternalQuoteRequestMail $mail) use ($quoteRequest): bool {
            return $mail->quoteRequest->is($quoteRequest);
        });

        Mail::assertQueued(CustomerQuoteAcknowledgementMail::class, function (CustomerQuoteAcknowledgementMail $mail) use ($quoteRequest): bool {
            return $mail->quoteRequest->is($quoteRequest);
        });

        Notification::assertSentTo($this->admin, NewQuoteRequestNotification::class);
    }

    public function test_whatsapp_submission_saves_lead_before_opening_chat(): void
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
            ->set('firstName', 'Sam')
            ->set('lastName', 'Patel')
            ->set('phone', '07503651476')
            ->set('email', 'sam@example.com')
            ->set('postcode', 'NG2 2BB')
            ->set('addressLine1', '2 Test Lane')
            ->set('city', 'Nottingham')
            ->call('submitViaWhatsApp')
            ->assertDispatched('open-whatsapp')
            ->assertSet('whatsappNotice', 'Your request was saved and WhatsApp was opened.')
            ->assertSet('savedReference', 'NG-1001');

        $quoteRequest = QuoteRequest::query()->firstOrFail();

        $this->assertSame(QuoteRequestSource::Whatsapp, $quoteRequest->source);
        $this->assertNotNull($quoteRequest->whatsapp_initiated_at);

        $whatsappUrl = app(QuoteRequestService::class)->whatsappUrl($quoteRequest);

        $this->assertStringContainsString('wa.me', $whatsappUrl);
        $this->assertStringContainsString(rawurlencode($quoteRequest->reference), $whatsappUrl);
        $this->assertStringContainsString('text=', $whatsappUrl);

        Mail::assertQueued(InternalQuoteRequestMail::class);
        Mail::assertQueued(CustomerQuoteAcknowledgementMail::class);
        Notification::assertSentTo($this->admin, NewQuoteRequestNotification::class);
    }

    public function test_repeated_submission_clicks_do_not_create_duplicate_leads(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        $component = Livewire::test(EstimateWizard::class)
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
            ->set('email', 'alex@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham');

        $component->call('submit')->assertRedirect(route('quote.confirmation', 'NG-1001'));

        Mail::assertQueued(InternalQuoteRequestMail::class, 1);
        Notification::assertSentTo($this->admin, NewQuoteRequestNotification::class, 1);

        $component->call('submit')->assertRedirect(route('quote.confirmation', 'NG-1001'));

        $this->assertDatabaseCount('quote_requests', 1);
        Mail::assertQueued(InternalQuoteRequestMail::class, 1);
        Notification::assertSentTo($this->admin, NewQuoteRequestNotification::class, 1);
    }

    public function test_repeated_whatsapp_clicks_do_not_create_duplicate_leads(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        $component = Livewire::test(EstimateWizard::class)
            ->set('serviceId', $regular->id)
            ->set('frequency', CleaningFrequency::Weekly->value)
            ->set('propertyType', PropertyType::Flat->value)
            ->set('bedrooms', 1)
            ->set('floors', 1)
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('arrivalWindow', 'flexible')
            ->set('firstName', 'Sam')
            ->set('lastName', 'Patel')
            ->set('phone', '07503651476')
            ->set('email', 'sam@example.com')
            ->set('postcode', 'NG2 2BB')
            ->set('addressLine1', '2 Test Lane')
            ->set('city', 'Nottingham');

        $component->call('submitViaWhatsApp')->assertDispatched('open-whatsapp');
        $component->call('submitViaWhatsApp')->assertDispatched('open-whatsapp');

        $this->assertDatabaseCount('quote_requests', 1);
        Mail::assertQueued(InternalQuoteRequestMail::class, 1);
    }

    public function test_confirmation_page_displays_saved_request(): void
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
            ->set('email', 'alex@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham')
            ->call('submit');

        $quoteRequest = QuoteRequest::query()->firstOrFail();

        $this->get(route('quote.confirmation', $quoteRequest->reference))
            ->assertOk()
            ->assertSee($quoteRequest->reference, false)
            ->assertSee($regular->name, false)
            ->assertSee($quoteRequest->guide_estimate_headline, false)
            ->assertSee('What happens next', false)
            ->assertSee('Continue on WhatsApp', false)
            ->assertSee('Guide estimate only', false);
    }
}
