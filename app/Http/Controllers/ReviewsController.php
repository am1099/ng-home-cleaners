<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Services\SeoService;
use Illuminate\View\View;

class ReviewsController extends Controller
{
    public function __invoke(SeoService $seo): View
    {
        $pageSeo = $seo->forReviews();

        $testimonials = Testimonial::query()
            ->publishedForProduction()
            ->with(['service:id,name,slug,is_active'])
            ->orderBy('sort_order')
            ->get();

        return view('pages.reviews', [
            'seo' => $pageSeo,
            'jsonLd' => array_values(array_filter([
                $seo->organizationJsonLd(),
                $seo->breadcrumbJsonLd($pageSeo->breadcrumbs),
            ])),
            'testimonials' => $testimonials,
        ]);
    }
}
