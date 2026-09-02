<?php

namespace Tests\Feature;

use App\Enums\EmailTemplateKey;
use App\Enums\QuoteRequestStatus;
use App\Filament\Resources\EmailTemplates\Pages\EditEmailTemplate;
use App\Filament\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Mail\CustomerQuoteAcknowledgementMail;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\EmailTemplateService;
use App\Support\Media;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EmailTemplatesCrmTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        $this->admin = User::factory()->create();
    }

    public function test_cms_seeder_creates_fixed_email_templates(): void
    {
        $this->assertSame(
            count(EmailTemplateKey::cases()),
            EmailTemplate::query()->count(),
        );

        foreach (EmailTemplateKey::cases() as $key) {
            $this->assertDatabaseHas('email_templates', [
                'key' => $key->value,
            ]);
        }
    }

    public function test_admin_can_list_and_edit_email_templates_but_not_create(): void
    {
        $this->actingAs($this->admin)
            ->get(route('filament.admin.resources.email-templates.index'))
            ->assertOk()
            ->assertSee('Customer quote acknowledgement', false);

        Livewire::actingAs($this->admin)
            ->test(ListEmailTemplates::class)
            ->assertOk();

        $template = EmailTemplate::for(EmailTemplateKey::CustomerQuoteAcknowledgement);

        Livewire::actingAs($this->admin)
            ->test(EditEmailTemplate::class, ['record' => $template->getKey()])
            ->fillForm([
                'subject' => 'Thanks {{first_name}} — we have {{reference}}',
                'heading' => 'Request received',
                'body' => "Hello {{first_name}},\n\nCustom CRM copy for **{{reference}}**.\n\nThanks,\n{{business_name}}",
            ])
            ->call('save')
            ->assertHasNoErrors();

        $template->refresh();

        $this->assertSame('Thanks {{first_name}} — we have {{reference}}', $template->subject);
        $this->assertSame('Request received', $template->heading);
        $this->assertStringContainsString('Custom CRM copy', $template->body);
        $this->assertSame(EmailTemplateKey::CustomerQuoteAcknowledgement, $template->key);
    }

    public function test_customer_acknowledgement_mail_uses_crm_copy_and_site_logo(): void
    {
        Storage::fake(Media::diskName());
        Storage::disk(Media::diskName())->put('brand/logo.png', 'fake-logo');

        SiteSetting::instance()->update([
            'business_name' => 'NG Home Cleaners',
            'logo_path' => 'brand/logo.png',
        ]);

        EmailTemplate::for(EmailTemplateKey::CustomerQuoteAcknowledgement)->update([
            'subject' => 'CRM subject {{reference}}',
            'heading' => 'CRM heading',
            'body' => "Hello {{first_name}},\n\nCustom acknowledgement for **{{reference}}**.\n\nThanks,\n{{business_name}}",
        ]);

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

        $lead = QuoteRequest::query()->create([
            'reference' => 'QR-9001',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'source' => 'web',
            'status' => QuoteRequestStatus::New,
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone' => '07503651476',
            'email' => 'alex@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '1 Test Street',
            'city' => 'Nottingham',
            'preferred_date' => now()->addWeek()->toDateString(),
            'arrival_window' => 'flexible',
            'property_type' => 'flat',
            'bedrooms' => 1,
            'floors' => 1,
            'guide_estimate_headline' => 'from £99',
            'selections_snapshot' => [],
            'pricing_snapshot' => [],
            'submitted_at' => now(),
        ]);

        $mail = new CustomerQuoteAcknowledgementMail($lead);
        $html = $mail->render();

        $this->assertSame('CRM subject QR-9001', $mail->envelope()->subject);
        $this->assertStringContainsString('CRM heading', $html);
        $this->assertStringContainsString('Custom acknowledgement', $html);
        $this->assertStringContainsString('QR-9001', $html);
        $this->assertStringContainsString('brand/logo.png', $html);
        $this->assertStringContainsString('NG Home Cleaners', $html);
    }

    public function test_email_template_key_cannot_be_changed_or_deleted(): void
    {
        $template = EmailTemplate::for(EmailTemplateKey::CustomerFinalQuote);
        $originalKey = $template->key;

        $template->update([
            'key' => EmailTemplateKey::CustomerReviewRequest->value,
            'subject' => 'Updated subject {{reference}}',
        ]);

        $template->refresh();

        $this->assertSame($originalKey, $template->key);
        $this->assertSame('Updated subject {{reference}}', $template->subject);

        $this->assertFalse($template->delete());
        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'key' => $originalKey->value,
        ]);
    }

    public function test_placeholder_replacement_strips_unknown_tokens_safely(): void
    {
        $html = app(EmailTemplateService::class)->renderBodyHtml(
            'Hello {{first_name}} — {{unknown_token}}',
            ['first_name' => 'Sam'],
        );

        $this->assertStringContainsString('Hello Sam', $html);
        $this->assertStringNotContainsString('{{unknown_token}}', $html);
    }
}
