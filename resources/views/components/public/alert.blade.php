@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $variants = [
        'info' => 'border-brand-100 bg-brand-50 text-brand-950',
        'success' => 'border-green-200 bg-green-50 text-green-950',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-950',
        'error' => 'border-red-200 bg-red-50 text-red-950',
    ];
@endphp

<div {{ $attributes->class([
    'rounded-[var(--radius-lg)] border px-4 py-3 text-sm leading-relaxed',
    $variants[$variant] ?? $variants['info'],
]) }} role="alert">
    @if ($title)
        <p class="font-semibold">{{ $title }}</p>
    @endif
    <div @class(['mt-1' => $title])>{{ $slot }}</div>
</div>
