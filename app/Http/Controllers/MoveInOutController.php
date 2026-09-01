<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\SeoService;
use Illuminate\View\View;

class MoveInOutController extends Controller
{
    public function __invoke(SeoService $seo): View
    {
        $pageSeo = $seo->forMoveInOut();
        $endOfTenancy = Service::query()->active()->where('slug', 'end-of-tenancy')->first();
        $deep = Service::query()->active()->where('slug', 'deep-clean')->first();

        return view('pages.move-in-out', [
            'seo' => $pageSeo,
            'jsonLd' => array_values(array_filter([
                $seo->organizationJsonLd(),
                $seo->breadcrumbJsonLd($pageSeo->breadcrumbs),
            ])),
            'endOfTenancy' => $endOfTenancy,
            'deep' => $deep,
        ]);
    }
}
