<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recent_works', function (Blueprint $table) {
            $table->id();
            $table->string('before_image_path');
            $table->string('after_image_path');
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('alt_text_before');
            $table->string('alt_text_after');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('show_recent_work')->default(true)->after('guarantee_statement');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('show_recent_work');
        });

        Schema::dropIfExists('recent_works');
    }
};
