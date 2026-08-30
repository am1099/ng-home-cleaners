@props([
    'title',
    'message' => null,
])

<div {{ $attributes->class([
    'rounded-[var(--radius-card)] border border-dashed border-border-strong bg-surface-sunken/60 px-6 py-10 text-center',
]) }}>
    <p class="ng-display text-xl text-ink">{{ $title }}</p>
    @if ($message)
        <p class="mt-2 text-sm text-ink-muted">{{ $message }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
