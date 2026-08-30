@props([
    'error' => false,
])

<input {{ $attributes->class([
    'min-h-11 w-full rounded-[var(--radius-md)] border bg-paper px-3.5 py-2.5 text-sm text-ink shadow-sm transition-colors',
    'border-border focus:border-brand-600 focus:ring-2 focus:ring-brand-100' => ! $error,
    'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100' => $error,
]) }} />
