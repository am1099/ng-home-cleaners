@php
    $settings = app(\App\Services\SiteSettingsService::class)->get();
    $logoPath = $settings->logo_path;
    $logoUrl = filled($logoPath)
        ? \App\Support\Media::url($logoPath)
        : null;
@endphp

@if ($logoUrl)
    <img
        src="{{ $logoUrl }}"
        alt="{{ $settings->business_name }}"
        class="h-9 w-auto max-w-[10rem] object-contain"
    >
@else
    <div class="flex items-center gap-2.5">
        <span class="inline-flex size-8 items-center justify-center rounded-full bg-primary-600 text-xs font-bold text-white">
            NG
        </span>
        <span class="text-sm font-semibold tracking-tight text-gray-950 dark:text-white">
            Home Cleaners
        </span>
    </div>
@endif
