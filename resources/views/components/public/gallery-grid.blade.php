@props(['items'])

@if ($items->isNotEmpty())
    <div {{ $attributes->class(['mx-auto flex max-w-7xl flex-wrap justify-center gap-8']) }}>
        @foreach ($items as $item)
            @php
                $src = \App\Support\Media::url($item->image_path);
                $label = filled($item->caption) ? $item->caption : $item->alt_text;
            @endphp
            <figure class="w-full overflow-hidden rounded-[var(--radius-card)] bg-surface-card shadow-[var(--shadow-card)] sm:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.5rem)] lg:max-w-md">
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
                        <span class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent px-5 pb-5 pt-20">
                            <span class="line-clamp-2 text-sm font-medium leading-snug text-white drop-shadow-sm sm:text-base">
                                {{ $label }}
                            </span>
                        </span>
                    @endif
                </button>
            </figure>
        @endforeach
    </div>
@else
    <x-public.empty-state
        title="Gallery coming soon"
        message="We will add photographs from recent jobs here as they are published in the admin."
    />
@endif
