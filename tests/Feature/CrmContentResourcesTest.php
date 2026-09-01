<?php

namespace Tests\Feature;

use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Filament\Resources\LegalPages\Pages\ListLegalPages;
use App\Models\User;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrmContentResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        $this->actingAs(User::factory()->create());
    }

    public function test_staff_can_open_faq_and_legal_admin_lists(): void
    {
        Livewire::test(ListFaqs::class)->assertOk();
        Livewire::test(ListLegalPages::class)->assertOk();
    }
}
