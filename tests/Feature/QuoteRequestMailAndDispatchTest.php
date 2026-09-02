<?php

namespace Tests\Feature;

use App\Actions\DispatchQuoteRequestNotifications;
use App\Enums\CleaningFrequency;
use App\Enums\PropertyType;
use App\Enums\QuoteRequestSource;
use App\Livewire\EstimateWizard;
use App\Mail\CustomerQuoteAcknowledgementMail;
use App\Mail\InternalQuoteRequestMail;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingsService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class QuoteRequestMailAndDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        User::factory()->create();
    }

    public function test_internal_and_customer_mails_include_reference_and_key_fields(): void
    {
        Mail::fake();
        Notification::fake();

        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $regular->id)
            ->set('frequency', CleaningFrequency::OneOff->value)
            ->set('propertyType', PropertyType::House->value)
            ->set('bedrooms', 2)
            ->set('floors', 2)
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('arrivalWindow', 'flexible')
            ->set('firstName', 'Jordan')
            ->set('lastName', 'Lee')
            ->set('phone', '07503651476')
            ->set('email', 'jordan@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '9 Lace Market')
            ->set('city', 'Nottingham')
            ->call('submit');

        $lead = QuoteRequest::query()->firstOrFail();

        Mail::assertSent(InternalQuoteRequestMail::class, function (InternalQuoteRequestMail $mail) use ($lead): bool {
            $html = $mail->render();

            return $mail->quoteRequest->is($lead)
                && str_contains($mail->envelope()->subject, $lead->reference)
                && str_contains($html, $lead->reference)
                && str_contains($html, 'Jordan')
                && str_contains($html, 'NG1 1AA');
        });

        Mail::assertSent(CustomerQuoteAcknowledgementMail::class, function (CustomerQuoteAcknowledgementMail $mail) use ($lead): bool {
            $html = $mail->render();

            return $mail->quoteRequest->is($lead)
                && str_contains($mail->envelope()->subject, $lead->reference)
                && str_contains($html, $lead->reference)
                && str_contains($html, 'fixed price in writing');
        });
    }

    public function test_lead_survives_notification_dispatch_failure(): void
    {
        Notification::fake();

        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        $customer = Customer::query()->create([
            'first_name' => 'Casey',
            'last_name' => 'Ng',
            'phone_normalized' => '07503651476',
            'phone_display' => '07503 651476',
            'email' => 'casey@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '2 Test Street',
            'city' => 'Nottingham',
        ]);

        $lead = QuoteRequest::query()->create([
            'reference' => 'NG-MAIL1',
            'customer_id' => $customer->id,
            'service_id' => $regular->id,
            'source' => QuoteRequestSource::Web,
            'status' => 'new',
            'first_name' => 'Casey',
            'last_name' => 'Ng',
            'phone' => '07503651476',
            'email' => 'casey@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '2 Test Street',
            'city' => 'Nottingham',
            'preferred_date' => now()->addWeek()->toDateString(),
            'arrival_window' => 'flexible',
            'submitted_at' => now(),
            'is_numeric_estimate' => true,
            'guide_estimate_headline' => 'From £90',
        ]);

        Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP unavailable'));

        app(DispatchQuoteRequestNotifications::class)->handle($lead);

        $this->assertDatabaseHas('quote_requests', [
            'reference' => 'NG-MAIL1',
            'email' => 'casey@example.com',
        ]);
    }

    public function test_internal_mail_uses_configured_notification_recipients(): void
    {
        Mail::fake();
        Notification::fake();

        SiteSetting::instance()->update([
            'lead_notification_emails' => ['ops@nghomecleaners.test', 'owner@nghomecleaners.test'],
        ]);
        app(SiteSettingsService::class)->forget();

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

        Mail::assertSent(InternalQuoteRequestMail::class, 2);
    }
}
