<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;
use ReflectionClass;
use Tests\TestCase;

class SecureImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_temporary_uploaded_file_uses_project_override_without_tmpfile(): void
    {
        $reflection = new ReflectionClass(TemporaryUploadedFile::class);

        $this->assertStringContainsString(
            'Overrides'.DIRECTORY_SEPARATOR.'Livewire',
            $reflection->getFileName() ?: '',
            'Composer must load app/Overrides/Livewire/TemporaryUploadedFile.php, not the vendor copy.'
        );

        $this->assertTrue(
            $reflection->hasMethod('resolveLocalPathname'),
            'Override must provide resolveLocalPathname() so construction never calls tmpfile().'
        );
    }

    public function test_site_settings_logo_upload_persists_to_public_disk(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $settings = SiteSetting::instance();

        $file = UploadedFile::fake()->image('logo.jpg', 200, 200);

        Livewire::actingAs($admin)
            ->test(SiteSettings::class)
            ->fillForm([
                'home_hero_title' => $settings->home_hero_title ?: 'There are better uses for a Saturday morning.',
                'home_hero_subtitle' => $settings->home_hero_subtitle ?: 'Your home cleaned by a vetted, DBS-checked cleaner.',
                'logo_path' => [$file],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = SiteSetting::instance()->fresh();

        $this->assertNotNull($settings->logo_path);
        Storage::disk('public')->assertExists($settings->logo_path);
    }
}
