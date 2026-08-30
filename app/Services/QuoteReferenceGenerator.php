<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class QuoteReferenceGenerator
{
    public function next(): string
    {
        return DB::transaction(function (): string {
            $counter = DB::table('quote_reference_counters')->lockForUpdate()->first();

            $next = ((int) $counter->last_number) + 1;

            DB::table('quote_reference_counters')->update([
                'last_number' => $next,
                'updated_at' => now(),
            ]);

            return 'NG-'.$next;
        });
    }
}
