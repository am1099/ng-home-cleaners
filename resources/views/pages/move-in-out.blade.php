@extends('layouts.public')

@section('content')
    <x-public.page-hero
        eyebrow="Moving"
        title="Move-in and move-out cleaning"
        subtitle="End of tenancy and empty-property cleans priced from the same engine as the rest of the site. Send the details, then a walkthrough video on WhatsApp, and we confirm a fixed price in writing."
    >
        <div class="flex flex-wrap gap-3">
            <x-public.estimate-cta
                label="Get a moving quote"
                size="lg"
                location="move_in_out_hero"
                :href="route('quote', ['service' => 'end-of-tenancy'])"
            />
            <x-public.button
                href="{{ $settings->phoneTel() }}"
                variant="phone"
                size="lg"
                onclick="window.ngTrack && window.ngTrack('phone_clicked', { location: 'move_in_out_hero' })"
            >
                {{ $settings->phoneDisplay() }}
            </x-public.button>
        </div>
    </x-public.page-hero>

    <x-public.section spacing="follow">
        <x-public.container>
            <div class="grid gap-6 lg:grid-cols-2">
                <x-public.card>
                    <h2 class="ng-display text-2xl text-ink">Leaving a rental</h2>
                    <p class="mt-3 text-sm leading-relaxed text-ink-muted">
                        End of tenancy work is aimed at inventory standard: kitchen, bathrooms, floors, and the rooms that usually cost a deposit. Photos on the estimate form help; a WhatsApp walkthrough tightens the quote.
                    </p>
                    @if ($endOfTenancy)
                        <div class="mt-5">
                            <x-public.button href="{{ route('services.show', $endOfTenancy) }}" variant="outline">
                                {{ $endOfTenancy->name }}
                            </x-public.button>
                        </div>
                    @endif
                </x-public.card>
                <x-public.card>
                    <h2 class="ng-display text-2xl text-ink">Moving into an empty home</h2>
                    <p class="mt-3 text-sm leading-relaxed text-ink-muted">
                        A deep clean before the boxes arrive is often the calmer option. The guide price uses the same room counts and condition flags as every other quote on this site.
                    </p>
                    @if ($deep)
                        <div class="mt-5">
                            <x-public.button href="{{ route('services.show', $deep) }}" variant="outline">
                                {{ $deep->name }}
                            </x-public.button>
                        </div>
                    @endif
                </x-public.card>
            </div>
        </x-public.container>
    </x-public.section>

    <x-public.final-cta title="Get a moving estimate" />
@endsection
