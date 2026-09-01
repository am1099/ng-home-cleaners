<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('is_published');
        });

        // Existing published reviews should remain visible after HasPublishedScope runs.
        DB::table('testimonials')
            ->where('is_published', true)
            ->whereNull('published_at')
            ->update(['published_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
