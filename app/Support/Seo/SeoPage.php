<?php

namespace App\Support\Seo;

final class SeoPage
{
    /**
     * @param  list<array{name: string, url: string}>  $breadcrumbs
     */
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $canonical,
        public readonly ?string $ogImage = null,
        public readonly string $robots = 'index,follow',
        public readonly string $ogType = 'website',
        public readonly array $breadcrumbs = [],
    ) {}

    public function shouldIndex(): bool
    {
        return ! str_contains($this->robots, 'noindex');
    }
}
