@component('mail::message')
# Your confirmed quote

Hello {{ $quoteRequest->first_name }},

Thank you for your patience. Here is your confirmed quote from {{ $settings->business_name ?? config('app.name') }}.

**Your reference:** {{ $quoteRequest->reference }}

**Service:** {{ $quoteRequest->service?->name }}

@if ($quoteRequest->preferred_date)
**Preferred visit:** {{ $quoteRequest->preferred_date->format('l j F Y') }}@if ($quoteRequest->arrival_window) ({{ \App\Enums\ArrivalWindow::tryFrom($quoteRequest->arrival_window)?->label() }})@endif
@endif

**Confirmed quote:** {{ $quoteRequest->finalQuoteDisplay() }}

@if ($quoteRequest->guide_estimate_headline)
**Original guide estimate:** {{ $quoteRequest->guide_estimate_headline }}
@endif

This is our fixed price based on the details you provided. Reply to this email or call us on {{ $settings->phoneDisplay() }} if you would like to go ahead or need anything changed.

Thanks,<br>
{{ $settings->business_name ?? config('app.name') }}
@endcomponent
