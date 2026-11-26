<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_events', function (Blueprint $table) {
            $table->id();
            $table->timestamp('occurred_at')->index();
            $table->string('event', 40);
            $table->string('path', 300);
            $table->string('source', 80)->nullable();
            $table->string('medium', 80)->nullable();
            $table->string('campaign', 120)->nullable();
            $table->string('device', 16)->nullable();
            $table->uuid('visitor_key')->nullable();
            $table->boolean('from_ads')->default(false);
            $table->index(['event', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_events');
    }
};
