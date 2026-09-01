@extends('layouts.public')

@section('content')
    <x-public.page-hero
        :eyebrow="$area->postcode_label"
        :title="$service->name.' in '.$area->name"
        :subtitle="$service->short_description"
        :image="$service->heroImageUrl()"
        :image-alt="$service->name"
    >
        <div class="flex flex-wrap gap-3">
            <x-public.estimate-cta
                :label="$service->cta_label ?: 'Get a free estimate'"
                size="lg"
                location="area_service_hero"
                :href="route('quote', ['service' => $service->slug])"
            />
            <x-public.button
                href="{{ $settings->phoneTel() }}"
                variant="phone"
                size="lg"
                onclick="window.ngTrack && window.ngTrack('phone_clicked', { location: 'area_service_hero' })"
            >
                {{ $settings->phoneDisplay() }}
            </x-public.button>
        </div>
    </x-public.page-hero>

    <x-public.section spacing="follow">
        <x-public.container>
            <div class="max-w-3xl">
                <p class="text-lg leading-relaxed text-ink-muted">
                    {{ $service->name }} across {{ $area->name }} ({{ $area->postcode_label }}). Travel inside NG1 to NG16 is included. The guide price on the estimate form is the same engine we use everywhere else.
                </p>
                @if ($area->short_intro)
                    <p class="mt-4 text-sm leading-relaxed text-ink-muted">{{ $area->short_intro }}</p>
                @endif
            </div>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-public.button href="{{ route('services.show', $service) }}" variant="outline">
                    Full {{ strtolower($service->name) }} details
                </x-public.button>
                <x-public.button href="{{ route('areas.show', $area) }}" variant="outline">
                    More about {{ $area->name }}
                </x-public.button>
            </div>
        </x-public.container>
    </x-public.section>

    <x-public.final-cta />
@endsection
