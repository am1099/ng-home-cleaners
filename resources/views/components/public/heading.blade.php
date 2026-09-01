@props([
    'eyebrow' => null,
    'title' => null,
    'subtitle' => null,
    'level' => 'h2',
    'align' => 'left',
    'subtitleClass' => 'text-ink-muted',
])

@php
    $alignClass = match ($align) {
        'center' => 'text-center mx-auto',
        default => 'text-left',
    };
@endphp

<div {{ $attributes->class(['max-w-3xl', $alignClass]) }}>
    @if ($eyebrow)
        <p class="ng-eyebrow">{{ $eyebrow }}</p>
    @endif

    @if ($title)
        <{{ $level }} @class([
            'ng-display mt-0 text-balance',
            'ng-display-hero' => $level === 'h1',
            'ng-display-section mt-3 text-3xl sm:text-4xl' => $level !== 'h1',
        ])>
            {{ $title }}
        </{{ $level }}>
    @endif

    @if ($subtitle)
        <p @class([
            'ng-body-lg',
            $subtitleClass,
            'mt-[18px]' => $level === 'h1',
            'mt-4' => $level !== 'h1',
        ])>{{ $subtitle }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>
