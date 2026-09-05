<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\RecentWork;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\Testimonial;
use App\Services\SeoService;
use App\Services\SiteSettingsService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(SiteSettingsService $settings, SeoService $seo): View
    {
        $site = $settings->get();
        $pageSeo = $seo->forHome();
        $faqs = Faq::query()->published()->get();

        return view('home', [
            'settings' => $site,
            'seo' => $pageSeo,
            'jsonLd' => array_values(array_filter([
                $seo->organizationJsonLd(),
                $seo->websiteJsonLd(),
                $seo->breadcrumbJsonLd($pageSeo->breadcrumbs),
                $seo->faqPageJsonLd($faqs),
            ])),
            'services' => Service::query()->active()->orderBy('sort_order')->get(),
            'recentWorks' => RecentWork::query()->published()->orderBy('sort_order')->limit(6)->get(),
            'galleryItems' => GalleryItem::query()->published()->orderBy('sort_order')->limit(12)->get(),
            'areas' => ServiceArea::query()->active()->orderBy('sort_order')->get(),
            'testimonials' => $this->publishedTestimonials(),
            'faqs' => $faqs,
        ]);
    }

    /**
     * @return Collection<int, Testimonial>
     */
    private function publishedTestimonials()
    {
        $query = Testimonial::query()
            ->when(
                app()->isProduction(),
                fn ($builder) => $builder->publishedForProduction(),
                fn ($builder) => $builder->published(),
            )
            ->with(['service:id,name,slug,is_active'])
            ->orderBy('sort_order');

        return $query->limit(3)->get();
    }
}
