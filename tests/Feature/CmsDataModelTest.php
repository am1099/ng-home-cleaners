<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CmsDataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_addon_uses_guide_price_range_columns(): void
    {
        $columns = Schema::getColumnListing('addons');

        $this->assertContains('price_pence', $columns);
        $this->assertContains('price_max_pence', $columns);

        $addon = Addon::factory()->create([
            'price_pence' => 4500,
            'price_max_pence' => 5500,
        ]);

        $this->assertSame(4500, $addon->priceMinPence());
        $this->assertSame(5500, $addon->priceMaxPence());
    }

    public function test_site_settings_cache_is_invalidated_on_save(): void
    {
        $settings = SiteSetting::instance();
        Cache::forever(SiteSettingsService::CACHE_KEY, $settings->getAttributes());

        $settings->update(['business_name' => 'Updated Name']);

        $this->assertFalse(Cache::has(SiteSettingsService::CACHE_KEY));
    }

    public function test_service_active_scope_hides_inactive_records(): void
    {
        Service::factory()->create(['is_active' => true, 'slug' => 'active-service']);
        Service::factory()->create(['is_active' => false, 'slug' => 'inactive-service']);

        $this->assertCount(1, Service::query()->active()->get());
    }

    public function test_authenticated_user_can_access_service_admin_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/services')
            ->assertOk();
    }
}
