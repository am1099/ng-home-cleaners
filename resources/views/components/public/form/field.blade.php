@props([
    'label',
    'name',
    'hint' => null,
    'error' => null,
    'required' => false,
])

<div {{ $attributes->class(['flex flex-col gap-2']) }}>
    <label for="{{ $name }}" class="text-sm font-semibold text-ink">
        {{ $label }}
        @if ($required)
            <span class="text-brand-700" aria-hidden="true">*</span>
            <span class="sr-only">(required)</span>
        @endif
    </label>

    {{ $slot }}

    @if ($hint && ! $error)
        <p id="{{ $name }}-hint" class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif

    @if ($error)
        <p id="{{ $name }}-error" class="text-sm text-red-700" role="alert">{{ $error }}</p>
    @endif
</div>
