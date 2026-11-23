@props(['testimonials'])

@if ($testimonials->count() > 3)
    <div
        class="ng-review-marquee relative mt-10"
        style="--review-count: {{ $testimonials->count() }}"
        x-data="{ paused: false }"
        :data-paused="paused ? 'true' : 'false'"
    >
        <div class="mb-4 flex justify-end">
            <button
                type="button"
                class="text-sm font-semibold text-ink-muted hover:text-ink"
                @click="paused = !paused"
                x-text="paused ? 'Play' : 'Pause'"
            >Pause</button>
        </div>
        <div class="ng-review-marquee__window">
            <div class="ng-review-marquee__track items-start gap-6">
                @foreach ($testimonials as $testimonial)
                    <div class="w-80 shrink-0" wire:key="review-{{ $testimonial->id }}">
                        <x-public.testimonial-card :testimonial="$testimonial" />
                    </div>
                @endforeach
                @foreach ($testimonials as $testimonial)
                    <div class="w-80 shrink-0" aria-hidden="true">
                        <x-public.testimonial-card :testimonial="$testimonial" />
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="mt-10 grid items-start gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($testimonials as $testimonial)
            <x-public.testimonial-card :testimonial="$testimonial" wire:key="review-{{ $testimonial->id }}" />
        @endforeach
    </div>
@endif
