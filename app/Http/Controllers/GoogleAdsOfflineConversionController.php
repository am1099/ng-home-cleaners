<?php

namespace App\Http\Controllers;

use App\Services\GoogleAdsOfflineConversionExport;
use Illuminate\Http\Response;

class GoogleAdsOfflineConversionController extends Controller
{
    public function __invoke(GoogleAdsOfflineConversionExport $export): Response
    {
        $bookings = $export->pending();

        if ($bookings->isEmpty()) {
            return response(
                'No confirmed bookings are ready to export. A booking must be scheduled or completed, have an agreed price, a Google click ID saved after cookie consent, and not already have been exported.',
                200,
                ['Content-Type' => 'text/plain; charset=UTF-8'],
            );
        }

        $csv = $export->toCsv($bookings);
        $export->markExported($bookings);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="google-ads-booked-jobs.csv"',
        ]);
    }
}
