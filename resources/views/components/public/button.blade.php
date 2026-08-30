@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-[var(--radius-pill)] font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 disabled:pointer-events-none disabled:opacity-50';

    $sizes = [
        'sm' => 'min-h-10 px-4 text-sm',
        'md' => 'min-h-11 px-5 text-sm',
        'lg' => 'min-h-12 px-6 text-base',
    ];

    $variants = [
        'primary' => 'bg-brand-700 text-white shadow-md shadow-brand-700/25 hover:bg-brand-800 hover:shadow-lg hover:shadow-brand-700/30 focus-visible:outline-brand-700',
        'secondary' => 'bg-brand-100 text-brand-950 hover:bg-brand-100/80 focus-visible:outline-brand-600',
        'outline' => 'border-2 border-brand-600/35 bg-white/90 text-brand-900 shadow-sm backdrop-blur-sm hover:border-brand-700 hover:bg-brand-50 hover:text-brand-950 focus-visible:outline-brand-600',
        'inverse' => 'bg-ink-inverse text-brand-950 shadow-md shadow-black/10 hover:bg-white focus-visible:outline-ink-inverse',
        'ghost' => 'bg-transparent text-brand-800 hover:bg-brand-50 focus-visible:outline-brand-600',
        'whatsapp' => 'bg-[#25D366] text-white shadow-md shadow-[#25D366]/30 hover:bg-[#1ebe5d] hover:shadow-lg hover:shadow-[#25D366]/35 focus-visible:outline-[#25D366]',
        'phone' => 'bg-gradient-to-b from-brand-600 to-brand-800 text-white shadow-md shadow-brand-800/30 ring-1 ring-inset ring-white/15 hover:from-brand-700 hover:to-brand-900 focus-visible:outline-brand-700',
    ];

    $classes = $base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
