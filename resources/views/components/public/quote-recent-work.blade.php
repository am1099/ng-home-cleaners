@props(['photos'])

@php
    $photos = collect($photos)->values();
    $visible = $photos->take(min(6, $photos->count()));
    $rotates = $photos->count() > 6;
@endphp

<section
    wire:ignore
    data-quote-recent-work
    class="min-w-0"
    aria-labelledby="quote-recent-work-heading"
    x-data="ngQuoteGallery(@js($photos->all()))"
    x-on:mouseenter="hovered = true"
    x-on:mouseleave="hovered = false"
    x-on:focusin="focused = true"
    x-on:focusout="if (! $el.contains($event.relatedTarget)) focused = false"
>
    <div class="flex items-end justify-between gap-4">
        <div class="min-w-0">
            <h2 id="quote-recent-work-heading" class="ng-display text-xl sm:text-2xl">Recent work</h2>
            <p class="mt-1 text-sm leading-relaxed text-ink-muted">A look at homes we've cleaned around Nottingham.</p>
        </div>
        @if ($rotates)
            <div class="flex shrink-0 gap-2">
                <button
                    type="button"
                    class="inline-flex size-9 cursor-pointer items-center justify-center rounded-full border border-border bg-white text-ink transition hover:border-brand-200 hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                    x-on:click="step(-1)"
                    aria-label="Previous photos"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true">
                        <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="inline-flex size-9 cursor-pointer items-center justify-center rounded-full border border-border bg-white text-ink transition hover:border-brand-200 hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                    x-on:click="step(1)"
                    aria-label="Next photos"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <div class="mt-4 grid grid-cols-3 gap-2 sm:gap-3">
        @foreach ($visible as $index => $photo)
            @php
                $alt = filled($photo['alt'] ?? null) ? $photo['alt'] : '';
                $label = $alt !== ''
                    ? 'View photo: '.$alt
                    : 'View full-size photo '.($index + 1).' of '.$visible->count();
            @endphp
            <button
                type="button"
                class="quote-recent-work__tile group relative aspect-[4/3] cursor-zoom-in overflow-hidden rounded-[var(--radius-md)] bg-surface-sunken focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                x-on:click.prevent="openAt({{ $index }})"
                x-bind:aria-label="tileLabel({{ $index }})"
                aria-label="{{ $label }}"
            >
                <img
                    src="{{ $photo['src'] }}"
                    alt="{{ $alt }}"
                    width="640"
                    height="480"
                    loading="lazy"
                    decoding="async"
                    class="absolute inset-0 size-full object-cover transition-opacity duration-700 motion-reduce:transition-none"
                    style="opacity: 1"
                    x-bind:src="slotsA[{{ $index }}]?.src || ''"
                    x-bind:alt="front === 'a' ? (slotsA[{{ $index }}]?.alt || '') : ''"
                    x-bind:style="front === 'a' ? 'opacity: 1' : 'opacity: 0'"
                >
                <img
                    src="{{ $photo['src'] }}"
                    alt=""
                    width="640"
                    height="480"
                    loading="lazy"
                    decoding="async"
                    class="absolute inset-0 size-full object-cover transition-opacity duration-700 motion-reduce:transition-none"
                    style="opacity: 0"
                    x-bind:src="slotsB[{{ $index }}]?.src || ''"
                    x-bind:alt="front === 'b' ? (slotsB[{{ $index }}]?.alt || '') : ''"
                    x-bind:style="front === 'b' ? 'opacity: 1' : 'opacity: 0'"
                >
            </button>
        @endforeach
    </div>
    <p class="sr-only" aria-live="polite" x-text="liveMessage"></p>
</section>
