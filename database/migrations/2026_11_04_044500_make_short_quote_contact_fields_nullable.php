<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('address_line1')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->date('preferred_date')->nullable()->change();
            $table->string('arrival_window')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('address_line1')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->date('preferred_date')->nullable(false)->change();
            $table->string('arrival_window')->nullable(false)->change();
        });
    }
};
