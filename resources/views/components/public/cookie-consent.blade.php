@props([
    'offsetClass' => 'bottom-0',
])

@php
    $policyUrl = route('legal.cookies');
@endphp

{{-- Mobbin-informed compact bottom bar: short copy, equal Accept/Reject targets, policy link --}}
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
    {{ $attributes->class(['fixed inset-x-0 z-[70] px-3 pb-[max(0.5rem,env(safe-area-inset-bottom))] sm:px-4', $offsetClass]) }}
    role="dialog"
    aria-labelledby="cookie-consent-title"
    aria-describedby="cookie-consent-copy"
>
    <div class="mx-auto flex max-w-5xl flex-col gap-2.5 rounded-[var(--radius-md)] border border-border bg-white/95 px-3 py-2.5 shadow-[var(--shadow-elevated)] backdrop-blur-md sm:flex-row sm:items-center sm:gap-4 sm:px-4 sm:py-2.5">
        <div class="min-w-0 flex-1">
            <p id="cookie-consent-title" class="sr-only">Cookies and analytics</p>
            <p id="cookie-consent-copy" class="text-xs leading-snug text-ink-muted sm:text-sm">
                @if (filled(config('analytics.google_ads_id')))
                    Optional analytics and Google Ads cookies help us measure enquiries. They stay off until you accept.
                @else
                    Optional analytics help us see which pages help people book. Nothing loads until you accept.
                @endif
                <a href="{{ $policyUrl }}" class="font-semibold text-brand-800 underline-offset-2 hover:underline">Cookie policy</a>
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-2 sm:w-auto sm:min-w-[14rem]">
            <button
                type="button"
                class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-[var(--radius-pill)] border border-border bg-white px-3 text-sm font-semibold text-ink hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                @click="decline()"
            >
                Reject
            </button>
            <button
                type="button"
                class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-[var(--radius-pill)] bg-brand-700 px-3 text-sm font-semibold text-white hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700"
                @click="accept()"
            >
                Accept
            </button>
        </div>
    </div>
</div>
