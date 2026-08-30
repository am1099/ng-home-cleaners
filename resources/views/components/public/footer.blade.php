@php
    $navLinks = [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Services', 'href' => route('services')],
        ['label' => 'Areas', 'href' => route('areas')],
        ['label' => 'About', 'href' => route('about')],
        ['label' => 'Contact', 'href' => route('contact')],
    ];

    if (! empty($showGalleryNav)) {
        array_splice($navLinks, 3, 0, [[
            'label' => 'Gallery',
            'href' => route('home').'#gallery',
        ]]);
    }
@endphp

<footer @class([
    'bg-surface-ink text-ink-inverse',
    'border-t border-border' => ! request()->routeIs('home'),
    'pb-20 lg:pb-0' => request()->routeIs('home'),
])>
    <x-public.container>
        <div class="grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8 lg:py-16">
            <div class="sm:col-span-2 lg:col-span-1">
                <x-public.logo variant="inverse" />
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-ink-inverse-muted">
                    {{ $settings->service_area_summary }}
                </p>
            </div>

            <div>
                <h2 class="text-sm font-semibold uppercase tracking-[var(--tracking-eyebrow)] text-brand-100">Pages</h2>
                <ul class="mt-4 space-y-2">
                    @foreach ($navLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}" class="text-sm text-ink-inverse-muted transition-colors hover:text-ink-inverse focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-100">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('quote') }}" class="text-sm text-ink-inverse-muted transition-colors hover:text-ink-inverse focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-100">
                            Get a quote
                        </a>
                    </li>
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-semibold uppercase tracking-[var(--tracking-eyebrow)] text-brand-100">Contact</h2>
                <ul class="mt-4 space-y-2 text-sm text-ink-inverse-muted">
                    <li>
                        <a href="{{ $settings->phoneTel() }}" class="transition-colors hover:text-ink-inverse focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-100" onclick="window.ngTrack && window.ngTrack('phone_clicked', { location: 'footer' })">
                            {{ $settings->phoneDisplay() }}
                        </a>
                    </li>
                    <li>
                        <a href="mailto:{{ $settings->email }}" class="transition-colors hover:text-ink-inverse focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-100">
                            {{ $settings->email }}
                        </a>
                    </li>
                    @if ($settings->whatsappLink())
                        <li>
                            <a href="{{ $settings->whatsappLink() }}" target="_blank" rel="noopener noreferrer" class="transition-colors hover:text-ink-inverse focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-100" onclick="window.ngTrack && window.ngTrack('whatsapp_clicked', { location: 'footer' })">
                                WhatsApp
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-semibold uppercase tracking-[var(--tracking-eyebrow)] text-brand-100">Hours</h2>
                <p class="mt-4 text-sm leading-relaxed text-ink-inverse-muted">
                    {{ $settings->hoursSummary() }}
                </p>
                <ul class="mt-6 space-y-2 text-sm">
                    <li><a href="{{ route('legal.privacy') }}" class="text-ink-inverse-muted hover:text-ink-inverse">Privacy policy</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="text-ink-inverse-muted hover:text-ink-inverse">Terms of service</a></li>
                    <li><a href="{{ route('legal.cookies') }}" class="text-ink-inverse-muted hover:text-ink-inverse">Cookie policy</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-brand-800/50 py-6 text-center text-sm text-ink-inverse-muted">
            <p>&copy; {{ now()->year }} {{ $settings->business_name }}. All rights reserved.</p>
        </div>
    </x-public.container>
</footer>
