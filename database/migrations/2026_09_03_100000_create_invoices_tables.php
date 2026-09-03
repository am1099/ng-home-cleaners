<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_number_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('invoice_number')->nullable()->unique();
            $table->string('status')->default('draft');
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('currency', 3)->default('GBP');

            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('billing_address_line1')->nullable();
            $table->string('billing_address_line2')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_postcode')->nullable();

            $table->string('business_name');
            $table->string('business_email')->nullable();
            $table->string('business_phone')->nullable();
            $table->text('business_address')->nullable();
            $table->string('company_legal_name')->nullable();
            $table->string('company_registration_number')->nullable();

            $table->boolean('vat_registered')->default(false);
            $table->string('vat_number')->nullable();
            $table->decimal('vat_rate_percent', 5, 2)->nullable();

            $table->unsignedInteger('subtotal_pence')->default(0);
            $table->unsignedInteger('discount_pence')->default(0);
            $table->unsignedInteger('vat_pence')->default(0);
            $table->unsignedInteger('total_pence')->default(0);

            $table->text('notes')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('payment_instructions')->nullable();

            $table->string('booking_reference')->nullable();
            $table->date('booking_date')->nullable();
            $table->string('service_name')->nullable();

            $table->string('pdf_disk')->nullable();
            $table->string('pdf_path')->nullable();

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('first_sent_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('status');
            $table->index('due_date');
            $table->index('issue_date');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 8, 2)->default(1);
            $table->integer('unit_price_pence');
            $table->integer('line_total_pence');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invoice_id', 'sort_order']);
        });

        Schema::create('invoice_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_email');
            $table->string('status')->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('error_summary')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['invoice_id', 'status']);
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('vat_registered')->default(false)->after('default_og_image');
            $table->string('vat_number')->nullable()->after('vat_registered');
            $table->decimal('default_vat_rate_percent', 5, 2)->nullable()->default(20.00)->after('vat_number');
            $table->unsignedSmallInteger('invoice_due_days')->default(7)->after('default_vat_rate_percent');
            $table->text('invoice_payment_terms')->nullable()->after('invoice_due_days');
            $table->text('invoice_payment_instructions')->nullable()->after('invoice_payment_terms');
            $table->text('invoice_default_notes')->nullable()->after('invoice_payment_instructions');
            $table->string('company_legal_name')->nullable()->after('invoice_default_notes');
            $table->string('company_registration_number')->nullable()->after('company_legal_name');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'vat_registered',
                'vat_number',
                'default_vat_rate_percent',
                'invoice_due_days',
                'invoice_payment_terms',
                'invoice_payment_instructions',
                'invoice_default_notes',
                'company_legal_name',
                'company_registration_number',
            ]);
        });

        Schema::dropIfExists('invoice_deliveries');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_number_counters');
    }
};
