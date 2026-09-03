<?php

namespace Tests\Feature;

use App\Filament\Resources\QuoteRequests\Pages\ListQuoteRequests;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingsService;
use App\Support\Media;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AuthAndPublicAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_crm_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/quote-requests')->assertRedirect('/admin/login');
        $this->get('/admin/bookings')->assertRedirect('/admin/login');
        $this->get('/admin/customers')->assertRedirect('/admin/login');
        $this->get('/admin/services')->assertRedirect('/admin/login');
    }

    public function test_public_pages_remain_public(): void
    {
        $service = Service::query()->active()->firstOrFail();

        $this->get('/')->assertOk();
        $this->get('/services')->assertOk();
        $this->get(route('services.show', $service))->assertOk();
        $this->get('/areas')->assertOk();
        $this->get('/about')->assertOk();
        $this->get('/contact')->assertOk();
        $this->get('/get-a-quote')->assertOk();
        $this->get('/up')->assertOk();
    }

    public function test_failed_login_keeps_guest_out_of_crm(): void
    {
        $this->seed(AdminUserSeeder::class);

        $this->post('/admin/login', [
            'email' => 'admin@nghomecleaners.co.uk',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_seeded_admin_can_reach_dashboard(): void
    {
        $this->seed(AdminUserSeeder::class);

        $user = User::query()->where('email', 'admin@nghomecleaners.co.uk')->firstOrFail();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_pages_use_site_favicon(): void
    {
        Storage::fake(Media::diskName());
        Storage::disk(Media::diskName())->put('brand/favicon.png', 'fake-favicon');

        SiteSetting::instance()->update(['favicon_path' => 'brand/favicon.png']);
        app(SiteSettingsService::class)->forget();

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('rel="icon"', false)
            ->assertSee('brand/favicon.png', false);
    }

    public function test_admin_record_tables_support_card_layout_toggle(): void
    {
        $this->seed(AdminUserSeeder::class);

        $user = User::query()->where('email', 'admin@nghomecleaners.co.uk')->firstOrFail();

        $this->actingAs($user)
            ->get('/admin/quote-requests')
            ->assertOk()
            ->assertSee('ng-table-layout-toggle', false)
            ->assertSee('Card view', false)
            ->assertSee('Table view', false);

        $component = Livewire::actingAs($user)
            ->test(ListQuoteRequests::class)
            ->call('setTableLayoutView', 'grid')
            ->assertSet('tableLayoutView', 'grid');

        $this->assertNotNull(
            $component->instance()->getTable()->getContentGrid(),
            'Card layout should enable Filament contentGrid.',
        );
        $this->assertTrue(
            $component->instance()->getTable()->hasColumnsLayout(),
            'Card layout should use stacked column layout components.',
        );
    }
}
