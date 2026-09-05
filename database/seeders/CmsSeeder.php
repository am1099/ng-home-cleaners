<?php

namespace Database\Seeders;

use App\Enums\AddonPricingUnit;
use App\Enums\ConditionFlag;
use App\Enums\PropertyType;
use App\Enums\RoomModifierType;
use App\Enums\ServiceIcon;
use App\Models\Addon;
use App\Models\Faq;
use App\Models\LegalPage;
use App\Models\PricingBedroomRule;
use App\Models\PricingCondition;
use App\Models\PricingExtraRoom;
use App\Models\PricingSetting;
use App\Models\PricingStartingPrice;
use App\Models\RecentWork;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\ServiceExclusion;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Services\EmailTemplateService;
use App\Support\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::instance();
        PricingSetting::instance();
        app(EmailTemplateService::class)->ensureDefaults();

        $this->seedSiteContent();
        $this->seedServices();
        $this->seedAddons();
        $this->seedSimplifiedPricing();
        $this->seedServiceAreas();
        $this->seedFaqs();
        $this->seedLegalPages();
        $this->seedDemoTestimonials();
        $this->seedDemoRecentWork();
    }

    private function seedSiteContent(): void
    {
        SiteSetting::query()->first()?->update([
            'home_hero_title' => 'There are better uses for a Saturday morning.',
            'home_hero_subtitle' => 'Your home cleaned by a vetted, DBS-checked cleaner working to a written standard, with a fixed price agreed before we start.',
            'about_hero_title' => 'A small Nottingham team, not a national franchise.',
            'about_hero_subtitle' => 'A family-run team working across NG1 to NG16. We have no plans to spread thinner than that.',
            'about_story' => "Every clean is carried out by a vetted, DBS-checked cleaner working to our checklist. Not a rota of strangers. You will always know who is coming to your home.\n\nWe quote a fixed price before we start, and that is the number you pay. If we arrive and the property is in worse condition than described, you hear what it will cost before we do the work.",
            'about_promises' => [
                'Cover when your regular cleaner is away, with a vetted replacement that same week.',
                'Someone to ring when something goes wrong, rather than having that conversation with the person who did the work.',
                'Insurance if something breaks. We tell you the same day and our cover pays for it.',
                'A 48-hour re-clean guarantee if we miss anything on the agreed checklist.',
            ],
            'how_it_works_steps' => [
                ['title' => 'Tell us about your home', 'body' => 'Postcode, rooms, which clean and when. Send a one-minute walkthrough video with it and the price comes back tighter.'],
                ['title' => 'We put it in writing', 'body' => 'Within one working day, with every task listed. If the property is worse than described, we agree the extra before we start.'],
                ['title' => 'Meet your cleaner', 'body' => 'You meet whoever is cleaning your home before the first visit, and we tell you in advance who is coming.'],
                ['title' => 'Pay whichever way suits you', 'body' => 'In full by card before the clean, or half to hold the slot and the balance on the day. No contract and no subscription.'],
            ],
            'why_choose_items' => [
                ['title' => 'Cover when yours is away', 'body' => 'Illness or holiday means a vetted replacement that same week.'],
                ['title' => 'Someone to ring when it goes wrong', 'body' => 'A missed room or a clean that was not up to standard is raised with us, not left with you to sort alone.'],
                ['title' => 'The bill if something breaks', 'body' => 'If we damage something we tell you the same day and our public liability cover pays for it.'],
            ],
            'show_google_reviews' => true,
            'show_dbs_statement' => true,
            'dbs_statement' => 'Every cleaner is DBS-checked and referenced before their first job.',
            'show_insurance_statement' => true,
            'insurance_amount' => '£1m',
            'insurance_statement' => '£1m public liability insurance on every visit.',
            'guarantee_statement' => 'If anything is missed, tell us within 48 hours and we come back and finish it at our cost.',
            'default_seo_title' => 'Professional Home Cleaners Nottingham',
            'default_seo_description' => 'Vetted, DBS-checked cleaners across Nottingham and surrounding areas. Fixed prices agreed in writing before we start.',
            'lead_notification_emails' => ['hello@nghomecleaners.co.uk'],
        ]);
    }

    private function seedServices(): void
    {
        $services = [
            [
                'name' => 'Regular clean',
                'slug' => 'regular-clean',
                'card_title' => 'Regular clean',
                'short_description' => 'Get your evenings and Sundays back. Weekly or fortnightly on a day that suits you.',
                'estimate_description' => 'Dusting, hoovering, mopping, bathrooms and toilets. Two-hour minimum visit.',
                'full_description' => 'A regular upkeep clean for homes that already have a reasonable baseline standard.',
                'icon' => ServiceIcon::House,
                'cta_label' => 'Book my first clean',
                'seo_title' => 'Regular House Cleaning Nottingham | NG Home Cleaners',
                'seo_description' => 'Weekly or fortnightly regular cleans across Nottingham. Dusting, hoovering, mopping and bathrooms, with a fixed price agreed before we start.',
                'sort_order' => 1,
                'inclusions' => [
                    'Dusting throughout',
                    'Hoovering all floors and carpets',
                    'Mopping hard floors',
                    'Bathrooms and toilets cleaned',
                ],
            ],
            [
                'name' => 'Deep clean',
                'slug' => 'deep-clean',
                'card_title' => 'Deep clean',
                'short_description' => 'For the house you would rather nobody saw. Kitchen and hob degreased, skirting boards, inside cabinets, limescale.',
                'estimate_description' => 'Kitchen and hob degreased, skirting boards, inside cabinets and limescale. Oven interior and internal windows are optional add-ons.',
                'full_description' => 'A top-to-bottom reset before regular upkeep or before hosting.',
                'icon' => ServiceIcon::Sparkles,
                'seo_title' => 'Deep Cleaning Nottingham | NG Home Cleaners',
                'seo_description' => 'Deep cleans for Nottingham homes that need a proper reset. Kitchen degreasing, skirting boards, cabinets and limescale included.',
                'sort_order' => 2,
                'inclusions' => [
                    'Everything in a regular clean',
                    'Kitchen and hob degreased',
                    'Skirting boards cleaned',
                    'Inside cabinets and cupboards',
                    'Limescale, tiles and shower screens',
                ],
            ],
            [
                'name' => 'End of tenancy clean',
                'slug' => 'end-of-tenancy',
                'card_title' => 'End of tenancy',
                'short_description' => 'Your deposit is the reason this clean exists. Cleaned to inventory standard.',
                'estimate_description' => 'Cleaned to inventory standard so your agent has nothing to deduct for.',
                'full_description' => 'Move-out cleaning for tenants and landlords across Nottingham.',
                'icon' => ServiceIcon::Key,
                'seo_title' => 'End of Tenancy Cleaning Nottingham | NG Home Cleaners',
                'seo_description' => 'End of tenancy cleans across Nottingham, cleaned to inventory standard so agents have less to deduct from your deposit.',
                'sort_order' => 3,
                'inclusions' => [
                    'Everything in a deep clean',
                    'Inside all wardrobes and drawers',
                    'Wall marks, doors and light fittings',
                    'Fridge, freezer and appliances emptied and cleaned',
                    'Cleaned to inventory standard',
                ],
            ],
            [
                'name' => 'Office & commercial',
                'slug' => 'office-commercial',
                'card_title' => 'Office & commercial',
                'short_description' => 'Evenings and weekends, worked around your opening hours and priced per visit after a walk-round.',
                'estimate_description' => 'Commercial premises are quoted after a short walk-round, usually within a few days.',
                'full_description' => 'Small commercial premises cleaned around your opening hours.',
                'icon' => ServiceIcon::Building,
                'seo_title' => 'Office and Commercial Cleaning Nottingham | NG Home Cleaners',
                'seo_description' => 'Small commercial cleaning in Nottingham, evenings and weekends around your opening hours, priced after a short walk-round.',
                'sort_order' => 4,
                'inclusions' => [
                    'Desks, touch points, glass and reception',
                    'Kitchens, washrooms and consumables',
                    'Floors, bins and waste',
                    'Evenings and weekends around opening hours',
                ],
            ],
        ];

        foreach ($services as $index => $data) {
            $inclusions = $data['inclusions'];
            unset($data['inclusions']);

            $service = Service::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [...$data, 'is_active' => true],
            );

            foreach ($inclusions as $sort => $content) {
                $service->inclusions()->updateOrCreate(
                    ['content' => $content],
                    ['sort_order' => $sort + 1],
                );
            }
        }

        $this->seedServiceExclusions();
    }

    private function seedServiceExclusions(): void
    {
        $global = [
            ['Inside oven, fridge or freezer on a regular clean', 'Included on deep cleans and end of tenancy. Addable from the estimate form.'],
            ['External windows', 'Internal glass can be added. Outside glass needs a window specialist.'],
            ['Heavy mould or limescale treatment', 'Light surface mould is fine. Structural or heavy build-up needs a specialist treatment - ask on the quote.'],
            ['Carpet shampoo or upholstery deep clean', 'We vacuum thoroughly. Wet extraction is a separate trade.'],
            ['Hoarding clearance or rubbish removal beyond one bin bag', 'We can quote rubbish removal as an add-on where it is manageable.'],
            ['Laundry, ironing or making beds with fresh linen', 'Surfaces and floors are our job. Soft furnishings and laundry are not.'],
            ['Moving heavy furniture or white goods', 'We clean around fixed items. Ask if you need help shifting lighter pieces.'],
            ['Biohazard, flood or fire restoration', 'We clean homes - not disaster recovery. We will tell you if a specialist is needed.'],
        ];

        Service::query()->each(function (Service $service) use ($global): void {
            foreach ($global as $sort => [$task, $note]) {
                ServiceExclusion::query()->updateOrCreate(
                    ['service_id' => $service->id, 'task' => $task],
                    ['note' => $note, 'sort_order' => $sort + 1],
                );
            }
        });
    }

    private function seedAddons(): void
    {
        $addons = [
            ['name' => 'Single oven', 'slug' => 'single-oven', 'label' => 'Oven interior (single)', 'price_pence' => 4500, 'price_max_pence' => 5500, 'pricing_unit' => AddonPricingUnit::Flat],
            ['name' => 'Double oven', 'slug' => 'double-oven', 'label' => 'Double oven or range cooker', 'price_pence' => 7000, 'price_max_pence' => 8500, 'pricing_unit' => AddonPricingUnit::Flat],
            ['name' => 'Fridge and freezer', 'slug' => 'fridge-freezer', 'label' => 'Inside fridge and freezer', 'price_pence' => 4000, 'price_max_pence' => 5000, 'pricing_unit' => AddonPricingUnit::Flat],
            ['name' => 'Inside wardrobes', 'slug' => 'inside-wardrobes', 'label' => 'Inside wardrobes and drawers', 'price_pence' => 3500, 'price_max_pence' => 4500, 'pricing_unit' => AddonPricingUnit::Flat],
            ['name' => 'Wall wipe', 'slug' => 'wall-wipe', 'label' => 'Light wall wipe-down throughout', 'price_pence' => 4500, 'price_max_pence' => 6500, 'pricing_unit' => AddonPricingUnit::Flat],
            ['name' => 'Limescale treatment', 'slug' => 'limescale-treatment', 'label' => 'Heavy limescale or mould treatment (per bathroom)', 'price_pence' => 3000, 'price_max_pence' => 7000, 'pricing_unit' => AddonPricingUnit::PerBathroom],
            ['name' => 'Rubbish removal', 'slug' => 'rubbish-removal', 'label' => 'Rubbish removal', 'price_pence' => 4000, 'price_max_pence' => 12000, 'pricing_unit' => AddonPricingUnit::Flat, 'show_from_prefix' => true],
        ];

        foreach ($addons as $sort => $addon) {
            Addon::query()->updateOrCreate(
                ['slug' => $addon['slug']],
                [...$addon, 'is_active' => true, 'sort_order' => $sort + 1],
            );
        }

        $deep = Service::query()->where('slug', 'deep-clean')->first();
        if ($deep) {
            $deep->addons()->sync(Addon::query()->pluck('id'));
        }
    }

    private function seedSimplifiedPricing(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->first();
        $deep = Service::query()->where('slug', 'deep-clean')->first();
        $eot = Service::query()->where('slug', 'end-of-tenancy')->first();

        if (! $regular || ! $deep || ! $eot) {
            return;
        }

        $starting = [
            $regular->id => [
                PropertyType::Flat->value => [7500, 8500],
                PropertyType::House->value => [7500, 9000],
                PropertyType::Bungalow->value => [7500, 9000],
            ],
            $deep->id => [
                PropertyType::Flat->value => [15500, 18000],
                PropertyType::House->value => [17000, 20000],
                PropertyType::Bungalow->value => [17000, 20000],
            ],
            $eot->id => [
                PropertyType::Flat->value => [20500, 23500],
                PropertyType::House->value => [22500, 25500],
                PropertyType::Bungalow->value => [22500, 25500],
            ],
        ];

        foreach ($starting as $serviceId => $rows) {
            foreach ($rows as $propertyType => [$min, $max]) {
                PricingStartingPrice::query()->updateOrCreate(
                    ['service_id' => $serviceId, 'property_type' => $propertyType],
                    ['min_pence' => $min, 'max_pence' => $max],
                );
            }
        }

        $bedroomRules = [
            $regular->id => [1, 2500, 3500],
            $deep->id => [1, 3000, 3500],
            $eot->id => [1, 3500, 4500],
        ];

        foreach ($bedroomRules as $serviceId => [$included, $min, $max]) {
            PricingBedroomRule::query()->updateOrCreate(
                ['service_id' => $serviceId],
                [
                    'bedrooms_included' => $included,
                    'extra_min_pence' => $min,
                    'extra_max_pence' => $max,
                ],
            );
        }

        $roomRates = [
            $regular->id => [
                [RoomModifierType::Bathroom, 'Extra bathroom', 1800, 2000],
                [RoomModifierType::Wc, 'Separate toilet (WC)', 600, 800],
                [RoomModifierType::Kitchen, 'Extra kitchen', 1200, 1500],
                [RoomModifierType::Reception, 'Extra reception room', 1200, 1500],
                [RoomModifierType::Floor, 'Extra floor', 600, 800],
                [RoomModifierType::ExtraRoom, 'Extra room', 600, 800],
            ],
            $deep->id => [
                [RoomModifierType::Bathroom, 'Extra bathroom', 4000, 5000],
                [RoomModifierType::Wc, 'Separate toilet (WC)', 1500, 1800],
                [RoomModifierType::Kitchen, 'Extra kitchen', 2800, 3500],
                [RoomModifierType::Reception, 'Extra reception room', 2800, 3500],
                [RoomModifierType::Floor, 'Extra floor', 1500, 2000],
                [RoomModifierType::ExtraRoom, 'Extra room', 1500, 2000],
            ],
            $eot->id => [
                [RoomModifierType::Bathroom, 'Extra bathroom', 4000, 5000],
                [RoomModifierType::Wc, 'Separate toilet (WC)', 1500, 1800],
                [RoomModifierType::Kitchen, 'Extra kitchen', 2800, 3500],
                [RoomModifierType::Reception, 'Extra reception room', 2800, 3500],
                [RoomModifierType::Floor, 'Extra floor', 1500, 2000],
                [RoomModifierType::ExtraRoom, 'Extra room', 1500, 2000],
            ],
        ];

        foreach ($roomRates as $serviceId => $rows) {
            foreach ($rows as $sort => [$type, $label, $min, $max]) {
                PricingExtraRoom::query()->updateOrCreate(
                    ['service_id' => $serviceId, 'room_type' => $type],
                    [
                        'label' => $label,
                        'min_pence' => $min,
                        'max_pence' => $max,
                        'sort_order' => $sort + 1,
                    ],
                );
            }
        }

        $conditionRates = [
            $regular->id => [800, 1200],
            $deep->id => [1500, 2500],
            $eot->id => [2000, 3000],
        ];

        foreach ($conditionRates as $serviceId => [$min, $max]) {
            foreach (ConditionFlag::cases() as $flag) {
                PricingCondition::query()->updateOrCreate(
                    ['service_id' => $serviceId, 'condition_flag' => $flag],
                    ['min_pence' => $min, 'max_pence' => $max],
                );
            }
        }
    }

    private function seedServiceAreas(): void
    {
        $areas = [
            ['NG1', 'city-centre', 'City centre', 'Apartments and city-centre lets, often between tenants. Parking arranged with you before the first visit.'],
            ['NG2', 'west-bridgford', 'West Bridgford', 'Family homes and rentals south of the Trent. One of our busiest districts for fortnightly cleans.'],
            ['NG3', 'mapperley-and-st-anns', 'Mapperley and St Ann\'s', 'Victorian terraces and semis, where a deep clean usually comes before a regular routine starts.'],
            ['NG5', 'sherwood-and-arnold', 'Sherwood and Arnold', 'Larger family houses. Weekday morning slots are usually the easiest to get here.'],
            ['NG6', 'bulwell', 'Bulwell', 'Houses and landlord turnarounds, booked around your check-out or re-let date.'],
            ['NG7', 'lenton-and-radford', 'Lenton and Radford', 'Student lets and shared houses. End of tenancy work to inventory standard, with the deposit in mind.'],
            ['NG8', 'wollaton-and-bilborough', 'Wollaton and Bilborough', 'Detached and semi-detached homes, often a one-off deep clean before hosting.'],
            ['NG9', 'beeston-and-stapleford', 'Beeston and Stapleford', 'Professionals and families. Weekly or fortnightly upkeep on a set day.'],
            ['NG11', 'clifton-and-ruddington', 'Clifton and Ruddington', 'Suburban and village homes to the south. Travel inside the district is included.'],
            ['NG12', 'radcliffe-and-keyworth', 'Radcliffe and Keyworth', 'Village properties east and south, from cottages to newer family homes.'],
            ['NG14', 'burton-joyce-and-lowdham', 'Burton Joyce and Lowdham', 'Edge-of-city villages along the Trent, including larger detached properties.'],
            ['NG16', 'eastwood-and-kimberley', 'Eastwood and Kimberley', 'North-west of the city. Regular customers here are usually booked in a standing weekly slot.'],
        ];

        foreach ($areas as $sort => [$code, $slug, $name, $intro]) {
            ServiceArea::query()->updateOrCreate(
                ['postcode_label' => $code],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'short_intro' => $intro,
                    'content' => "We clean homes and small commercial premises across {$name} ({$code}). Travel inside NG1 to NG16 is included in your quote. If you are booking a regular clean, deep clean or end of tenancy job here, tell us the full postcode and we will confirm the slot.",
                    'seo_title' => "{$name} Home Cleaning ({$code}) | NG Home Cleaners",
                    'seo_description' => "Domestic cleaning in {$name}, Nottingham {$code}. {$intro}",
                    'is_active' => true,
                    'sort_order' => $sort + 1,
                ],
            );
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            [
                'How does it work out on cost?',
                'Known before we start. Fill in the estimate form and you see your range instantly - it depends on the property, so a 3 bed in Beeston and a 3 bed student let are priced differently. Your fixed price follows in writing within one working day.',
            ],
            [
                'How and when do I pay?',
                'Pay in full by card before the clean, or pay a 50% deposit to secure the slot and the balance on the day. Regular weekly and fortnightly customers skip the deposit and are invoiced per visit. No cash needed, no subscription, and the total is the fixed written price you agreed - we do not add to it afterwards.',
            ],
            [
                'Why are you more than the cleaner down the road?',
                'Because an independent cleaner is cheaper by leaving costs with you - no cover when she is ill, no insurance if something breaks, no DBS check on the person let into your home, and nobody to raise it with but her. We carry all four. If that is not worth the difference to you, an independent cleaner is genuinely the better buy and we will say so.',
            ],
            [
                'Do I need to be home during the clean?',
                'No, and most customers are not. You meet your cleaner first, then agree how they get in on the day. Every cleaner is DBS-checked and referenced before their first job.',
            ],
            [
                'When could you start?',
                'Tell us the date you have in mind on the quote form and we will confirm it or offer you the closest we can. Moving out on a fixed date, or need a Saturday? Say so and we will work to it.',
            ],
            [
                'What if there is more work than expected?',
                'We quote from the details and the walkthrough video you send. If the property turns out to need considerably more work than that showed, the cleaner stops and rings you with a number before carrying on. You will never find extra labour added to an invoice you have not agreed to.',
            ],
            [
                'Do you bring products and equipment?',
                'Yes, and they are in the price. If you would rather we used your own products, or need the eco-friendly range for a child or a pet, say so when you book - it costs nothing extra.',
            ],
            [
                'What if the clean is not up to standard?',
                'Tell us within 48 hours and we come back and finish it at our cost.',
            ],
        ];

        // Replace the previous short set so homepage FAQs match the live copy.
        Faq::query()->whereIn('question', [
            'How does it work out on cost?',
            'How and when do I pay?',
            'Do I need to be home during the clean?',
            'Do you bring products and equipment?',
            'What if the clean is not up to standard?',
            'Why are you more than the cleaner down the road?',
            'When could you start?',
            'What if there is more work than expected?',
            'How do I pay?',
            'Do I need to be in during the clean?',
            'Do you supply products and equipment?',
            'What if the clean is not up to scratch?',
        ])->delete();

        foreach ($faqs as $sort => [$question, $answer]) {
            Faq::query()->updateOrCreate(
                ['question' => $question],
                [
                    'answer' => $answer,
                    'sort_order' => $sort + 1,
                    'is_published' => true,
                ],
            );
        }
    }

    private function seedLegalPages(): void
    {
        $pages = [
            'privacy' => [
                'title' => 'Privacy policy',
                'seo_title' => 'Privacy policy',
                'seo_description' => 'How NG Home Cleaners uses contact details from cleaning enquiries and estimate requests.',
                'content' => "We use your contact details to respond to cleaning enquiries and provide quotes. We do not sell your data or add you to marketing lists without consent.\n\nIf you submit an estimate request, we store the details you provide so we can quote accurately and contact you about your booking.",
            ],
            'terms' => [
                'title' => 'Terms of service',
                'seo_title' => 'Terms of service',
                'seo_description' => 'Booking terms for NG Home Cleaners, including fixed quotes, access and cancellation timing.',
                'content' => "Quotes are fixed in writing before work begins. If the property is materially worse than described, we agree any change with you before continuing.\n\nCancellations more than 24 hours before a visit are free. Inside 24 hours, or if we cannot gain access, half the visit fee may apply.",
            ],
            'cookies' => [
                'title' => 'Cookie policy',
                'seo_title' => 'Cookie policy',
                'seo_description' => 'Essential cookies used by the NG Home Cleaners website. We do not use advertising cookies.',
                'content' => 'This site uses essential cookies required for basic functionality and security. We do not use advertising cookies.',
            ],
        ];

        foreach ($pages as $slug => $data) {
            LegalPage::query()->updateOrCreate(
                ['slug' => $slug],
                [...$data, 'is_published' => true],
            );
        }
    }

    private function seedDemoTestimonials(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $deep = Service::query()->where('slug', 'deep-clean')->first();

        Testimonial::query()->updateOrCreate(
            ['customer_name' => 'Demo customer A'],
            [
                'rating' => 5,
                'review' => 'Demo review for local development only. Professional deep clean with excellent attention to detail.',
                'location' => 'Nottingham',
                'service_id' => $deep?->id,
                'source' => 'Demo',
                'is_published' => true,
                'published_at' => now(),
                'is_demo' => true,
                'sort_order' => 1,
            ],
        );
    }

    /**
     * Placeholder before/after cards for local visual QA only.
     * Uses simple SVG assets - replace with real photography before production.
     */
    private function seedDemoRecentWork(): void
    {
        if (app()->isProduction()) {
            return;
        }

        SiteSetting::query()->first()?->update(['show_recent_work' => true]);

        $pairs = [
            [
                'slug' => 'bathroom-floor',
                'title' => 'Bathroom floor',
                'description' => 'GROUND-IN GRIME LIFTED',
                'before_label' => 'Bathroom floor before deep clean',
                'after_label' => 'Bathroom floor after deep clean',
                'before_fill' => '#8a7f72',
                'after_fill' => '#e8e4dc',
            ],
            [
                'slug' => 'limescale-removal',
                'title' => 'Limescale removal',
                'description' => 'HARD-WATER BUILD-UP GONE',
                'before_label' => 'Tap and tile limescale before cleaning',
                'after_label' => 'Tap and tile after limescale treatment',
                'before_fill' => '#6b7c7a',
                'after_fill' => '#f3f1ec',
            ],
        ];

        foreach ($pairs as $sort => $pair) {
            $beforePath = "recent-work/{$pair['slug']}-before.svg";
            $afterPath = "recent-work/{$pair['slug']}-after.svg";

            Storage::disk(Media::diskName())->put($beforePath, $this->demoSvg($pair['before_fill'], 'BEFORE'));
            Storage::disk(Media::diskName())->put($afterPath, $this->demoSvg($pair['after_fill'], 'AFTER'));

            RecentWork::query()->updateOrCreate(
                ['title' => $pair['title']],
                [
                    'before_image_path' => $beforePath,
                    'after_image_path' => $afterPath,
                    'description' => $pair['description'],
                    'alt_text_before' => $pair['before_label'],
                    'alt_text_after' => $pair['after_label'],
                    'sort_order' => $sort + 1,
                    'is_published' => true,
                    'published_at' => now(),
                ],
            );
        }
    }

    private function demoSvg(string $fill, string $label): string
    {
        $safe = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600" role="img">
          <rect width="800" height="600" fill="{$fill}"/>
          <text x="400" y="310" text-anchor="middle" fill="#1a1a1a" font-family="Arial, sans-serif" font-size="42" font-weight="700">{$safe}</text>
          <text x="400" y="360" text-anchor="middle" fill="#1a1a1a" font-family="Arial, sans-serif" font-size="18">Demo placeholder - replace with real photos</text>
        </svg>
        SVG;
    }
}
