@props([
    'variant' => 'default',
    'padding' => true,
])

@php
    $classes = match ($variant) {
        'ink' => 'border border-brand-800/30 bg-brand-900/40 text-ink-inverse',
        'flat' => 'border border-border bg-surface-card',
        default => 'border border-border bg-surface-card shadow-[var(--shadow-card)]',
    };

    if ($padding) {
        $classes .= ' p-6 sm:p-7';
    }
@endphp

<div {{ $attributes->class(['rounded-[var(--radius-card)]', $classes]) }}>
    {{ $slot }}
</div>
