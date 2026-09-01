<?php

namespace Tests\Feature\Livewire;

use App\Enums\CleaningFrequency;
use App\Enums\PropertyType;
use App\Livewire\EstimateWizard;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Support\Media;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EstimateWizardPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        Storage::fake(Media::diskName());
    }

    public function test_submission_succeeds_without_photos(): void
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
            ->set('email', 'photos-none@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect();

        $lead = QuoteRequest::query()->where('email', 'photos-none@example.com')->firstOrFail();

        $this->assertEmpty($lead->property_photo_paths);
    }

    public function test_valid_photos_are_stored_on_the_media_disk(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $photos = [
            UploadedFile::fake()->image('kitchen.jpg', 400, 300),
            UploadedFile::fake()->image('bathroom.png', 400, 300),
        ];

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
            ->set('email', 'photos-ok@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham')
            ->set('propertyPhotos', $photos)
            ->call('submit')
            ->assertHasNoErrors();

        $lead = QuoteRequest::query()->where('email', 'photos-ok@example.com')->firstOrFail();

        $this->assertCount(2, $lead->property_photo_paths ?? []);

        foreach ($lead->property_photo_paths as $path) {
            $this->assertStringStartsWith('quote-requests/'.$lead->reference.'/', $path);
            Storage::disk(Media::diskName())->assertExists($path);
            $this->assertNotNull(Media::url($path));
        }
    }

    public function test_video_uploads_are_rejected(): void
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
            ->set('email', 'photos-video@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham')
            ->set('propertyPhotos', [
                UploadedFile::fake()->create('walkthrough.mp4', 400, 'video/mp4'),
            ])
            ->call('submit')
            ->assertHasErrors(['propertyPhotos.0']);

        $this->assertDatabaseMissing('quote_requests', [
            'email' => 'photos-video@example.com',
        ]);
    }

    public function test_oversized_photos_are_rejected(): void
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
            ->set('email', 'photos-big@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham')
            ->set('propertyPhotos', [
                UploadedFile::fake()->image('huge.jpg')->size(5121),
            ])
            ->call('submit')
            ->assertHasErrors(['propertyPhotos.0']);
    }

    public function test_more_than_eight_photos_are_rejected(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $photos = [];

        for ($i = 0; $i < 9; $i++) {
            $photos[] = UploadedFile::fake()->image("room-{$i}.jpg", 200, 200);
        }

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
            ->set('email', 'photos-nine@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham')
            ->set('propertyPhotos', $photos)
            ->call('submit')
            ->assertHasErrors(['propertyPhotos']);
    }
}
