<?php

namespace Tests\Feature;

use App\Actions\DispatchQuoteRequestNotifications;
use App\Actions\SendCustomerFinalQuote;
use App\Enums\QuoteRequestStatus;
use App\Filament\Resources\QuoteRequests\Pages\EditQuoteRequest;
use App\Mail\CustomerFinalQuoteMail;
use App\Mail\CustomerQuoteAcknowledgementMail;
use App\Mail\InternalQuoteRequestMail;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingsService;
use App\Support\Media;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class QuoteRequestAdminEmailTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        $this->admin = User::factory()->create();
    }

    public function test_resend_lead_emails_queues_internal_and_customer_messages(): void
    {
        Mail::fake();

        $lead = $this->createLead();

        app(DispatchQuoteRequestNotifications::class)->handle($lead);

        Mail::assertQueued(InternalQuoteRequestMail::class);
        Mail::assertQueued(CustomerQuoteAcknowledgementMail::class);
    }

    public function test_resend_lead_emails_skips_customer_message_without_email(): void
    {
        Mail::fake();

        $lead = $this->createLead(['email' => null]);

        app(DispatchQuoteRequestNotifications::class)->handle($lead);

        Mail::assertQueued(InternalQuoteRequestMail::class);
        Mail::assertNotQueued(CustomerQuoteAcknowledgementMail::class);
    }

    public function test_send_customer_final_quote_queues_email_and_marks_quote_sent(): void
    {
        Mail::fake();

        $lead = $this->createLead(['final_quote_amount_pence' => 15000]);

        app(SendCustomerFinalQuote::class)->handle($lead);

        Mail::assertQueued(CustomerFinalQuoteMail::class, function (CustomerFinalQuoteMail $mail) use ($lead): bool {
            $html = $mail->render();

            return $mail->quoteRequest->reference === $lead->reference
                && str_contains($html, '£150')
                && str_contains($html, $lead->reference);
        });

        $lead->refresh();

        $this->assertSame(QuoteRequestStatus::QuoteSent, $lead->status);
        $this->assertNotNull($lead->quote_sent_at);
    }

    public function test_saving_final_quote_from_admin_edit_queues_customer_final_quote_mail(): void
    {
        Mail::fake();

        $lead = $this->createLead();

        Livewire::actingAs($this->admin)
            ->test(EditQuoteRequest::class, ['record' => $lead->getKey()])
            ->fillForm([
                'final_quote_amount_pence' => '125.00',
            ])
            ->call('save')
            ->assertHasNoErrors();

        Mail::assertQueued(CustomerFinalQuoteMail::class);

        $lead->refresh();

        $this->assertSame(12500, $lead->final_quote_amount_pence);
        $this->assertSame(QuoteRequestStatus::QuoteSent, $lead->status);
    }

    public function test_footer_logo_is_used_in_public_footer(): void
    {
        Storage::fake(Media::diskName());
        Storage::disk(Media::diskName())->put('brand/footer/footer-logo.png', 'fake-image');

        SiteSetting::instance()->update(['footer_logo_path' => 'brand/footer/footer-logo.png']);
        app(SiteSettingsService::class)->forget();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('brand/footer/footer-logo.png', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createLead(array $overrides = []): QuoteRequest
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        $customer = Customer::query()->create([
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone_normalized' => '07503651476',
            'phone_display' => '07503 651476',
            'email' => 'alex@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '1 Test Street',
            'city' => 'Nottingham',
        ]);

        return QuoteRequest::query()->create(array_merge([
            'reference' => 'NG-EMAIL1',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'source' => 'web',
            'status' => QuoteRequestStatus::New,
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone' => '07503 651476',
            'email' => 'alex@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '1 Test Street',
            'city' => 'Nottingham',
            'preferred_date' => now()->addWeek()->toDateString(),
            'arrival_window' => 'flexible',
            'submitted_at' => now(),
            'is_numeric_estimate' => true,
            'guide_estimate_headline' => 'From £90',
        ], $overrides));
    }
}
