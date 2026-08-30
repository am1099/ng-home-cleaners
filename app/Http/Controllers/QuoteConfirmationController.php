<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use App\Services\QuoteRequestService;
use App\Services\SeoService;
use Illuminate\View\View;

class QuoteConfirmationController extends Controller
{
    public function __invoke(string $reference, QuoteRequestService $quoteRequestService, SeoService $seo): View
    {
        $quoteRequest = QuoteRequest::query()
            ->with('service')
            ->where('reference', $reference)
            ->firstOrFail();

        $pageSeo = $seo->forQuoteConfirmation();

        return view('quote.confirmation', [
            'quoteRequest' => $quoteRequest,
            'whatsappUrl' => $quoteRequestService->whatsappUrl($quoteRequest),
            'seo' => $pageSeo,
            'jsonLd' => [$seo->organizationJsonLd()],
        ]);
    }
}
