@component('mail::message')
# Thank you for having us

Hello {{ $booking->customer?->first_name ?? 'there' }},

Your booking **{{ $booking->reference }}** is complete. If the clean was up to standard, a short Google review helps other Nottingham households find us.

@if (filled($settings->google_business_url))
@component('mail::button', ['url' => $settings->google_business_url])
Leave a Google review
@endcomponent
@endif

Thank you for choosing {{ $settings->business_name ?? config('app.name') }}.

Thanks,<br>
{{ $settings->business_name ?? config('app.name') }}
@endcomponent
