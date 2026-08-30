@props(['service'])

@php
    $cardImage = filled($service->card_image)
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($service->card_image)
        : null;
@endphp

<a
    href="{{ route('services.show', $service) }}"
    {{ $attributes->class([
        'group flex h-full flex-col overflow-hidden rounded-[var(--radius-card)] border border-border bg-surface-card shadow-[var(--shadow-card)] transition hover:border-brand-200 hover:shadow-[var(--shadow-elevated)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600',
    ]) }}
    aria-label="{{ $service->name }}: see what is included"
>
    @if ($cardImage)
        <div class="relative aspect-[16/10] overflow-hidden bg-surface-sunken">
            <img
                src="{{ $cardImage }}"
                alt=""
                class="size-full object-cover transition duration-500 group-hover:scale-[1.03]"
                loading="lazy"
                width="640"
                height="400"
            />
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
        </div>
    @else
        <div class="flex aspect-[16/10] items-center justify-center bg-gradient-to-br from-brand-50 via-surface-sunken to-brand-100/60">
            <x-public.service-icon :icon="$service->icon" class="size-14 bg-transparent text-brand-700/80" />
        </div>
    @endif

    <div class="flex flex-1 flex-col p-6 sm:p-7">
        <p class="text-xs font-semibold uppercase tracking-[var(--tracking-eyebrow)] text-brand-700">{{ $service->name }}</p>
        <h3 class="ng-display mt-2 text-2xl text-ink transition group-hover:text-brand-800">{{ $service->card_title }}</h3>
        <p class="mt-3 flex-1 text-sm leading-relaxed text-ink-muted">{{ $service->short_description }}</p>
        <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-brand-800">
            See what is included
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 transition group-hover:translate-x-0.5" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
        </span>
    </div>
</a>
