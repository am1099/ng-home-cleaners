@extends('layouts.public')

@section('content')
    <x-public.page-hero
        eyebrow="About us"
        :title="$settings->about_hero_title ?? 'A small Nottingham team, not a national franchise.'"
        :subtitle="$settings->about_hero_subtitle ?? $settings->service_area_summary"
    />

    @if ($settings->about_story)
        <x-public.section spacing="follow">
            <x-public.container>
                <div class="grid gap-8 lg:grid-cols-2 lg:gap-16">
                    <div>
                        <h2 class="ng-display ng-display-section text-ink">Local, and staying that way</h2>
                    </div>
                    <div class="prose-ng max-w-xl">
                        {!! nl2br(e($settings->about_story)) !!}
                    </div>
                </div>
            </x-public.container>
        </x-public.section>
    @endif

    @if (! empty($settings->about_promises))
        <x-public.section>
            <x-public.container>
                <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">
                    <div>
                        <h2 class="ng-display ng-display-section text-ink">What we promise, in plain terms</h2>
                        <p class="mt-3.5 max-w-md text-base leading-relaxed text-ink-muted">Four commitments. Break one and we put it right on our own time, at our own cost.</p>
                    </div>
                    <ul class="space-y-3.5">
                        @foreach ($settings->about_promises as $promise)
                            <li class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 size-[18px] shrink-0 text-brand-500" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/>
                                </svg>
                                <span class="text-base leading-relaxed text-ink-muted">{{ $promise }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </x-public.container>
        </x-public.section>
    @endif

    <x-public.section variant="sunken">
        <x-public.container narrow>
            <x-public.trust-strip />
            @if ($settings->show_dbs_statement && $settings->dbs_statement)
                <p class="mt-6 text-sm text-ink-muted">{{ $settings->dbs_statement }}</p>
            @endif
            @if ($settings->show_insurance_statement && $settings->insurance_statement)
                <p class="mt-4 text-sm text-ink-muted">{{ $settings->insurance_statement }}</p>
            @endif
        </x-public.container>
    </x-public.section>

    @if ($areas->isNotEmpty())
        <x-public.section>
            <x-public.container>
                <p class="ng-eyebrow">Coverage</p>
                <h2 class="ng-display ng-display-section text-ink">Cleaning across Nottingham and the surrounding areas</h2>
                <div class="mt-8 grid gap-x-8 gap-y-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($areas as $area)
                        <a href="{{ route('areas.show', $area) }}" class="text-base text-ink-muted transition-colors hover:text-brand-800">
                            {{ $area->postcode_label }} · {{ $area->name }}
                        </a>
                    @endforeach
                </div>
                <p class="mt-7 text-sm text-ink-subtle">
                    Not sure if you are inside the line?
                    <a href="{{ route('areas') }}" class="font-semibold text-brand-700 hover:text-brand-600">See the district list</a>
                    or send your postcode and we will tell you straight.
                </p>
            </x-public.container>
        </x-public.section>
    @endif

    <x-public.final-cta title="Check your postcode, get your price" />
@endsection
