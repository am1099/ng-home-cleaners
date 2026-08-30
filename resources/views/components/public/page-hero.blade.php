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

@if ($hasImage)
    <section {{ $attributes->class(['relative min-h-[28rem] overflow-hidden lg:min-h-[32rem]']) }}>
        <img
            src="{{ $image }}"
            alt="{{ $imageAlt }}"
            class="absolute inset-0 size-full object-cover"
            width="1600"
            height="900"
        >
        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/65 to-black/25" aria-hidden="true"></div>

        <x-public.container class="relative z-10 flex min-h-[28rem] flex-col justify-end py-12 lg:min-h-[32rem] lg:py-16">
            @if ($eyebrow)
                <p class="ng-eyebrow !mb-3 !text-brand-100">{{ $eyebrow }}</p>
            @endif
            <h1 class="ng-display ng-display-hero mt-0 max-w-3xl text-balance text-white">
                {{ $title }}
            </h1>
            @if ($subtitle)
                <p class="ng-body-lg mt-4 max-w-2xl text-white">{{ $subtitle }}</p>
            @endif
            @if ($slot->isNotEmpty())
                <div class="mt-8">{{ $slot }}</div>
            @endif
        </x-public.container>
    </section>
@else
    <section {{ $attributes->class(['bg-gradient-to-br from-brand-50 via-surface-page to-brand-50/40']) }}>
        <x-public.container class="pb-14 pt-16 lg:pb-16 lg:pt-20">
            <x-public.heading :eyebrow="$eyebrow" :title="$title" :subtitle="$subtitle" level="h1" class="max-w-3xl" />
            @if ($slot->isNotEmpty())
                <div class="mt-6">{{ $slot }}</div>
            @endif
        </x-public.container>
    </section>
@endif
