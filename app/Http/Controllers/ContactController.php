<?php

namespace App\Http\Controllers;

use App\Services\SeoService;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __invoke(SeoService $seo): View
    {
        $pageSeo = $seo->forContact();

        return view('pages.contact', [
            'seo' => $pageSeo,
            'jsonLd' => array_values(array_filter([
                $seo->organizationJsonLd(),
                $seo->breadcrumbJsonLd($pageSeo->breadcrumbs),
            ])),
        ]);
    }
}
