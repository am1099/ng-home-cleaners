@component('mail::message')
# We received your estimate request

Hello {{ $quoteRequest->first_name }},

Thank you for requesting an estimate from {{ $settings->business_name ?? config('app.name') }}.

**Your reference:** {{ $quoteRequest->reference }}

**Service:** {{ $quoteRequest->service?->name }}

**Preferred visit:** {{ $quoteRequest->preferred_date->format('l j F Y') }} ({{ \App\Enums\ArrivalWindow::tryFrom($quoteRequest->arrival_window)?->label() }})

**Guide estimate:** {{ $quoteRequest->guide_estimate_headline }}

This guide price helps you plan ahead. It is not yet your final confirmed quote. We will check your details and send a fixed price in writing within one working day.

If anything needs correcting, reply to this email or call us on {{ $settings->phoneDisplay() }} and quote reference **{{ $quoteRequest->reference }}**.

Thanks,<br>
{{ $settings->business_name ?? config('app.name') }}
@endcomponent
