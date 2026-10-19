<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_confirmations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->text('address');
            $table->text('access_instructions');
            $table->string('parking');
            $table->string('equipment_preference');
            $table->text('notes')->nullable();
            $table->timestamp('valuables_confirmed_at');
            $table->timestamp('payment_confirmed_at');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('new');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_confirmations');
    }
};
