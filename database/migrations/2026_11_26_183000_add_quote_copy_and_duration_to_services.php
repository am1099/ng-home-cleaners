<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->text('equipment_note')->nullable()->after('estimate_description');
            $table->decimal('guide_duration_base_hours', 4, 2)->nullable()->after('equipment_note');
            $table->decimal('guide_duration_extra_bedroom_hours', 4, 2)->nullable()->after('guide_duration_base_hours');
            $table->decimal('guide_duration_extra_bathroom_hours', 4, 2)->nullable()->after('guide_duration_extra_bedroom_hours');
        });

        DB::table('services')->where('slug', 'regular-clean')->update([
            'equipment_note' => 'We bring all cleaning products — you provide a vacuum and mop',
            'guide_duration_base_hours' => 2,
            'guide_duration_extra_bedroom_hours' => 0.5,
            'guide_duration_extra_bathroom_hours' => 0.75,
        ]);

        DB::table('services')->whereIn('slug', ['deep-clean', 'end-of-tenancy'])->update([
            'equipment_note' => 'We bring all products and equipment, including vacuum and mop',
        ]);
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'equipment_note',
                'guide_duration_base_hours',
                'guide_duration_extra_bedroom_hours',
                'guide_duration_extra_bathroom_hours',
            ]);
        });
    }
};
