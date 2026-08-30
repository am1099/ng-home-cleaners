<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('city');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unique('email');
        });

        Schema::table('quote_requests', function (Blueprint $table) {
            $table->unsignedInteger('final_quote_amount_pence')->nullable()->after('is_numeric_estimate');
            $table->text('internal_notes')->nullable()->after('final_quote_amount_pence');
            $table->timestamp('contacted_at')->nullable()->after('whatsapp_initiated_at');
            $table->timestamp('quote_sent_at')->nullable()->after('contacted_at');
            $table->timestamp('won_at')->nullable()->after('quote_sent_at');
            $table->timestamp('lost_at')->nullable()->after('won_at');
        });

        Schema::table('quote_requests', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->date('preferred_date')->nullable()->change();
            $table->string('arrival_window')->nullable()->change();
            $table->string('property_type')->nullable()->change();
            $table->unsignedTinyInteger('bedrooms')->nullable()->change();
            $table->unsignedTinyInteger('floors')->nullable()->change();
            $table->string('address_line1')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('postcode')->nullable()->change();
            $table->json('selections_snapshot')->nullable()->change();
            $table->json('pricing_snapshot')->nullable()->change();
            $table->string('guide_estimate_headline')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropColumn([
                'final_quote_amount_pence',
                'internal_notes',
                'contacted_at',
                'quote_sent_at',
                'won_at',
                'lost_at',
            ]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn('notes');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
        });
    }
};
