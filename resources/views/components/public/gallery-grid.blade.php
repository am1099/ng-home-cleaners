@props(['items'])

@if ($items->isNotEmpty())
    <div {{ $attributes->class(['ng-gallery-grid grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4']) }}>
        @foreach ($items as $index => $item)
            @php
                $src = \App\Support\Media::url($item->image_path);
                $alt = filled($item->alt_text) ? $item->alt_text : '';
                $caption = filled($item->caption) ? $item->caption : '';
                $label = $alt !== ''
                    ? 'View photo: '.$alt
                    : 'View full-size photo '.($index + 1).' of '.$items->count();
            @endphp
            <figure class="overflow-hidden rounded-[var(--radius-card)] bg-surface-sunken">
                <button
                    type="button"
                    class="group relative block w-full cursor-zoom-in text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                    data-lightbox-group="home-gallery"
                    data-lightbox-index="{{ $index }}"
                    data-lightbox-src="{{ $src }}"
                    data-lightbox-alt="{{ $alt }}"
                    @if ($caption !== '') data-lightbox-caption="{{ $caption }}" @endif
                    aria-label="{{ $label }}"
                >
                    <x-public.img
                        :src="$src"
                        :alt="$alt"
                        :width="800"
                        :height="600"
                        sizes="(min-width: 1024px) 25vw, (min-width: 768px) 33vw, 50vw"
                        class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.02] motion-reduce:transition-none motion-reduce:group-hover:scale-100"
                        :lazy="true"
                    />
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
