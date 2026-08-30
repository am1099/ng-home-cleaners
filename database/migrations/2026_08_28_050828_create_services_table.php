<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('card_title');
            $table->text('short_description');
            $table->text('estimate_description');
            $table->longText('full_description')->nullable();
            $table->string('icon')->default('house');
            $table->string('cta_label')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('card_image')->nullable();
            $table->string('og_image')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('service_inclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['service_id', 'sort_order']);
        });

        Schema::create('service_exclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('task');
            $table->text('note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['service_id', 'sort_order']);
        });

        Schema::create('service_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['service_id', 'sort_order']);
        });

        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->text('disclaimer')->nullable();
            $table->unsignedInteger('price_pence');
            $table->string('pricing_unit')->default('flat');
            $table->boolean('show_from_prefix')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('addon_service', function (Blueprint $table) {
            $table->foreignId('addon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();

            $table->primary(['addon_id', 'service_id']);
        });

        Schema::create('pricing_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('property_type');
            $table->unsignedTinyInteger('bedrooms');
            $table->unsignedInteger('price_min_pence');
            $table->unsignedInteger('price_max_pence');
            $table->timestamps();

            $table->unique(['service_id', 'property_type', 'bedrooms']);
        });

        Schema::create('pricing_room_modifiers', function (Blueprint $table) {
            $table->id();
            $table->string('room_type')->unique();
            $table->string('label');
            $table->unsignedInteger('regular_min_pence');
            $table->unsignedInteger('regular_max_pence');
            $table->unsignedInteger('deep_min_pence');
            $table->unsignedInteger('deep_max_pence');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('pricing_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('condition_uplift_percent', 5, 2)->default(7);
            $table->decimal('max_condition_uplift_percent', 5, 2)->default(28);
            $table->decimal('furnished_multiplier', 5, 3)->default(1.050);
            $table->decimal('empty_multiplier', 5, 3)->default(0.920);
            $table->decimal('weekly_discount_percent', 5, 2)->default(5);
            $table->decimal('fortnightly_discount_percent', 5, 2)->default(2.5);
            $table->unsignedInteger('regular_min_pence')->default(5500);
            $table->decimal('range_narrow_percent_per_signal', 5, 2)->default(12);
            $table->decimal('max_range_narrow_percent', 5, 2)->default(50);
            $table->timestamps();
        });

        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('postcode_label', 10);
            $table->text('short_intro');
            $table->longText('content')->nullable();
            $table->text('coverage_notes')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('hero_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('service_area_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_area_id')->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['service_area_id', 'sort_order']);
        });

        Schema::create('service_area_service', function (Blueprint $table) {
            $table->foreignId('service_area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();

            $table->primary(['service_area_id', 'service_id']);
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->string('alt_text');
            $table->text('caption')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_area_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->unsignedTinyInteger('rating');
            $table->text('review');
            $table->string('location')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source')->default('Google');
            $table->string('source_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_demo')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->string('whatsapp_number')->nullable();
            $table->string('whatsapp_url')->nullable();
            $table->json('lead_notification_emails')->nullable();
            $table->json('opening_hours')->nullable();
            $table->text('service_area_summary')->nullable();
            $table->text('business_address')->nullable();
            $table->string('google_business_url')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('show_google_reviews')->default(false);
            $table->boolean('show_dbs_statement')->default(false);
            $table->text('dbs_statement')->nullable();
            $table->boolean('show_insurance_statement')->default(false);
            $table->string('insurance_amount')->nullable();
            $table->text('insurance_statement')->nullable();
            $table->text('guarantee_statement')->nullable();
            $table->string('default_seo_title')->nullable();
            $table->text('default_seo_description')->nullable();
            $table->string('default_og_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('service_area_service');
        Schema::dropIfExists('service_area_faqs');
        Schema::dropIfExists('service_areas');
        Schema::dropIfExists('pricing_settings');
        Schema::dropIfExists('pricing_room_modifiers');
        Schema::dropIfExists('pricing_bands');
        Schema::dropIfExists('addon_service');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('service_faqs');
        Schema::dropIfExists('service_exclusions');
        Schema::dropIfExists('service_inclusions');
        Schema::dropIfExists('services');
    }
};
