<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class BookingReferenceGenerator
{
    public function next(): string
    {
        return DB::transaction(function (): string {
            $counter = DB::table('booking_reference_counters')->lockForUpdate()->first();

            $next = ((int) $counter->last_number) + 1;

            DB::table('booking_reference_counters')->update([
                'last_number' => $next,
                'updated_at' => now(),
            ]);

            return 'BK-'.$next;
        });
    }
}
