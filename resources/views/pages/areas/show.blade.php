@extends('layouts.public')

@section('content')
    <x-public.page-hero
        :eyebrow="$area->postcode_label"
        :title="$area->name"
        :subtitle="$area->short_intro"
    >
        <div class="flex flex-wrap gap-3">
            <x-public.button
                href="{{ $settings->phoneTel() }}"
                variant="phone"
                size="lg"
                onclick="window.ngTrack && window.ngTrack('phone_clicked', { location: 'area_hero' })"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 15.352V16.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd"/></svg>
                {{ $settings->phoneDisplay() }}
            </x-public.button>
            <x-public.estimate-cta size="lg" variant="outline" location="area_hero" />
        </div>
    </x-public.page-hero>

    @if ($area->content)
        <x-public.section spacing="follow">
            <x-public.container narrow>
                <div class="prose-ng text-lg leading-relaxed text-ink-muted">
                    {!! nl2br(e($area->content)) !!}
                </div>
            </x-public.container>
        </x-public.section>
    @endif

    @if ($area->services->isNotEmpty())
        <x-public.section>
            <x-public.container narrow>
                <x-public.heading title="Services available in {{ $area->name }}" />
                <ul class="mt-6 flex flex-wrap gap-2">
                    @foreach ($area->services as $service)
                        <li>
                            <a href="{{ route('areas.service', [$area, $service]) }}" class="inline-flex rounded-[var(--radius-pill)] border border-border bg-surface-card px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-50">
                                {{ $service->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-6 text-sm text-ink-muted">
                    Prefer to compare everything first?
                    <a href="{{ route('services') }}" class="ng-link">See all cleaning services</a>.
                </p>
            </x-public.container>
        </x-public.section>
    @elseif ($allServices->isNotEmpty())
        <x-public.section>
            <x-public.container narrow>
                <x-public.heading title="Cleaning services we offer nearby" />
                <ul class="mt-6 flex flex-wrap gap-2">
                    @foreach ($allServices as $service)
                        <li>
                            <a href="{{ route('areas.service', [$area, $service]) }}" class="inline-flex rounded-[var(--radius-pill)] border border-border bg-surface-card px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-50">
                                {{ $service->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </x-public.container>
        </x-public.section>
    @endif

    @if ($area->coverage_notes)
        <x-public.section variant="sunken">
            <x-public.container narrow>
                <x-public.alert variant="info" title="Coverage notes">{{ $area->coverage_notes }}</x-public.alert>
            </x-public.container>
        </x-public.section>
    @endif

    @if ($area->faqs->isNotEmpty())
        <x-public.section>
            <x-public.container narrow>
                <x-public.heading title="Questions about cleaning in {{ $area->name }}" />
                <div class="mt-8">
                    <x-public.faq-list :faqs="$area->faqs" />
                </div>
            </x-public.container>
        </x-public.section>
    @endif

    @if ($relatedAreas->isNotEmpty())
        <x-public.section>
            <x-public.container>
                <x-public.heading title="Nearby districts we also cover" />
                <ul class="mt-6 flex flex-wrap gap-2">
                    @foreach ($relatedAreas as $related)
                        <li>
                            <a href="{{ route('areas.show', $related) }}" class="inline-flex rounded-[var(--radius-pill)] border border-border bg-surface-card px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-50">
                                {{ $related->postcode_label }} · {{ $related->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-6">
                    <a href="{{ route('areas') }}" class="ng-link">View the full areas list</a>
                </p>
            </x-public.container>
        </x-public.section>
    @endif

    <x-public.final-cta title="Check your postcode" />
@endsection
