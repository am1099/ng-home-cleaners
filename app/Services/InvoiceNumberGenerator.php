<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class InvoiceNumberGenerator
{
    public function next(?int $year = null): string
    {
        $year ??= (int) now()->year;

        return DB::transaction(function () use ($year): string {
            $counter = DB::table('invoice_number_counters')
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                DB::table('invoice_number_counters')->insert([
                    'year' => $year,
                    'last_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $counter = DB::table('invoice_number_counters')
                    ->where('year', $year)
                    ->lockForUpdate()
                    ->first();
            }

            $next = ((int) $counter->last_number) + 1;

            DB::table('invoice_number_counters')
                ->where('year', $year)
                ->update([
                    'last_number' => $next,
                    'updated_at' => now(),
                ]);

            return sprintf('NG-%d-%04d', $year, $next);
        });
    }
}
