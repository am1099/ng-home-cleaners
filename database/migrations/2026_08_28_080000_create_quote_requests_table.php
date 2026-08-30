<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_normalized')->index();
            $table->string('phone_display');
            $table->string('email')->unique();
            $table->string('postcode')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();
        });

        Schema::create('quote_reference_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('last_number')->default(1000);
            $table->timestamps();
        });

        DB::table('quote_reference_counters')->insert([
            'last_number' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('source');
            $table->string('status')->default('new');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('email');
            $table->string('postcode');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->text('parking_notes')->nullable();
            $table->text('access_notes')->nullable();
            $table->date('preferred_date');
            $table->string('arrival_window');
            $table->string('frequency')->nullable();
            $table->string('property_type');
            $table->unsignedTinyInteger('bedrooms');
            $table->boolean('split_level_flat')->default(false);
            $table->unsignedTinyInteger('floors');
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedTinyInteger('wcs')->nullable();
            $table->unsignedTinyInteger('kitchens')->nullable();
            $table->unsignedTinyInteger('reception_rooms')->nullable();
            $table->json('extra_rooms')->nullable();
            $table->string('property_status')->nullable();
            $table->json('condition_flags')->nullable();
            $table->text('condition_notes')->nullable();
            $table->json('addon_ids')->nullable();
            $table->json('selections_snapshot');
            $table->json('pricing_snapshot');
            $table->string('guide_estimate_headline');
            $table->text('guide_estimate_detail')->nullable();
            $table->unsignedInteger('guide_estimate_min_pence')->nullable();
            $table->unsignedInteger('guide_estimate_max_pence')->nullable();
            $table->unsignedInteger('guide_single_price_pence')->nullable();
            $table->boolean('is_numeric_estimate')->default(true);
            $table->timestamp('whatsapp_initiated_at')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->index(['status', 'submitted_at']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
        Schema::dropIfExists('quote_reference_counters');
        Schema::dropIfExists('customers');
    }
};
