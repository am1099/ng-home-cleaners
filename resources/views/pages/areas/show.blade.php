@extends('layouts.public')

@section('content')
    <x-public.page-hero
        :eyebrow="$area->postcode_label"
        :title="$area->name"
        :subtitle="$area->short_intro"
    />

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
                            <a href="{{ route('services.show', $service) }}" class="inline-flex rounded-[var(--radius-pill)] border border-border bg-surface-card px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-50">
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
                            <a href="{{ route('services.show', $service) }}" class="inline-flex rounded-[var(--radius-pill)] border border-border bg-surface-card px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-50">
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
