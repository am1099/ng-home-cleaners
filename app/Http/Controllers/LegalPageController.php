<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use App\Services\SeoService;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function __invoke(string $slug): View
    {
        $seo = app(SeoService::class);

        $page = LegalPage::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $pageSeo = $seo->forLegal($page);

        return view('pages.legal.show', [
            'page' => $page,
            'seo' => $pageSeo,
            'jsonLd' => array_values(array_filter([
                $seo->organizationJsonLd(),
                $seo->breadcrumbJsonLd($pageSeo->breadcrumbs),
            ])),
        ]);
    }
}
