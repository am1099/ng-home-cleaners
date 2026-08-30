@props([
    'title' => 'Book a clean you will not have to chase',
    'subtitle' => null,
])

<x-public.section variant="ink">
    <x-public.container class="text-center">
        <x-public.heading
            :title="$title"
            :subtitle="$subtitle ?? 'Tell us about your home and we will match you with the right cleaner. Everything in writing first, and nothing happens until you say yes.'"
            align="center"
            class="mx-auto text-ink-inverse [&_.ng-body-lg]:text-ink-inverse-muted"
        />
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <x-public.estimate-cta label="Get a free estimate" variant="inverse" size="lg" />
            <x-public.whatsapp-cta size="lg" />
        </div>
        <p class="mx-auto mt-6 max-w-xl text-sm text-ink-inverse-muted">
            Or call <a href="{{ $settings->phoneTel() }}" class="font-semibold text-ink-inverse hover:text-brand-100">{{ $settings->phoneDisplay() }}</a>
            @if ($settings->hoursSummary())
                · {{ $settings->hoursSummary() }}
            @endif
        </p>
    </x-public.container>
</x-public.section>
