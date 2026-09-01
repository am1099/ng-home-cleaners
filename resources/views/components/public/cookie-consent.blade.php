@props([
    'offsetClass' => 'bottom-0',
])

@php
    $policyUrl = route('legal.cookies');
@endphp

<div
    x-data="ngCookieConsent()"
    x-cloak
    x-show="visible"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    {{ $attributes->class(['fixed inset-x-0 z-[70] px-4 pb-[max(1rem,env(safe-area-inset-bottom))] sm:px-6', $offsetClass]) }}
    role="dialog"
    aria-labelledby="cookie-consent-title"
    aria-describedby="cookie-consent-copy"
>
    <div class="mx-auto max-w-3xl rounded-[var(--radius-card)] border border-border bg-surface-page p-4 shadow-[var(--shadow-elevated)] sm:p-5">
        <h2 id="cookie-consent-title" class="text-sm font-semibold text-ink">Cookies and analytics</h2>
        <p id="cookie-consent-copy" class="mt-2 text-sm leading-relaxed text-ink-muted">
            We use optional analytics (Plausible) to understand which pages help people book. Nothing is loaded until you accept.
            See the <a href="{{ $policyUrl }}" class="ng-link">cookie policy</a> for details.
        </p>
        <div class="mt-4 flex flex-wrap gap-2">
            <button
                type="button"
                class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-[var(--radius-pill)] bg-brand-700 px-4 text-sm font-semibold text-white hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700"
                @click="accept()"
            >
                Accept analytics
            </button>
            <button
                type="button"
                class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-[var(--radius-pill)] border border-border px-4 text-sm font-semibold text-ink hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                @click="decline()"
            >
                Essential only
            </button>
        </div>
    </div>
</div>
