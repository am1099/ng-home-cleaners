@props([
    'src',
    'alt',
    'width' => 800,
    'height' => 600,
    'lazy' => true,
    'priority' => false,
    'sizes' => null,
    'class' => '',
])

@php
    $loading = $priority ? 'eager' : ($lazy ? 'lazy' : 'eager');
    $fetchpriority = $priority ? 'high' : null;
@endphp

<img
    src="{{ $src }}"
    alt="{{ $alt }}"
    width="{{ $width }}"
    height="{{ $height }}"
    loading="{{ $loading }}"
    @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
    @if ($sizes) sizes="{{ $sizes }}" @endif
    decoding="{{ $priority ? 'sync' : 'async' }}"
    {{ $attributes->class([$class]) }}
>
