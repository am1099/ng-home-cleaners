<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addons', function (Blueprint $table) {
            $table->unsignedInteger('price_max_pence')->nullable()->after('price_pence');
        });

        Schema::table('pricing_settings', function (Blueprint $table) {
            $table->unsignedInteger('regular_min_max_pence')->default(6500)->after('regular_min_pence');
            $table->decimal('regular_single_price_bias_percent', 5, 2)->default(60)->after('regular_min_max_pence');
            $table->unsignedInteger('rounding_step_pence')->default(500)->after('regular_single_price_bias_percent');
            $table->unsignedTinyInteger('included_floors_baseline')->default(2)->after('rounding_step_pence');
            $table->decimal('monthly_discount_percent', 5, 2)->default(0)->after('fortnightly_discount_percent');
        });
    }

    public function down(): void
    {
        Schema::table('addons', function (Blueprint $table) {
            $table->dropColumn('price_max_pence');
        });

        Schema::table('pricing_settings', function (Blueprint $table) {
            $table->dropColumn([
                'regular_min_max_pence',
                'regular_single_price_bias_percent',
                'rounding_step_pence',
                'included_floors_baseline',
                'monthly_discount_percent',
            ]);
        });
    }
};
