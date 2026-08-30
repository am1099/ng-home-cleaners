@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'image' => null,
    'imageAlt' => '',
])

@php
    $hasImage = filled($image);
@endphp

<section {{ $attributes->class(['ng-section-hero relative overflow-hidden', 'bg-gradient-to-br from-brand-50 via-surface-page to-surface-sunken' => ! $hasImage]) }}>
    @if ($hasImage)
        <div class="absolute inset-0" aria-hidden="true">
            <img src="{{ $image }}" alt="" class="size-full object-cover" width="1600" height="900">
            <div class="absolute inset-0 bg-gradient-to-r from-[#0e3b36]/92 via-[#0e3b36]/75 to-[#0e3b36]/40"></div>
        </div>
    @endif

    <x-public.container class="relative z-10">
        <div @class(['grid items-center gap-10 lg:grid-cols-2' => $hasImage])>
            <div class="{{ $hasImage ? 'text-white' : '' }}">
                @if ($eyebrow)
                    <p class="ng-eyebrow {{ $hasImage ? '!text-brand-100' : '' }}">{{ $eyebrow }}</p>
                @endif
                <h1 class="ng-display ng-display-hero mt-0 max-w-2xl text-balance {{ $hasImage ? 'text-white' : 'text-brand-950' }}">
                    {{ $title }}
                </h1>
                @if ($subtitle)
                    <p class="ng-body-lg mt-[18px] max-w-xl {{ $hasImage ? 'text-brand-50/90' : '' }}">{{ $subtitle }}</p>
                @endif
                @if ($slot->isNotEmpty())
                    <div class="mt-6">{{ $slot }}</div>
                @endif
            </div>

            @if ($hasImage)
                <div class="hidden lg:block">
                    <div class="aspect-[4/5] overflow-hidden rounded-[var(--radius-card)] border border-white/20 shadow-2xl shadow-black/25">
                        <img
                            src="{{ $image }}"
                            alt="{{ $imageAlt }}"
                            class="size-full object-cover"
                            width="800"
                            height="1000"
                        >
                    </div>
                </div>
            @endif
        </div>
    </x-public.container>
</section>
