<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->timestamp('follow_up_sent_at')->nullable()->after('submitted_at');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('review_request_sent_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropColumn('follow_up_sent_at');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('review_request_sent_at');
        });
    }
};
