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

{{-- backdrop-filter must not wrap the fixed panel: it creates a containing block and traps inset-0 to header height --}}
<header class="sticky top-0 z-50" data-mobile-nav>
    <a href="#main-content" class="ng-skip-link">Skip to main content</a>

    <div class="border-b border-border/80 bg-surface-page/95 backdrop-blur-sm">
        <x-public.container tag="div" class="flex h-[var(--height-header)] items-center justify-between gap-3 sm:gap-4">
            <a href="{{ route('home') }}" class="min-w-0 shrink rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600" aria-label="{{ $settings->business_name }} home">
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
                class="inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-[var(--radius-md)] border border-border bg-surface-card text-ink lg:hidden"
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
    </div>

    <div
        id="mobile-navigation"
        class="ng-mobile-nav fixed inset-0 z-[60] hidden lg:hidden"
        data-mobile-nav-panel
        data-open="false"
        aria-hidden="true"
        hidden
    >
        <button
            type="button"
            class="ng-mobile-nav__backdrop absolute inset-0 bg-brand-950/45"
            data-mobile-nav-backdrop
            aria-label="Close menu"
            tabindex="-1"
        ></button>

        <div
            class="ng-mobile-nav__drawer absolute inset-0 flex w-full flex-col bg-surface-page outline-none"
            role="dialog"
            aria-modal="true"
            aria-label="Site navigation"
            data-mobile-nav-dialog
        >
            <div class="flex h-[var(--height-header)] shrink-0 items-center justify-between gap-3 border-b border-border/80 px-5 sm:px-6">
                <a href="{{ route('home') }}" class="min-w-0 rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600" aria-label="{{ $settings->business_name }} home" data-mobile-nav-link>
                    <x-public.logo />
                </a>
                <button
                    type="button"
                    class="inline-flex size-11 shrink-0 items-center justify-center rounded-[var(--radius-md)] border border-border text-ink-muted transition-colors hover:bg-brand-50 hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                    data-mobile-nav-close
                    aria-label="Close menu"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5" aria-hidden="true">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>

            <nav class="flex flex-1 flex-col gap-1 overflow-y-auto px-4 py-6 sm:px-6" aria-label="Primary mobile">
                @foreach ($navLinks as $link)
                    <a
                        href="{{ $link['href'] }}"
                        @class([
                            'flex min-h-14 items-center rounded-[var(--radius-md)] px-4 text-xl font-semibold tracking-tight transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600',
                            'bg-brand-50 text-brand-950' => $link['active'],
                            'text-ink hover:bg-brand-50/70' => ! $link['active'],
                        ])
                        @if ($link['active']) aria-current="page" @endif
                        data-mobile-nav-link
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div
                class="ng-mobile-nav__cta shrink-0 space-y-3 border-t border-border bg-surface-sunken px-5 py-5 sm:px-6"
                style="padding-bottom: max(1.25rem, env(safe-area-inset-bottom));"
                data-mobile-nav-cta
            >
                <x-public.estimate-cta label="Get a free estimate" class="w-full justify-center" size="lg" data-mobile-nav-link />

                <div class="grid grid-cols-2 gap-3">
                    <a
                        href="{{ $settings->phoneTel() }}"
                        class="inline-flex min-h-12 items-center justify-center gap-1.5 rounded-[var(--radius-pill)] border border-border bg-surface-card px-3 text-base font-semibold text-brand-900 transition-colors hover:border-brand-300 hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                        data-mobile-nav-link
                        onclick="window.ngTrack && window.ngTrack('phone_clicked', { location: 'mobile_nav' })"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 15.352V16.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd"/></svg>
                        Call
                    </a>

                    @if ($settings->whatsappLink())
                        <a
                            href="{{ $settings->whatsappLink() }}"
                            class="inline-flex min-h-12 items-center justify-center gap-1.5 rounded-[var(--radius-pill)] border border-[#25D366]/35 bg-[#25D366]/10 px-3 text-base font-semibold text-[#128C7E] transition-colors hover:bg-[#25D366]/18 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#25D366]"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-mobile-nav-link
                            onclick="window.ngTrack && window.ngTrack('whatsapp_clicked', { location: 'mobile_nav' })"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                            WhatsApp
                        </a>
                    @endif
                </div>

                <p class="pt-1 text-center text-sm leading-relaxed text-ink-muted">
                    {{ $settings->service_area_summary ?: 'Nottingham' }}
                    @if (filled($settings->hoursSummary()))
                        <span aria-hidden="true"> · </span>{{ $settings->hoursSummary() }}
                    @endif
                </p>
            </div>
        </div>
    </div>
</header>
