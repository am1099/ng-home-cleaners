<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('google_review_id')->nullable()->unique();
            $table->timestamp('reviewed_at')->nullable();
        });

        Schema::create('google_business_connections', function (Blueprint $table) {
            $table->id();
            $table->text('refresh_token');
            $table->string('account_name')->nullable();
            $table->string('account_resource')->nullable();
            $table->string('location_title')->nullable();
            $table->string('location_resource')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_business_connections');

        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropUnique(['google_review_id']);
            $table->dropColumn(['google_review_id', 'reviewed_at']);
        });
    }
};
