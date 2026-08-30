<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_starting_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('property_type');
            $table->unsignedInteger('min_pence');
            $table->unsignedInteger('max_pence');
            $table->timestamps();

            $table->unique(['service_id', 'property_type']);
        });

        Schema::create('pricing_bedroom_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('bedrooms_included')->default(1);
            $table->unsignedInteger('extra_min_pence');
            $table->unsignedInteger('extra_max_pence');
            $table->timestamps();

            $table->unique('service_id');
        });

        Schema::create('pricing_extra_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('room_type');
            $table->string('label');
            $table->unsignedInteger('min_pence');
            $table->unsignedInteger('max_pence');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['service_id', 'room_type']);
        });

        Schema::create('pricing_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('condition_flag');
            $table->unsignedInteger('min_pence');
            $table->unsignedInteger('max_pence');
            $table->timestamps();

            $table->unique(['service_id', 'condition_flag']);
        });

        Schema::dropIfExists('pricing_bands');
        Schema::dropIfExists('pricing_room_modifiers');
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_conditions');
        Schema::dropIfExists('pricing_extra_rooms');
        Schema::dropIfExists('pricing_bedroom_rules');
        Schema::dropIfExists('pricing_starting_prices');

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
    }
};
