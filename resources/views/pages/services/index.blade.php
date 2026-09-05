@extends('layouts.public')

@section('content')
    <x-public.page-hero
        eyebrow="Services · {{ $settings->service_area_summary }}"
        title="Cleaning Services in Nottingham"
        subtitle="Four services, one checklist, and a fixed price agreed in writing before a cleaner sets foot in the property."
    />

    <x-public.section>
        <x-public.container>
            <x-public.heading
                eyebrow="What is included"
                title="Each clean covers a different amount of ground."
                subtitle="Here is exactly where the line sits, so nothing is a surprise on the day."
            />
            @if ($services->isNotEmpty())
                <div class="mt-10 grid items-stretch gap-6 sm:grid-cols-2">
                    @foreach ($services as $service)
                        <x-public.service-card :service="$service" />
                    @endforeach
                </div>
            @else
                <x-public.empty-state class="mt-10" title="No services published" message="Services will appear here once they are active in the admin." />
            @endif
        </x-public.container>
    </x-public.section>

    @if ($exclusions->isNotEmpty())
        <x-public.section variant="sunken">
            <x-public.container>
                <x-public.heading
                    eyebrow="What is not included"
                    title="Most companies leave this off their website."
                    subtitle="We would rather you knew now than found out on the day - every one of these can be added to your quote if you ask."
                />
                <ul class="mt-10 grid gap-4 sm:grid-cols-2">
                    @foreach ($exclusions as $exclusion)
                        <li class="rounded-[var(--radius-card)] border border-border bg-surface-card p-5 shadow-[var(--shadow-card)]">
                            <p class="font-semibold text-ink">{{ $exclusion->task }}</p>
                            @if ($exclusion->note)
                                <p class="mt-2 text-sm leading-relaxed text-ink-muted">{{ $exclusion->note }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </x-public.container>
        </x-public.section>
    @endif

    @if ($areas->isNotEmpty())
        <x-public.section>
            <x-public.container>
                <x-public.heading
                    title="Available across Nottingham"
                    subtitle="Every service above can be booked in the districts we cover."
                />
                <ul class="mt-6 flex flex-wrap gap-2">
                    @foreach ($areas as $area)
                        <li>
                            <a href="{{ route('areas.show', $area) }}" class="inline-flex rounded-[var(--radius-pill)] border border-border bg-surface-card px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-50">
                                {{ $area->postcode_label }} · {{ $area->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-6">
                    <a href="{{ route('areas') }}" class="ng-link">View all service areas</a>
                </p>
            </x-public.container>
        </x-public.section>
    @endif

    <x-public.final-cta title="Send a postcode, get a price you can hold us to" />
@endsection
