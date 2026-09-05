@extends('layouts.public')

@section('content')
    <x-public.page-hero
        :eyebrow="$service->name ?: 'Cleaning service'"
        :title="$service->pageHeading()"
        :subtitle="$service->short_description"
        :image="$service->heroImageUrl()"
        :image-alt="$service->name.' by NG Home Cleaners in Nottingham'"
    >
        <div class="flex flex-wrap gap-3">
            <x-public.estimate-cta :label="$service->cta_label ?: 'Get a free estimate'" size="lg" />
            <x-public.whatsapp-cta size="lg" />
        </div>
    </x-public.page-hero>

    @if ($service->full_description)
        <x-public.section variant="sunken" spacing="follow">
            <x-public.container>
                <div class="max-w-3xl py-8 lg:py-10">
                    <p class="text-lg leading-relaxed text-ink-muted">{{ $service->full_description }}</p>
                </div>
            </x-public.container>
        </x-public.section>
    @endif

    <x-public.section>
        <x-public.container>
            <div>
                <h2 class="ng-display text-2xl">What is included</h2>
                @if ($service->inclusions->isNotEmpty())
                    <ul class="mt-5 grid gap-x-8 gap-y-2.5 sm:grid-cols-2">
                        @foreach ($service->inclusions as $inclusion)
                            <li class="flex gap-3 text-sm leading-relaxed text-ink">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="mt-0.5 size-5 shrink-0 text-brand-600" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                {{ $inclusion->content }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-5 text-sm text-ink-muted">Ask us what is included for your property when you request an estimate.</p>
                @endif
            </div>

            <div class="mt-12">
                <h2 class="ng-display text-2xl">What is not included</h2>
                @if ($service->exclusions->isNotEmpty())
                    <x-public.exclusion-list :exclusions="$service->exclusions" class="mt-8" />
                @else
                    <p class="mt-8 text-sm text-ink-muted">We will confirm anything outside the standard checklist before we start.</p>
                @endif
            </div>
        </x-public.container>
    </x-public.section>

    @if ($service->addons->isNotEmpty())
        <x-public.section variant="sunken">
            <x-public.container narrow>
                <x-public.heading eyebrow="Optional extras" title="Add-ons priced separately" subtitle="Each extra uses one price from our system. What you see is what we calculate." />
                <ul class="mt-8 space-y-3">
                    @foreach ($service->addons as $addon)
                        <li class="flex flex-wrap items-start justify-between gap-3 rounded-[var(--radius-md)] border border-border bg-surface-card px-4 py-3">
                            <div>
                                <p class="font-semibold text-ink">{{ $addon->label }}</p>
                                @if ($addon->description)
                                    <p class="mt-1 text-sm text-ink-muted">{{ $addon->description }}</p>
                                @endif
                            </div>
                            <p class="font-semibold text-brand-800">{{ $addon->formattedPrice() }}</p>
                        </li>
                    @endforeach
                </ul>
            </x-public.container>
        </x-public.section>
    @endif

    @if ($service->faqs->isNotEmpty())
        <x-public.section>
            <x-public.container narrow>
                <x-public.heading title="Questions about {{ strtolower($service->name) }}" />
                <div class="mt-8">
                    <x-public.faq-list :faqs="$service->faqs" />
                </div>
            </x-public.container>
        </x-public.section>
    @endif

    @if ($service->serviceAreas->isNotEmpty())
        <x-public.section variant="sunken">
            <x-public.container>
                <x-public.heading
                    title="Where we offer {{ strtolower($service->name) }}"
                    subtitle="We stay within Nottingham and nearby districts so we can keep appointments on time."
                />
                <ul class="mt-6 flex flex-wrap gap-2">
                    @foreach ($service->serviceAreas as $area)
                        <li>
                            <a href="{{ route('areas.service', [$area, $service]) }}" class="inline-flex rounded-[var(--radius-pill)] border border-border bg-surface-card px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-50">
                                {{ $area->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-6 text-sm text-ink-muted">
                    <a href="{{ route('areas') }}" class="ng-link">See all areas we cover</a>
                    or
                    <a href="{{ route('services') }}" class="ng-link">browse other services</a>.
                </p>
            </x-public.container>
        </x-public.section>
    @endif

    @if (isset($recentWorks) && $recentWorks->isNotEmpty())
        <x-public.recent-work :items="$recentWorks" />
    @endif

    <x-public.final-cta />
@endsection

@push('scripts')
    @isset($analyticsEvent)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.ngTrack && window.ngTrack(@json($analyticsEvent), @json($analyticsPayload ?? []));
            });
        </script>
    @endisset
@endpush
