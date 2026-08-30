@if ($settings->show_google_reviews || $settings->show_insurance_statement || $settings->show_dbs_statement)
    <div {{ $attributes->class(['flex flex-wrap gap-2']) }}>
        @if ($settings->show_google_reviews)
            <x-public.trust-badge icon="google">Five-star Google reviews</x-public.trust-badge>
        @endif
        @if ($settings->show_insurance_statement && $settings->insurance_amount)
            <x-public.trust-badge icon="shield">{{ $settings->insurance_amount }} public liability</x-public.trust-badge>
        @endif
        @if ($settings->show_dbs_statement)
            <x-public.trust-badge icon="check">DBS-checked cleaners</x-public.trust-badge>
        @endif
    </div>
@endif
