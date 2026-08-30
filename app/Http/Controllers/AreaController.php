<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceArea;
use App\Services\SeoService;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(SeoService $seo): View
    {
        $pageSeo = $seo->forAreasIndex();

        return view('pages.areas.index', [
            'seo' => $pageSeo,
            'jsonLd' => array_values(array_filter([
                $seo->organizationJsonLd(),
                $seo->breadcrumbJsonLd($pageSeo->breadcrumbs),
            ])),
            'areas' => ServiceArea::query()->active()->orderBy('sort_order')->get(),
        ]);
    }

    public function show(ServiceArea $area, SeoService $seo): View
    {
        $area->load([
            'faqs',
            'services' => fn ($query) => $query->active()->orderBy('sort_order'),
        ]);

        $relatedAreas = ServiceArea::query()
            ->active()
            ->whereKeyNot($area->id)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $pageSeo = $seo->forArea($area);

        return view('pages.areas.show', [
            'area' => $area,
            'relatedAreas' => $relatedAreas,
            'allServices' => Service::query()->active()->orderBy('sort_order')->get(),
            'seo' => $pageSeo,
            'jsonLd' => array_values(array_filter([
                $seo->organizationJsonLd(),
                $seo->breadcrumbJsonLd($pageSeo->breadcrumbs),
            ])),
        ]);
    }
}
