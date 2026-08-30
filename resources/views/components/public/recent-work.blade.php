@props(['items'])

<section id="recent-work" class="scroll-mt-[calc(var(--height-header)+1rem)]">
    <x-public.section variant="sunken">
        <x-public.container>
            <x-public.heading
                eyebrow="Recent work"
                title="Before & after."
                subtitle="Tap any photo to view it full size."
            />

            <div class="mt-10 grid gap-6 md:grid-cols-2">
                @foreach ($items as $index => $item)
                    @php
                        $beforeSrc = \App\Support\Media::url($item->before_image_path);
                        $afterSrc = \App\Support\Media::url($item->after_image_path);
                    @endphp
                    <article
                        class="overflow-hidden rounded-[var(--radius-card)] bg-surface-card shadow-[var(--shadow-card)] transition duration-500 ease-out motion-reduce:transform-none motion-reduce:opacity-100"
                        x-data="{ shown: false }"
                        x-intersect.once="shown = true"
                        x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                        style="transition-delay: {{ min($index * 80, 240) }}ms"
                    >
                        <div class="grid grid-cols-2 gap-px bg-border">
                            <div class="relative bg-surface-sunken">
                                <span class="pointer-events-none absolute left-2 top-2 z-10 rounded bg-ink px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-white">Before</span>
                                <button
                                    type="button"
                                    class="group block w-full cursor-zoom-in focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                                    data-lightbox-src="{{ $beforeSrc }}"
                                    data-lightbox-alt="{{ $item->alt_text_before }}"
                                    data-lightbox-caption="{{ $item->title }} - before"
                                    aria-label="View before photo full size: {{ $item->title }}"
                                >
                                    <x-public.img
                                        :src="$beforeSrc"
                                        :alt="$item->alt_text_before"
                                        :width="480"
                                        :height="360"
                                        class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                                        :lazy="true"
                                    />
                                </button>
                            </div>
                            <div class="relative bg-surface-sunken">
                                <span class="pointer-events-none absolute left-2 top-2 z-10 rounded bg-ink px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-white">After</span>
                                <button
                                    type="button"
                                    class="group block w-full cursor-zoom-in focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                                    data-lightbox-src="{{ $afterSrc }}"
                                    data-lightbox-alt="{{ $item->alt_text_after }}"
                                    data-lightbox-caption="{{ $item->title }} - after"
                                    aria-label="View after photo full size: {{ $item->title }}"
                                >
                                    <x-public.img
                                        :src="$afterSrc"
                                        :alt="$item->alt_text_after"
                                        :width="480"
                                        :height="360"
                                        class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                                        :lazy="true"
                                    />
                                </button>
                            </div>
                        </div>
                        <div class="px-5 py-5 sm:px-6">
                            <h3 class="ng-display text-xl text-ink">{{ $item->title }}</h3>
                            @if ($item->description)
                                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-ink-muted">{{ $item->description }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </x-public.container>
    </x-public.section>
</section>
