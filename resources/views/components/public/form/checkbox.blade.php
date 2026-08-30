@props([
    'label',
    'name',
    'value' => null,
])

<label {{ $attributes->class([
    'inline-flex min-h-11 cursor-pointer items-center gap-3 rounded-[var(--radius-pill)] border border-border bg-surface-card px-4 py-2.5 text-sm font-medium text-ink transition-colors has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-brand-600',
]) }}>
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked($attributes->get('checked'))
        {{ $attributes->except('class', 'checked') }}
        class="size-4 shrink-0 rounded border-border text-brand-700 focus:ring-brand-600"
    />
    <span>{{ $label }}</span>
</label>
