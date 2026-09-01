<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->index(['is_published', 'published_at', 'sort_order'], 'gallery_items_published_sort_idx');
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->index(['is_published', 'published_at', 'sort_order'], 'testimonials_published_sort_idx');
        });

        Schema::table('recent_works', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['is_published', 'published_at', 'sort_order'], 'recent_works_published_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropIndex('gallery_items_published_sort_idx');
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropIndex('testimonials_published_sort_idx');
        });

        Schema::table('recent_works', function (Blueprint $table) {
            $table->dropIndex('recent_works_published_sort_idx');
            $table->dropConstrainedForeignId('service_id');
        });
    }
};
