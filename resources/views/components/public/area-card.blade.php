@props(['area'])

<a href="{{ route('areas.show', $area) }}" {{ $attributes->class(['group ng-area-card']) }}>
    <div class="flex items-center gap-2.5">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4 shrink-0 text-brand-600" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.5 7-10a7 7 0 1 0-14 0c0 5.5 7 10 7 10Z"/>
            <circle cx="12" cy="11" r="2.5"/>
        </svg>
        <p class="text-xs font-semibold uppercase tracking-[var(--tracking-eyebrow)] text-brand-600">{{ $area->postcode_label }}</p>
    </div>
    <h3 class="ng-display text-xl group-hover:text-brand-800">{{ $area->name }}</h3>
    <p class="text-sm leading-relaxed text-ink-muted">{{ $area->short_intro }}</p>
</a>
