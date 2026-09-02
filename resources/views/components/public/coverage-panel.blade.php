@props([
    'areas',
    'summary' => null,
])

@php
    $displayAreas = $areas->take(6);
    $hasMore = $areas->count() > 6;
@endphp

<x-public.section variant="ink" class="relative overflow-hidden">
    {{-- Soft concentric rings --}}
    <div class="pointer-events-none absolute -right-24 top-1/2 hidden h-[420px] w-[420px] -translate-y-1/2 opacity-30 lg:block" aria-hidden="true">
        <svg viewBox="0 0 420 420" class="h-full w-full text-brand-200/40" fill="none">
            <circle cx="210" cy="210" r="80" stroke="currentColor" stroke-width="1" />
            <circle cx="210" cy="210" r="130" stroke="currentColor" stroke-width="1" />
            <circle cx="210" cy="210" r="180" stroke="currentColor" stroke-width="1" />
            <circle cx="210" cy="210" r="205" stroke="currentColor" stroke-width="1" />
        </svg>
    </div>

    <x-public.container>
        <div
            class="relative grid gap-10 transition duration-500 ease-out motion-reduce:transform-none motion-reduce:opacity-100 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:items-center lg:gap-16"
            x-data="{ shown: false }"
            x-intersect.once="shown = true"
            x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-3 opacity-0'"
        >
            <div>
                <p class="ng-eyebrow">Coverage</p>
                <h2 class="ng-display ng-display-section mt-0 text-ink-inverse">
                    House cleaning across <em class="italic">Nottingham &amp; nearby.</em>
                </h2>
                <p class="mt-4 max-w-md text-base leading-relaxed text-brand-100/90">
                    {{ $summary ?: 'We stay local so we can hold the slot we give you, and turn up when we say we will.' }}
                </p>
                <div class="mt-7 max-w-md">
                    <livewire:coverage-checker variant="ink" />
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-ink-inverse/80">Areas we cover</p>

                @if ($displayAreas->isNotEmpty())
                    <div class="mt-5 grid grid-cols-2 border-t border-l border-white/15">
                        @foreach ($displayAreas as $area)
                            <a
                                href="{{ route('areas.show', $area) }}"
                                class="border-b border-r border-white/15 px-4 py-5 transition-colors hover:bg-white/5 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-brand-100 sm:px-5 sm:py-6"
                            >
                                <span class="ng-display block text-lg text-ink-inverse sm:text-xl">{{ $area->name }}</span>
                                @if ($area->postcode_label)
                                    <span class="mt-1.5 block text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-100/80">{{ $area->postcode_label }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                    <p class="mt-5 text-sm text-brand-100/80">
                        @if ($hasMore)
                            Wherever you are, <a href="{{ route('areas') }}" class="ng-link font-semibold">see every district we cover</a> and we will confirm.
                        @else
                            Wherever you are, just check your postcode and we will confirm.
                            <a href="{{ route('areas') }}" class="ng-link ml-1 font-semibold">Full area list</a>
                        @endif
                    </p>
                @else
                    <p class="mt-5 text-sm text-brand-100/80">
                        Tell us your postcode on the estimate form and we will confirm coverage.
                    </p>
                @endif
            </div>
        </div>
    </x-public.container>
</x-public.section>
