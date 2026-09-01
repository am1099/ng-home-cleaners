@extends('layouts.public')

@section('content')
    <x-public.page-hero
        eyebrow="Reviews"
        title="What customers say"
        subtitle="Published Google reviews from homes we have cleaned across Nottingham. Demo reviews never appear here."
    >
        <x-public.estimate-cta size="lg" location="reviews_hero" />
    </x-public.page-hero>

    <x-public.section spacing="follow">
        <x-public.container>
            @if ($testimonials->isNotEmpty())
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <x-public.testimonial-card :testimonial="$testimonial" :key="'review-'.$testimonial->id" />
                    @endforeach
                </div>
            @else
                <x-public.empty-state title="No reviews published yet" message="Customer reviews will appear here once they are published in the admin." />
            @endif
        </x-public.container>
    </x-public.section>

    <x-public.final-cta />
@endsection
