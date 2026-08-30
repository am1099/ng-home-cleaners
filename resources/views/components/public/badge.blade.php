@props([
    'tone' => 'default',
    'icon' => null,
])

@php
    $tones = [
        'default' => 'border-border bg-surface-card text-ink',
        'accent' => 'border-brand-100 bg-brand-50 text-brand-950',
        'brass' => 'border-accent-brass/30 bg-paper text-ink',
        'inverse' => 'border-brand-800/40 bg-brand-900/30 text-ink-inverse',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center gap-2 rounded-[var(--radius-pill)] border px-3 py-1.5 text-sm font-medium',
    $tones[$tone] ?? $tones['default'],
]) }}>
    @if ($icon)
        <span class="inline-flex shrink-0 text-brand-600" aria-hidden="true">{!! $icon !!}</span>
    @endif
    {{ $slot }}
</span>
