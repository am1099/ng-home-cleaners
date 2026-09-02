@props(['items'])

@if ($items->isNotEmpty())
    <div
        {{ $attributes->class(['ng-gallery-carousel']) }}
        x-data="ngGalleryCarousel({{ $items->count() }})"
    >
        <div class="relative">
            <div
                x-ref="track"
                class="ng-gallery-carousel__track flex snap-x snap-mandatory gap-4 overflow-x-auto overscroll-x-contain scroll-smooth pb-2"
                @scroll.passive="updateIndex()"
            >
                @foreach ($items as $index => $item)
                    @php
                        $src = \App\Support\Media::url($item->image_path);
                        $label = filled($item->caption) ? $item->caption : $item->alt_text;
                    @endphp
                    <figure
                        data-gallery-slide="{{ $index }}"
                        class="w-[min(100%,20rem)] shrink-0 snap-center overflow-hidden rounded-[var(--radius-card)] bg-surface-card shadow-[var(--shadow-card)] sm:w-[calc(50%-0.5rem)] lg:w-[calc(33.333%-0.667rem)]"
                    >
                        <button
                            type="button"
                            class="group relative block w-full cursor-zoom-in text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                            data-lightbox-src="{{ $src }}"
                            data-lightbox-alt="{{ $item->alt_text }}"
                            data-lightbox-caption="{{ $item->caption }}"
                            aria-label="View full-size image{{ $label ? ': '.$label : '' }}"
                        >
                            <x-public.img
                                :src="$src"
                                :alt="$item->alt_text"
                                :width="800"
                                :height="600"
                                class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                                :lazy="true"
                            />
                            @if (filled($label))
                                <span class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent px-4 pb-4 pt-16 sm:px-5 sm:pb-5 sm:pt-20">
                                    <span class="line-clamp-2 text-sm font-medium leading-snug text-white drop-shadow-sm">
                                        {{ $label }}
                                    </span>
                                </span>
                            @endif
                        </button>
                    </figure>
                @endforeach
            </div>

            @if ($items->count() > 1)
                <div class="pointer-events-none absolute inset-y-0 left-0 hidden w-12 items-center sm:flex">
                    <button
                        type="button"
                        class="pointer-events-auto inline-flex size-10 cursor-pointer items-center justify-center rounded-full border border-border bg-surface-page/95 text-ink shadow-sm backdrop-blur-sm transition hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:cursor-not-allowed disabled:opacity-40"
                        x-on:click="prev()"
                        x-bind:disabled="index === 0"
                        aria-label="Previous photo"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true">
                            <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-12 items-center justify-end sm:flex">
                    <button
                        type="button"
                        class="pointer-events-auto inline-flex size-10 cursor-pointer items-center justify-center rounded-full border border-border bg-surface-page/95 text-ink shadow-sm backdrop-blur-sm transition hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:cursor-not-allowed disabled:opacity-40"
                        x-on:click="next()"
                        x-bind:disabled="index >= total - 1"
                        aria-label="Next photo"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        @if ($items->count() > 1)
            <div class="mt-4 flex items-center justify-center gap-2" role="tablist" aria-label="Gallery photos">
                @foreach ($items as $index => $item)
                    <button
                        type="button"
                        role="tab"
                        class="size-2 rounded-full transition"
                        x-bind:class="index === {{ $index }} ? 'bg-brand-700 scale-110' : 'bg-brand-200 hover:bg-brand-300'"
                        x-bind:aria-selected="index === {{ $index }} ? 'true' : 'false'"
                        x-on:click="scrollTo({{ $index }})"
                        aria-label="Photo {{ $index + 1 }} of {{ $items->count() }}"
                    ></button>
                @endforeach
            </div>
            <p class="sr-only" aria-live="polite" x-text="`Photo ${index + 1} of ${total}`"></p>
        @endif
    </div>
@else
    <x-public.empty-state
        title="Gallery coming soon"
        message="We will add photographs from recent jobs here as they are published in the admin."
    />
@endif
