<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Short lines shown under each clean on the public quote form.
     *
     * @var array<string, string>
     */
    private array $descriptions = [
        'regular-clean' => 'Weekly, fortnightly or one-off',
        'deep-clean' => 'Top-to-bottom, one visit',
        'end-of-tenancy' => 'Move-out clean, oven included',
        'office-commercial' => 'Quoted on WhatsApp',
    ];

    public function up(): void
    {
        foreach ($this->descriptions as $slug => $description) {
            DB::table('services')->where('slug', $slug)->update([
                'estimate_description' => $description,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $previous = [
            'regular-clean' => 'Dusting, hoovering, mopping, bathrooms and toilets. Two-hour minimum visit.',
            'deep-clean' => 'Kitchen and hob degreased, skirting boards, inside cabinets and limescale. Oven interior and internal windows are optional add-ons.',
            'end-of-tenancy' => 'Cleaned to inventory standard so your agent has nothing to deduct for.',
            'office-commercial' => 'Commercial premises are quoted after a short walk-round, usually within a few days.',
        ];

        foreach ($previous as $slug => $description) {
            DB::table('services')->where('slug', $slug)->update([
                'estimate_description' => $description,
                'updated_at' => now(),
            ]);
        }
    }
};
