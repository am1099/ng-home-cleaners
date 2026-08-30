<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceArea;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_seeder_creates_four_primary_services(): void
    {
        $this->seed(CmsSeeder::class);

        $this->assertCount(4, Service::query()->get());
        $this->assertTrue(Service::query()->where('slug', 'regular-clean')->exists());
        $this->assertTrue(Service::query()->where('slug', 'deep-clean')->exists());
        $this->assertTrue(Service::query()->where('slug', 'end-of-tenancy')->exists());
        $this->assertTrue(Service::query()->where('slug', 'office-commercial')->exists());
    }

    public function test_cms_seeder_creates_twelve_service_areas(): void
    {
        $this->seed(CmsSeeder::class);

        $this->assertCount(12, ServiceArea::query()->active()->get());
        $this->assertTrue(ServiceArea::query()->where('postcode_label', 'NG6')->exists());
        $this->assertTrue(ServiceArea::query()->where('postcode_label', 'NG16')->exists());
    }
}
