<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->json('ad_attribution')->nullable();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('ads_offline_exported_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropColumn('ad_attribution');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('ads_offline_exported_at');
        });
    }
};
