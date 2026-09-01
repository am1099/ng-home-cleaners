<?php

namespace App\Http\Controllers;

use App\Models\RecentWork;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\ServiceExclusion;
use App\Services\SeoService;
use App\Support\Analytics\Analytics;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(SeoService $seo): View
    {
        $pageSeo = $seo->forServicesIndex();

        $exclusions = ServiceExclusion::query()
            ->select('task', 'note', DB::raw('MIN(sort_order) as sort_order'))
            ->groupBy('task', 'note')
            ->orderBy('sort_order')
            ->get();

        return view('pages.services.index', [
            'seo' => $pageSeo,
            'jsonLd' => array_values(array_filter([
                $seo->organizationJsonLd(),
                $seo->breadcrumbJsonLd($pageSeo->breadcrumbs),
            ])),
            'services' => Service::query()->active()->orderBy('sort_order')->get(),
            'exclusions' => $exclusions,
            'areas' => ServiceArea::query()->active()->orderBy('sort_order')->limit(8)->get(),
        ]);
    }

    public function show(Service $service, SeoService $seo): View
    {
        $service->load([
            'inclusions',
            'exclusions',
            'faqs',
            'addons' => fn ($query) => $query->active()->orderBy('sort_order'),
            'serviceAreas' => fn ($query) => $query->active()->orderBy('sort_order'),
        ]);

        $pageSeo = $seo->forService($service);

        $recentWorks = RecentWork::query()
            ->published()
            ->where(function ($query) use ($service): void {
                $query->where('service_id', $service->id)->orWhereNull('service_id');
            })
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        return view('pages.services.show', [
            'service' => $service,
            'recentWorks' => $recentWorks,
            'seo' => $pageSeo,
            'jsonLd' => array_values(array_filter([
                $seo->organizationJsonLd(),
                $seo->breadcrumbJsonLd($pageSeo->breadcrumbs),
            ])),
            'analyticsEvent' => Analytics::SERVICE_VIEWED,
            'analyticsPayload' => ['slug' => $service->slug],
        ]);
    }
}
