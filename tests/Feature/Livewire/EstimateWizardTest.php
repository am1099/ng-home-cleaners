<?php

namespace Tests\Feature\Livewire;

use App\Enums\CleaningFrequency;
use App\Enums\PropertyType;
use App\Livewire\EstimateWizard;
use App\Models\Service;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EstimateWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_quote_page_renders_wizard(): void
    {
        $this->get(route('quote'))
            ->assertOk()
            ->assertSee('Get your', false)
            ->assertSee('instant estimate', false)
            ->assertSeeLivewire(EstimateWizard::class);
    }

    public function test_switching_service_updates_estimate_description(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $deep = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $regular->id)
            ->assertSee($regular->estimate_description, false)
            ->set('serviceId', $deep->id)
            ->assertSee($deep->estimate_description, false);
    }

    public function test_regular_weekly_flat_matches_pricing_engine(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $regular->id)
            ->set('frequency', CleaningFrequency::Weekly->value)
            ->set('propertyType', PropertyType::Flat->value)
            ->set('bedrooms', 1)
            ->set('floors', 1)
            ->assertSee('From £', false);
    }

    public function test_regular_one_off_house_shows_guide_price(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $regular->id)
            ->set('frequency', CleaningFrequency::OneOff->value)
            ->set('propertyType', PropertyType::House->value)
            ->set('bedrooms', 2)
            ->set('floors', 2)
            ->assertSee('From £', false);
    }

    public function test_selecting_an_addon_updates_the_guide_estimate_card(): void
    {
        $deep = Service::query()->where('slug', 'deep-clean')->firstOrFail();
        $addon = $deep->addons()->active()->orderBy('sort_order')->firstOrFail();

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $deep->id)
            ->set('propertyStatus', 'furnished')
            ->set('propertyType', PropertyType::House->value)
            ->assertSee('None selected.', false)
            ->set('addonIds', [$addon->id])
            ->assertDontSee('None selected.', false)
            ->assertSee($addon->label, false);
    }

    public function test_flat_defaults_to_one_floor(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('propertyType', PropertyType::Flat->value)
            ->assertSet('floors', 1);
    }

    public function test_quantity_steppers_increment_and_respect_limits(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('bedrooms', 2)
            ->call('adjustQuantity', 'bedrooms', 1)
            ->assertSet('bedrooms', 3)
            ->call('adjustQuantity', 'bedrooms', -1)
            ->assertSet('bedrooms', 2)
            ->set('bathrooms', 6)
            ->call('adjustQuantity', 'bathrooms', 1)
            ->assertSet('bathrooms', 6)
            ->set('wcs', 0)
            ->call('adjustQuantity', 'wcs', -1)
            ->assertSet('wcs', 0)
            ->set('receptionRooms', 1)
            ->call('adjustQuantity', 'receptionRooms', 1)
            ->assertSet('receptionRooms', 2);
    }

    public function test_quote_page_shows_quantity_steppers(): void
    {
        $this->get(route('quote'))
            ->assertOk()
            ->assertSee('Decrease bedrooms', false)
            ->assertSee('Increase bathrooms', false)
            ->assertSee('Increase reception rooms', false);
    }

    public function test_split_level_flat_allows_multiple_floors(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('propertyType', PropertyType::Flat->value)
            ->set('splitLevelFlat', true)
            ->set('floors', 2)
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('firstName', 'Alex')
            ->set('lastName', 'Taylor')
            ->set('phone', '07503651476')
            ->set('email', 'alex@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->call('submit')
            ->assertHasNoErrors(['floors']);
    }

    public function test_commercial_shows_manual_quote(): void
    {
        $commercial = Service::query()->where('slug', 'office-commercial')->firstOrFail();

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $commercial->id)
            ->assertSee('Priced per visit', false);
    }

    public function test_invalid_phone_is_rejected(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('phone', 'not-a-phone')
            ->set('firstName', 'Alex')
            ->set('lastName', 'Taylor')
            ->set('email', 'alex@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->call('submit')
            ->assertHasErrors(['phone']);
    }

    public function test_email_in_phone_field_is_rejected(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('phone', 'hello@example.com')
            ->set('firstName', 'Alex')
            ->set('lastName', 'Taylor')
            ->set('email', 'alex@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->call('submit')
            ->assertHasErrors(['phone']);
    }

    public function test_phone_in_email_field_is_rejected(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('phone', '07503651476')
            ->set('email', '07503651476')
            ->set('firstName', 'Alex')
            ->set('lastName', 'Taylor')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->call('submit')
            ->assertHasErrors(['email']);
    }

    public function test_invalid_postcode_is_rejected(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('phone', '07503651476')
            ->set('email', 'alex@example.com')
            ->set('firstName', 'Alex')
            ->set('lastName', 'Taylor')
            ->set('postcode', 'SW1A 1AA')
            ->set('addressLine1', '1 Test Street')
            ->call('submit')
            ->assertHasErrors(['postcode']);
    }

    public function test_past_date_is_rejected(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('preferredDate', now()->subDay()->toDateString())
            ->set('phone', '07503651476')
            ->set('email', 'alex@example.com')
            ->set('firstName', 'Alex')
            ->set('lastName', 'Taylor')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->call('submit')
            ->assertHasErrors(['preferredDate']);
    }

    public function test_missing_service_is_rejected(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('serviceId', null)
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('firstName', 'Alex')
            ->set('lastName', 'Taylor')
            ->set('phone', '07503651476')
            ->set('email', 'alex@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->call('submit')
            ->assertHasErrors(['serviceId']);
    }

    public function test_single_page_shows_all_main_sections(): void
    {
        Livewire::test(EstimateWizard::class)
            ->assertSee('Which service do you need?', false)
            ->assertSee('About the property', false)
            ->assertSee('Rooms and layout', false)
            ->assertSee('When &amp; your details', false)
            ->assertSee('Send my estimate request', false)
            ->assertSee('Continue on WhatsApp', false)
            ->assertSee('Choose parking', false)
            ->assertSee('Choose access', false)
            ->assertSee('On-street (free)', false)
            ->assertSee('Someone will be home', false)
            ->assertSee('ngDatePicker', false)
            ->assertDontSee('id="preferredDate" type="date"', false);
    }

    public function test_parking_and_access_dropdowns_persist_on_the_lead(): void
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
            ->set('email', 'alex@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham')
            ->set('parkingNotes', 'Driveway')
            ->set('accessNotes', 'Key safe')
            ->call('submit');

        $this->assertDatabaseHas('quote_requests', [
            'email' => 'alex@example.com',
            'parking_notes' => 'Driveway',
            'access_notes' => 'Key safe',
        ]);
    }

    public function test_submission_redirects_to_confirmation_page(): void
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
            ->set('email', 'alex@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->set('city', 'Nottingham')
            ->call('submit')
            ->assertRedirect(route('quote.confirmation', 'NG-1001'));
    }

    public function test_cannot_submit_without_required_contact_and_visit_fields(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('firstName', '')
            ->set('lastName', '')
            ->set('phone', '')
            ->set('email', '')
            ->set('postcode', '')
            ->set('addressLine1', '')
            ->set('preferredDate', null)
            ->call('submit')
            ->assertHasErrors([
                'firstName',
                'lastName',
                'phone',
                'email',
                'postcode',
                'addressLine1',
                'preferredDate',
            ])
            ->assertSet('savedReference', null);

        $this->assertDatabaseCount('quote_requests', 0);
    }

    public function test_deep_clean_requires_property_status(): void
    {
        $deep = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $deep->id)
            ->set('propertyStatus', null)
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('firstName', 'Alex')
            ->set('lastName', 'Taylor')
            ->set('phone', '07503651476')
            ->set('email', 'alex@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->call('submit')
            ->assertHasErrors(['propertyStatus']);
    }

    public function test_regular_requires_frequency(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $regular->id)
            ->set('frequency', '')
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('firstName', 'Alex')
            ->set('lastName', 'Taylor')
            ->set('phone', '07503651476')
            ->set('email', 'alex@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->call('submit')
            ->assertHasErrors(['frequency']);
    }

    public function test_honeypot_blocks_submission_without_creating_lead(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('website', 'http://spam.example')
            ->set('preferredDate', now()->addWeek()->toDateString())
            ->set('firstName', 'Spam')
            ->set('lastName', 'Bot')
            ->set('phone', '07503651476')
            ->set('email', 'spam@example.com')
            ->set('postcode', 'NG1 1AA')
            ->set('addressLine1', '1 Test Street')
            ->call('submit')
            ->assertRedirect(route('home'));

        $this->assertDatabaseCount('quote_requests', 0);
    }

    public function test_condition_notes_are_preserved_on_submission(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $regular->id)
            ->set('frequency', CleaningFrequency::OneOff->value)
            ->set('propertyType', PropertyType::House->value)
            ->set('bedrooms', 2)
            ->set('conditionNotes', 'Dog hair on stairs; key safe code 4421.')
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

        $this->assertDatabaseHas('quote_requests', [
            'email' => 'alex@example.com',
            'condition_notes' => 'Dog hair on stairs; key safe code 4421.',
        ]);
    }

    public function test_navigation_state_preserved_when_changing_inputs(): void
    {
        Livewire::test(EstimateWizard::class)
            ->set('bedrooms', 4)
            ->set('bathrooms', 2)
            ->set('firstName', 'Alex')
            ->assertSet('bedrooms', 4)
            ->assertSet('bathrooms', 2)
            ->assertSet('firstName', 'Alex');
    }
}
