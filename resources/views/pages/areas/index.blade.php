@extends('layouts.public')

@section('content')
    <x-public.page-hero
        eyebrow="Areas we cover"
        title="Nottingham only, and we intend to keep it that way."
        subtitle="We cover NG1 to NG16 and nowhere else. Staying local means we hold the slot we give you rather than running late from the other side of the county."
    />

    <x-public.section spacing="follow">
        <x-public.container>
            <h2 class="ng-display ng-display-section mb-6 text-ink">District by district</h2>

            @if ($areas->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($areas as $area)
                        <x-public.area-card :area="$area" />
                    @endforeach
                </div>
            @else
                <x-public.empty-state title="No areas published" message="Service area pages will appear here once they are active in the admin." />
            @endif
        </x-public.container>
    </x-public.section>

    <x-public.section>
        <x-public.container>
            <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h2 class="ng-display ng-display-section text-ink">Just outside the line?</h2>
                    <p class="mt-3.5 max-w-md text-base leading-relaxed text-ink-muted">
                        NG17 and beyond usually means a longer drive than we can fit around the day's other jobs. Send the postcode anyway. If we can make it work, we will say so, and if we cannot we will point you to someone who can.
                    </p>
                </div>
                <ul class="space-y-3.5">
                    @foreach ([
                        'Travel inside NG1 to NG16 is included in the price, never added on.',
                        'Weekday and Saturday slots across the whole coverage area.',
                        'The same standard and checklist wherever you are in the coverage area.',
                        'Not sure of your district? Send the full postcode and we will confirm.',
                    ] as $fact)
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 size-[18px] shrink-0 text-brand-500" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/>
                            </svg>
                            <span class="text-base leading-relaxed text-ink-muted">{{ $fact }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <p class="mt-8">
                <a href="{{ route('services') }}" class="ng-link">Browse cleaning services</a>
            </p>
        </x-public.container>
    </x-public.section>

    <x-public.final-cta title="Check your postcode" subtitle="Tell us where you are and we will confirm we cover you, then match you with the right cleaner." />
@endsection
