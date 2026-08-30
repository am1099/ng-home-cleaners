<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_reference_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('last_number')->default(1000);
            $table->timestamps();
        });

        DB::table('booking_reference_counters')->insert([
            'last_number' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('quote_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode');
            $table->date('booking_date');
            $table->string('arrival_window');
            $table->unsignedSmallInteger('expected_duration_minutes')->nullable();
            $table->unsignedInteger('agreed_price_pence');
            $table->string('status')->default('scheduled');
            $table->text('internal_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['booking_date', 'status']);
            $table->index('status');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->integer('amount_pence');
            $table->string('type');
            $table->string('method');
            $table->date('paid_date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'paid_date']);
            $table->index('paid_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('booking_reference_counters');
    }
};
