@props([
    'exclusion',
    'number',
])

@php
    $panelId = 'exclusion-panel-'.$exclusion->getKey();
@endphp

<details {{ $attributes->class(['ng-exclusion-item group rounded-[var(--radius-md)] border border-border bg-surface-card']) }}>
    <summary
        class="flex cursor-pointer list-none items-center gap-3 px-4 py-3.5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 [&::-webkit-details-marker]:hidden"
        aria-controls="{{ $panelId }}"
    >
        <span class="w-8 shrink-0 text-sm font-semibold tabular-nums text-brand-700">{{ str_pad((string) $number, 2, '0', STR_PAD_LEFT) }}</span>
        <span class="min-w-0 flex-1 text-left font-semibold text-ink">{{ $exclusion->task }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-ink-subtle transition duration-200 group-open:rotate-180" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
    </summary>
    <div id="{{ $panelId }}" class="border-t border-border px-4 py-3 pl-[3.25rem]">
        <p class="text-sm leading-relaxed text-neutral-700">
            {{ $exclusion->note ?: 'This sits outside the standard checklist. Ask if you need it added to your quote.' }}
        </p>
    </div>
</details>
