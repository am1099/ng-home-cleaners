<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')->where('slug', 'regular-clean')->update([
            'estimate_description' => 'Keep on top of the home',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('services')->where('slug', 'regular-clean')->update([
            'estimate_description' => 'Weekly, fortnightly or one-off',
            'updated_at' => now(),
        ]);
    }
};
