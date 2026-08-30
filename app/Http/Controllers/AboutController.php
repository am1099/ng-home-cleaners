<?php

namespace App\Http\Controllers;

use App\Models\ServiceArea;
use App\Services\SeoService;
use App\Services\SiteSettingsService;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(SiteSettingsService $settings, SeoService $seo): View
    {
        $pageSeo = $seo->forAbout();

        return view('pages.about', [
            'settings' => $settings->get(),
            'areas' => ServiceArea::query()->active()->orderBy('sort_order')->get(),
            'seo' => $pageSeo,
            'jsonLd' => array_values(array_filter([
                $seo->organizationJsonLd(),
                $seo->breadcrumbJsonLd($pageSeo->breadcrumbs),
            ])),
        ]);
    }
}
