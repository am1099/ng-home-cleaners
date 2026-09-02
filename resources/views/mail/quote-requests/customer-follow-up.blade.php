@component('mail::message')
# {{ $heading }}

{!! $bodyHtml !!}

@if ($settings->whatsappLink())
@component('mail::button', ['url' => $settings->whatsappLink()])
Message us on WhatsApp
@endcomponent
@endif
@endcomponent
