@props([
    'variant' => 'ink',
    'showText' => true,
])

@php
    $isInverse = $variant === 'inverse';
    $logoPath = $settings->logo_path ?? null;
    $logoUrl = filled($logoPath) ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : null;
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-3']) }}>
    @if ($logoUrl)
        <img
            src="{{ $logoUrl }}"
            alt="{{ $settings->business_name ?? config('ng.name') }}"
            width="160"
            height="40"
            class="h-10 w-auto max-w-[10rem] object-contain"
            decoding="async"
        >
    @else
        <span @class([
            'inline-flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-bold',
            'bg-brand-700 text-white' => ! $isInverse,
            'bg-ink-inverse text-brand-950' => $isInverse,
        ]) aria-hidden="true">NG</span>

        @if ($showText)
            <span @class([
                'font-semibold tracking-tight',
                'text-brand-950' => ! $isInverse,
                'text-ink-inverse' => $isInverse,
            ])>
                <span class="sr-only">{{ config('ng.name') }}</span>
                <span aria-hidden="true">Home Cleaners</span>
            </span>
        @endif
    @endif
</span>
