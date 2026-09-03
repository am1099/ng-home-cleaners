@props([
    'id',
    'label',
    'field',
    'value',
    'min' => 0,
    'max' => 10,
    'display' => null,
    'hint' => null,
])

@php
    $shown = $display ?? (string) $value;
    $canDecrement = (int) $value > (int) $min;
    $canIncrement = (int) $value < (int) $max;
@endphp

<div {{ $attributes->class(['ng-quantity-field']) }}>
    <label class="text-sm font-semibold text-ink" for="{{ $id }}">{{ $label }}</label>

    <div
        class="mt-2 inline-flex w-full max-w-[13.5rem] items-stretch overflow-hidden rounded-[var(--radius-md)] border border-border bg-surface-card shadow-sm"
        role="group"
        aria-labelledby="{{ $id }}-label"
    >
        <span id="{{ $id }}-label" class="sr-only">{{ $label }}</span>

        <button
            type="button"
            wire:click="adjustQuantity('{{ $field }}', -1)"
            @disabled(! $canDecrement)
            class="inline-flex min-h-11 min-w-11 items-center justify-center border-e border-border text-lg font-semibold text-ink transition hover:bg-brand-50 hover:text-brand-700 disabled:cursor-not-allowed disabled:text-ink-muted/40 disabled:hover:bg-transparent"
            aria-label="Decrease {{ strtolower($label) }}"
        >
            <span aria-hidden="true">−</span>
        </button>

        <div
            id="{{ $id }}"
            class="flex min-w-0 flex-1 items-center justify-center px-3 text-center text-base font-semibold tabular-nums text-ink"
            aria-live="polite"
            aria-atomic="true"
        >
            {{ $shown }}
        </div>

        <button
            type="button"
            wire:click="adjustQuantity('{{ $field }}', 1)"
            @disabled(! $canIncrement)
            class="inline-flex min-h-11 min-w-11 items-center justify-center border-s border-border text-lg font-semibold text-ink transition hover:bg-brand-50 hover:text-brand-700 disabled:cursor-not-allowed disabled:text-ink-muted/40 disabled:hover:bg-transparent"
            aria-label="Increase {{ strtolower($label) }}"
        >
            <span aria-hidden="true">+</span>
        </button>
    </div>

    @if ($hint)
        <p class="mt-1.5 text-xs text-ink-muted">{{ $hint }}</p>
    @endif

    @error($field)
        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
