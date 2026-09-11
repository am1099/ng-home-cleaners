<?php

namespace Tests\Feature;

use App\Enums\ServiceIcon;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\RelationManagers\ExclusionsRelationManager;
use App\Filament\Resources\Services\RelationManagers\InclusionsRelationManager;
use App\Models\Addon;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
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

    public function test_service_edit_page_keeps_detail_drawers_without_create_hint(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user)
            ->get('/admin/services/'.$service->getKey().'/edit')
            ->assertOk()
            ->assertSee('Service details', false)
            ->assertSee('Inclusions', false)
            ->assertSee('Optional add-ons', false)
            ->assertDontSee('Save the service first, then add inclusions here.', false);

        Livewire::actingAs($user)
            ->test(EditService::class, ['record' => $service->getKey()])
            ->assertOk()
            ->assertSeeLivewire(InclusionsRelationManager::class);
    }

    public function test_service_create_page_asks_to_save_before_detail_rows(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/services/create')
            ->assertOk()
            ->assertSee('Save the service first, then add inclusions here.', false);
    }

    public function test_inclusions_can_be_added_from_library_used_on_another_service(): void
    {
        $user = User::factory()->create();
        $source = Service::factory()->create(['icon' => ServiceIcon::House]);
        $target = Service::factory()->create(['icon' => ServiceIcon::Sparkles]);

        $source->inclusions()->create([
            'content' => 'Dust all surfaces',
            'sort_order' => 1,
        ]);
        $source->inclusions()->create([
            'content' => 'Mop hard floors',
            'sort_order' => 2,
        ]);

        Livewire::actingAs($user)
            ->test(InclusionsRelationManager::class, [
                'ownerRecord' => $target,
                'pageClass' => EditService::class,
            ])
            ->callTableAction('addFromLibrary', data: [
                'items' => ['Dust all surfaces', 'Mop hard floors'],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('service_inclusions', [
            'service_id' => $target->id,
            'content' => 'Dust all surfaces',
        ]);
        $this->assertDatabaseHas('service_inclusions', [
            'service_id' => $target->id,
            'content' => 'Mop hard floors',
        ]);
        $this->assertSame(2, $target->inclusions()->count());
    }

    public function test_exclusions_can_be_added_from_library_with_notes_copied(): void
    {
        $user = User::factory()->create();
        $source = Service::factory()->create(['icon' => ServiceIcon::Key]);
        $target = Service::factory()->create(['icon' => ServiceIcon::Building]);

        $source->exclusions()->create([
            'task' => 'External windows',
            'note' => 'Outside glass needs a window specialist.',
            'sort_order' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(ExclusionsRelationManager::class, [
                'ownerRecord' => $target,
                'pageClass' => EditService::class,
            ])
            ->callTableAction('addFromLibrary', data: [
                'tasks' => ['External windows'],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('service_exclusions', [
            'service_id' => $target->id,
            'task' => 'External windows',
            'note' => 'Outside glass needs a window specialist.',
        ]);
    }
}
