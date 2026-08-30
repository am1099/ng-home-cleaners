@props([
    'variant' => 'default',
    'tag' => 'section',
    'spacing' => 'default',
])

@php
    $pad = match ($spacing) {
        'hero' => 'ng-section-hero',
        'follow' => 'ng-section-follow',
        default => 'ng-section',
    };

    $surface = match ($variant) {
        'sunken' => 'ng-section-sunken',
        'ink' => 'ng-section-ink',
        default => '',
    };
@endphp

<{{ $tag }} {{ $attributes->class([$pad, $surface]) }}>
    {{ $slot }}
</{{ $tag }}>
