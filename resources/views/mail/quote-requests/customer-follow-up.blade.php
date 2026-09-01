@component('mail::message')
# We are preparing your quote

Hello {{ $quoteRequest->first_name }},

Just a short note to say we have your estimate request **{{ $quoteRequest->reference }}** and are preparing your fixed price.

We will send that in writing as soon as we have checked the details. WhatsApp is still the quickest way to add a walkthrough video or ask a question in the meantime.

@if ($settings->whatsappLink())
[Message us on WhatsApp]({{ $settings->whatsappLink() }})
@endif

Thanks,<br>
{{ $settings->business_name ?? config('app.name') }}
@endcomponent
