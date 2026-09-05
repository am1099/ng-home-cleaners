<?php

namespace App\Services;

use App\Models\Faq;
use App\Models\LegalPage;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\SiteSetting;
use App\Support\Media;
use App\Support\Seo\SeoPage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class SeoService
{
    public function __construct(
        private readonly SiteSettingsService $settings,
    ) {}

    public function forHome(): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $this->brandedTitle(
                $site->default_seo_title ?: 'Professional Home Cleaners Nottingham',
                $site,
            ),
            description: $this->description(
                $site->default_seo_description,
                'Vetted cleaners across Nottingham and surrounding areas. Fixed prices agreed in writing before we start.',
            ),
            canonical: $this->absoluteRoute('home'),
            ogImage: $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
            ],
        );
    }

    public function forServicesIndex(): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $this->brandedTitle('Cleaning Services in Nottingham', $site),
            description: $this->description(
                null,
                'Regular, deep, end of tenancy and commercial cleaning across Nottingham NG1 to NG16.',
            ),
            canonical: $this->absoluteRoute('services'),
            ogImage: $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
                ['name' => 'Services', 'url' => $this->absoluteRoute('services')],
            ],
        );
    }

    public function forService(Service $service): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $service->seo_title ?: $this->brandedTitle($service->name, $site),
            description: $this->description(
                $service->seo_description,
                $service->short_description ?: ($site->default_seo_description),
            ),
            canonical: $this->absoluteRoute('services.show', $service),
            ogImage: $this->absoluteStorageUrl($service->og_image)
                ?? $this->absoluteStorageUrl($service->hero_image)
                ?? $this->absoluteStorageUrl($service->card_image)
                ?? $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
                ['name' => 'Services', 'url' => $this->absoluteRoute('services')],
                ['name' => $service->name, 'url' => $this->absoluteRoute('services.show', $service)],
            ],
        );
    }

    public function forAreasIndex(): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $this->brandedTitle('Home Cleaning Areas in Nottingham', $site),
            description: $this->description(
                null,
                'House cleaning across Nottingham NG1 to NG16 and surrounding districts.',
            ),
            canonical: $this->absoluteRoute('areas'),
            ogImage: $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
                ['name' => 'Areas', 'url' => $this->absoluteRoute('areas')],
            ],
        );
    }

    public function forArea(ServiceArea $area): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $area->seo_title ?: $this->brandedTitle($area->name.' cleaners', $site),
            description: $this->description(
                $area->seo_description,
                $area->short_intro ?: ($site->default_seo_description),
            ),
            canonical: $this->absoluteRoute('areas.show', $area),
            ogImage: $this->absoluteStorageUrl($area->hero_image) ?? $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
                ['name' => 'Areas', 'url' => $this->absoluteRoute('areas')],
                ['name' => $area->name, 'url' => $this->absoluteRoute('areas.show', $area)],
            ],
        );
    }

    public function forAbout(): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $this->brandedTitle('About us', $site),
            description: $this->description(
                null,
                'A small Nottingham cleaning team. Vetted, DBS-checked cleaners across NG1 to NG16.',
            ),
            canonical: $this->absoluteRoute('about'),
            ogImage: $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
                ['name' => 'About', 'url' => $this->absoluteRoute('about')],
            ],
        );
    }

    public function forContact(): SeoPage
    {
        $site = $this->site();
        $hours = trim($site->hoursSummary());

        return new SeoPage(
            title: $this->brandedTitle('Contact', $site),
            description: $this->description(
                null,
                'Call, email or message '.$site->business_name.($hours !== '' ? '. '.$hours : '.'),
            ),
            canonical: $this->absoluteRoute('contact'),
            ogImage: $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
                ['name' => 'Contact', 'url' => $this->absoluteRoute('contact')],
            ],
        );
    }

    public function forQuote(): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $this->brandedTitle('Get a free estimate', $site),
            description: $this->description(
                null,
                'Get a guide price for cleaning in Nottingham. Your fixed price follows in writing within one working day.',
            ),
            canonical: $this->absoluteRoute('quote'),
            ogImage: $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
                ['name' => 'Get a free estimate', 'url' => $this->absoluteRoute('quote')],
            ],
        );
    }

    public function forQuoteConfirmation(): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $this->brandedTitle('Estimate request received', $site),
            description: $this->description(
                null,
                'Your cleaning estimate request has been received. We will confirm availability and send your fixed price in writing within one working day.',
            ),
            canonical: url('/get-a-quote/confirmation'),
            ogImage: $this->defaultOgImage($site),
            robots: 'noindex,nofollow',
        );
    }

    public function forReviews(): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $this->brandedTitle('Customer reviews', $site),
            description: $this->description(
                null,
                'Read published customer reviews of '.$site->business_name.' across Nottingham NG1 to NG16.',
            ),
            canonical: $this->absoluteRoute('reviews'),
            ogImage: $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
                ['name' => 'Reviews', 'url' => $this->absoluteRoute('reviews')],
            ],
        );
    }

    public function forMoveInOut(): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $this->brandedTitle('Move-in and move-out cleaning', $site),
            description: $this->description(
                null,
                'End of tenancy and move-in cleaning across Nottingham. Fixed prices agreed in writing before we start.',
            ),
            canonical: $this->absoluteRoute('move-in-out'),
            ogImage: $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
                ['name' => 'Move-in and move-out', 'url' => $this->absoluteRoute('move-in-out')],
            ],
        );
    }

    public function forServiceInArea(Service $service, ServiceArea $area): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $this->brandedTitle($service->name.' in '.$area->name, $site),
            description: $this->description(
                null,
                $service->name.' across '.$area->name.' ('.$area->postcode_label.'). Fixed prices agreed in writing before we start.',
            ),
            canonical: $this->absoluteRoute('areas.service', [$area, $service]),
            ogImage: $this->absoluteStorageUrl($service->og_image)
                ?? $this->absoluteStorageUrl($area->hero_image)
                ?? $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
                ['name' => 'Areas', 'url' => $this->absoluteRoute('areas')],
                ['name' => $area->name, 'url' => $this->absoluteRoute('areas.show', $area)],
                ['name' => $service->name, 'url' => $this->absoluteRoute('areas.service', [$area, $service])],
            ],
        );
    }

    public function forLegal(LegalPage $page): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $this->brandedTitle($page->seo_title ?: $page->title, $site),
            description: $this->description(
                $page->seo_description,
                Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($page->content)) ?? ''), 155),
            ),
            canonical: $this->absoluteRoute('legal.'.$page->slug),
            ogImage: $this->defaultOgImage($site),
            breadcrumbs: [
                ['name' => 'Home', 'url' => $this->absoluteRoute('home')],
                ['name' => $page->title, 'url' => $this->absoluteRoute('legal.'.$page->slug)],
            ],
        );
    }

    public function forNotFound(): SeoPage
    {
        $site = $this->site();

        return new SeoPage(
            title: $this->brandedTitle('Page not found', $site),
            description: 'That page could not be found. Browse our cleaning services and areas across Nottingham, or get a free estimate.',
            canonical: url('/404'),
            ogImage: $this->defaultOgImage($site),
            robots: 'noindex,follow',
        );
    }

    /**
     * JSON-LD for Organization / LocalBusiness using only verified settings fields.
     *
     * @return array<string, mixed>
     */
    public function organizationJsonLd(): array
    {
        $site = $this->site();

        $data = [
            '@context' => 'https://schema.org',
            '@type' => ['LocalBusiness', 'HomeAndConstructionBusiness'],
            '@id' => $this->absoluteRoute('home').'#organization',
            'name' => $site->business_name,
            'url' => $this->absoluteRoute('home'),
            'telephone' => $site->phone,
            'email' => $site->email,
        ];

        if (filled($site->service_area_summary)) {
            $data['areaServed'] = $site->service_area_summary;
        }

        if (filled($site->business_address)) {
            $data['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $site->business_address,
                'addressCountry' => 'GB',
            ];
        }

        $logo = $this->absoluteStorageUrl($site->logo_path);
        if ($logo) {
            $data['logo'] = $logo;
            $data['image'] = $logo;
        }

        $sameAs = collect($site->social_links ?? [])
            ->filter(fn ($url) => filled($url) && is_string($url))
            ->values()
            ->all();

        if (filled($site->google_business_url)) {
            $sameAs[] = $site->google_business_url;
        }

        if ($sameAs !== []) {
            $data['sameAs'] = array_values(array_unique($sameAs));
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function websiteJsonLd(): array
    {
        $site = $this->site();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => $this->absoluteRoute('home').'#website',
            'url' => $this->absoluteRoute('home'),
            'name' => $site->business_name,
            'publisher' => ['@id' => $this->absoluteRoute('home').'#organization'],
        ];
    }

    /**
     * @param  Collection<int, Faq>|iterable<int, Faq>  $faqs
     * @return array<string, mixed>|null
     */
    public function faqPageJsonLd(iterable $faqs): ?array
    {
        $entities = collect($faqs)
            ->filter(fn ($faq) => filled($faq->question) && filled($faq->answer))
            ->map(fn ($faq): array => [
                '@type' => 'Question',
                'name' => $faq->question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => trim(preg_replace('/\s+/', ' ', strip_tags($faq->answer)) ?? ''),
                ],
            ])
            ->values()
            ->all();

        if ($entities === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }

    /**
     * @param  list<array{name: string, url: string}>  $crumbs
     * @return array<string, mixed>|null
     */
    public function breadcrumbJsonLd(array $crumbs): ?array
    {
        if (count($crumbs) < 2) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn (array $crumb, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ])->all(),
        ];
    }

    /**
     * @return list<array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    public function sitemapEntries(): array
    {
        return Cache::remember('seo.sitemap', now()->addHour(), fn (): array => $this->buildSitemapEntries());
    }

    public static function forgetSitemap(): void
    {
        Cache::forget('seo.sitemap');
    }

    /**
     * @return list<array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    private function buildSitemapEntries(): array
    {
        $entries = [
            $this->sitemapEntry($this->absoluteRoute('home'), 'weekly', '1.0'),
            $this->sitemapEntry($this->absoluteRoute('quote'), 'weekly', '0.9'),
            $this->sitemapEntry($this->absoluteRoute('services'), 'weekly', '0.9'),
            $this->sitemapEntry($this->absoluteRoute('areas'), 'weekly', '0.9'),
            $this->sitemapEntry($this->absoluteRoute('reviews'), 'weekly', '0.8'),
            $this->sitemapEntry($this->absoluteRoute('move-in-out'), 'monthly', '0.8'),
            $this->sitemapEntry($this->absoluteRoute('about'), 'monthly', '0.7'),
            $this->sitemapEntry($this->absoluteRoute('contact'), 'monthly', '0.7'),
        ];

        $services = Service::query()->active()->orderBy('sort_order')->get();
        $areas = ServiceArea::query()->active()->orderBy('sort_order')->get();

        foreach ($services as $service) {
            $entries[] = $this->sitemapEntry(
                $this->absoluteRoute('services.show', $service),
                'weekly',
                '0.8',
                $service->updated_at?->toAtomString(),
            );
        }

        foreach ($areas as $area) {
            $entries[] = $this->sitemapEntry(
                $this->absoluteRoute('areas.show', $area),
                'weekly',
                '0.8',
                $area->updated_at?->toAtomString(),
            );

            foreach ($services as $service) {
                $entries[] = $this->sitemapEntry(
                    $this->absoluteRoute('areas.service', [$area, $service]),
                    'weekly',
                    '0.7',
                    max($area->updated_at?->timestamp ?? 0, $service->updated_at?->timestamp ?? 0)
                        ? ($area->updated_at?->greaterThan($service->updated_at ?? $area->updated_at)
                            ? $area->updated_at?->toAtomString()
                            : $service->updated_at?->toAtomString())
                        : null,
                );
            }
        }

        foreach (LegalPage::query()->published()->orderBy('slug')->get() as $page) {
            if (! in_array($page->slug, ['privacy', 'terms', 'cookies'], true)) {
                continue;
            }

            $entries[] = $this->sitemapEntry(
                $this->absoluteRoute('legal.'.$page->slug),
                'yearly',
                '0.3',
                $page->updated_at?->toAtomString(),
            );
        }

        return $entries;
    }

    public function absoluteRoute(string $name, mixed $parameters = []): string
    {
        return route($name, $parameters, absolute: true);
    }

    public function absoluteStorageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $url = Media::url($path);

        if (! filled($url)) {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url($url);
    }

    private function brandedTitle(string $pageTitle, SiteSetting $site): string
    {
        $brand = trim((string) $site->business_name);
        $pageTitle = trim($pageTitle);

        if ($brand === '' || str_contains($pageTitle, $brand)) {
            return $pageTitle;
        }

        return $pageTitle.' | '.$brand;
    }

    private function site(): SiteSetting
    {
        return $this->settings->get();
    }

    private function defaultOgImage(SiteSetting $site): ?string
    {
        return $this->absoluteStorageUrl($site->default_og_image)
            ?? $this->absoluteStorageUrl($site->logo_path);
    }

    private function description(?string $preferred, ?string $fallback): string
    {
        $text = trim((string) ($preferred ?: $fallback ?: ''));
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return Str::limit($text, 160, '');
    }

    /**
     * @return array{loc: string, lastmod: ?string, changefreq: string, priority: string}
     */
    private function sitemapEntry(string $loc, string $changefreq, string $priority, ?string $lastmod = null): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }
}
