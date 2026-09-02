@props([
    'showEstimate' => true,
])

@php
    $settings = $settings ?? app(\App\Services\SiteSettingsService::class)->get();
@endphp

<div class="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-surface-page/95 p-3 backdrop-blur-sm lg:hidden" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
    <div class="mx-auto grid max-w-lg grid-cols-3 gap-2">
        <x-public.button
            href="{{ $settings->phoneTel() }}"
            variant="phone"
            size="xs"
            class="min-w-0 justify-center whitespace-nowrap"
            onclick="window.ngTrack && window.ngTrack('phone_clicked', { location: 'mobile_sticky' })"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 15.352V16.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd"/></svg>
            Call
        </x-public.button>
        @if ($showEstimate)
            <x-public.estimate-cta
                label="Quote"
                class="min-w-0 justify-center whitespace-nowrap"
                size="xs"
                location="mobile_sticky"
            />
        @endif
        @if ($settings->whatsappLink())
            <x-public.whatsapp-cta
                label="Chat"
                class="min-w-0 justify-center whitespace-nowrap"
                size="xs"
                aria-label="WhatsApp us"
            />
        @endif
    </div>
</div>
