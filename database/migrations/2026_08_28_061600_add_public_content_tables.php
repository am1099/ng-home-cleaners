<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content');
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('home_hero_title')->nullable()->after('business_name');
            $table->text('home_hero_subtitle')->nullable()->after('home_hero_title');
            $table->string('about_hero_title')->nullable()->after('home_hero_subtitle');
            $table->text('about_hero_subtitle')->nullable()->after('about_hero_title');
            $table->longText('about_story')->nullable()->after('about_hero_subtitle');
            $table->json('about_promises')->nullable()->after('about_story');
            $table->json('how_it_works_steps')->nullable()->after('about_promises');
            $table->json('why_choose_items')->nullable()->after('how_it_works_steps');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'home_hero_title',
                'home_hero_subtitle',
                'about_hero_title',
                'about_hero_subtitle',
                'about_story',
                'about_promises',
                'how_it_works_steps',
                'why_choose_items',
            ]);
        });

        Schema::dropIfExists('faqs');
        Schema::dropIfExists('legal_pages');
    }
};
