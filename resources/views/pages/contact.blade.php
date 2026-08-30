@extends('layouts.public')

@section('content')
    <x-public.page-hero
        eyebrow="Contact"
        title="Would rather just ask someone?"
        subtitle="Call or message us and you will get a straight answer, not a sales call."
    />

    <x-public.section spacing="follow">
        <x-public.container>
            <div class="grid gap-5 md:grid-cols-3">
                <x-public.card class="flex flex-col">
                    <h2 class="font-semibold text-ink">Phone</h2>
                    <p class="mt-3">
                        <a href="{{ $settings->phoneTel() }}" class="ng-link text-lg">{{ $settings->phoneDisplay() }}</a>
                    </p>
                    <p class="mt-2 text-sm text-ink-muted">{{ $settings->hoursSummary() }}</p>
                </x-public.card>
                <x-public.card class="flex flex-col">
                    <h2 class="font-semibold text-ink">Email</h2>
                    <p class="mt-3">
                        <a href="mailto:{{ $settings->email }}" class="ng-link">{{ $settings->email }}</a>
                    </p>
                </x-public.card>
                <x-public.card class="flex flex-col">
                    <h2 class="font-semibold text-ink">WhatsApp</h2>
                    <p class="mt-3 flex-1 text-sm text-ink-muted">Send a walkthrough video and we can usually tighten your guide price.</p>
                    <div class="mt-4">
                        <x-public.whatsapp-cta />
                    </div>
                </x-public.card>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <x-public.estimate-cta label="Get a free estimate" size="lg" />
                <x-public.button href="{{ route('services') }}" variant="outline" size="lg">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M6 3.75A2.75 2.75 0 0 1 8.75 1h2.5A2.75 2.75 0 0 1 14 3.75v.443c.572.055 1.14.122 1.706.2C17.053 4.582 18 5.75 18 7.07v3.469c0 1.126-.694 2.191-1.83 2.54-1.952.599-4.024.921-6.17.921s-4.219-.322-6.17-.921C2.694 12.73 2 11.665 2 10.539V7.07c0-1.321.947-2.489 2.294-2.676A41.047 41.047 0 0 1 6 4.193V3.75Zm6.5 0v.325a41.622 41.622 0 0 0-5 0V3.75c0-.69.56-1.25 1.25-1.25h2.5c.69 0 1.25.56 1.25 1.25ZM10 10a1 1 0 0 0-1 1v.01a1 1 0 0 0 1 1h.01a1 1 0 0 0 1-1V11a1 1 0 0 0-1-1H10Z" clip-rule="evenodd"/><path d="M3 15.078a7.984 7.984 0 0 0 4.11 2.179 31.053 31.053 0 0 0 3.78.362 31.053 31.053 0 0 0 3.78-.362 7.984 7.984 0 0 0 4.11-2.18.75.75 0 0 0-.163-1.23l-.006-.002a8.31 8.31 0 0 0-.808-.344 31.152 31.152 0 0 0-6.913-.662 31.152 31.152 0 0 0-6.913.662 8.31 8.31 0 0 0-.808.344l-.006.002a.75.75 0 0 0-.163 1.23Z"/></svg>
                    Browse services
                </x-public.button>
                <x-public.button href="{{ route('areas') }}" variant="outline" size="lg">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 0 0 .281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 1 0 3 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 0 0 2.273 1.765 11.842 11.842 0 0 0 .976.544l.02.009.008.003ZM10 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd"/></svg>
                    Check your area
                </x-public.button>
            </div>

            @if ($settings->business_address)
                <p class="mt-6 text-sm text-ink-muted">{{ $settings->business_address }}</p>
            @endif
        </x-public.container>
    </x-public.section>
@endsection
