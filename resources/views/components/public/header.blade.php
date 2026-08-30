@php
    $navLinks = [
        ['label' => 'Home', 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Services', 'href' => route('services'), 'active' => request()->routeIs(['services', 'services.show'])],
        ['label' => 'Areas', 'href' => route('areas'), 'active' => request()->routeIs(['areas', 'areas.show'])],
        ['label' => 'About', 'href' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Contact', 'href' => route('contact'), 'active' => request()->routeIs('contact')],
    ];

    if (! empty($showGalleryNav)) {
        array_splice($navLinks, 3, 0, [[
            'label' => 'Gallery',
            'href' => route('home').'#gallery',
            'active' => false,
        ]]);
    }
@endphp

<header class="sticky top-0 z-50 border-b border-border/80 bg-surface-page/95 backdrop-blur-sm" data-mobile-nav>
    <a href="#main-content" class="ng-skip-link">Skip to main content</a>

    <x-public.container tag="div" class="flex h-[var(--height-header)] items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="shrink-0 rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600" aria-label="{{ $settings->business_name }} home">
            <x-public.logo />
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary">
            @foreach ($navLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    @class([
                        'rounded-[var(--radius-pill)] px-3.5 py-2 text-sm font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600',
                        'bg-brand-50 text-brand-950' => $link['active'],
                        'text-ink-muted hover:bg-brand-50/70 hover:text-brand-950' => ! $link['active'],
                    ])
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            <a
                href="{{ $settings->phoneTel() }}"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-800 hover:text-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                onclick="window.ngTrack && window.ngTrack('phone_clicked', { location: 'header' })"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 15.352V16.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd"/></svg>
                {{ $settings->phoneDisplay() }}
            </a>
            <x-public.estimate-cta size="sm" />
        </div>

        <button
            type="button"
            class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-[var(--radius-md)] border border-border bg-surface-card text-ink lg:hidden"
            data-mobile-nav-toggle
            aria-expanded="false"
            aria-controls="mobile-navigation"
        >
            <span class="sr-only">Open menu</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5" aria-hidden="true" data-icon-menu>
                <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hidden size-5" aria-hidden="true" data-icon-close>
                <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>
    </x-public.container>

    <div id="mobile-navigation" class="fixed inset-0 z-40 hidden lg:hidden" data-mobile-nav-panel hidden>
        <button type="button" class="absolute inset-0 bg-brand-950/40" data-mobile-nav-backdrop aria-label="Close menu" tabindex="-1"></button>

        <div class="absolute right-0 top-0 flex h-full w-full max-w-sm flex-col bg-surface-page shadow-[var(--shadow-elevated)]" role="dialog" aria-modal="true" aria-label="Site navigation" data-mobile-nav-dialog>
            <div class="flex h-[var(--height-header)] items-center justify-between border-b border-border px-[var(--spacing-section-x)]">
                <x-public.logo />
                <button type="button" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-[var(--radius-md)] border border-border bg-surface-card" data-mobile-nav-close>
                    <span class="sr-only">Close menu</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5" aria-hidden="true">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>

            <nav class="flex flex-1 flex-col gap-1 overflow-y-auto px-[var(--spacing-section-x)] py-6" aria-label="Primary mobile">
                @foreach ($navLinks as $link)
                    <a href="{{ $link['href'] }}" @class([
                        'rounded-[var(--radius-md)] px-4 py-3 text-base font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600',
                        'bg-brand-50 text-brand-950' => $link['active'],
                        'text-ink hover:bg-brand-50/70' => ! $link['active'],
                    ]) data-mobile-nav-link>
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="space-y-3 border-t border-border px-[var(--spacing-section-x)] py-6">
                <a href="{{ $settings->phoneTel() }}" class="block text-center text-sm font-semibold text-brand-800" data-mobile-nav-link onclick="window.ngTrack && window.ngTrack('phone_clicked', { location: 'mobile_nav' })">
                    Call {{ $settings->phoneDisplay() }}
                </a>
                <x-public.estimate-cta class="w-full justify-center" />
                <x-public.whatsapp-cta class="w-full justify-center" onclick="window.ngTrack && window.ngTrack('whatsapp_clicked', { location: 'mobile_nav' })" />
            </div>
        </div>
    </div>
</header>
