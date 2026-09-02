@component('mail::message')
# {{ $heading }}

{!! $bodyHtml !!}

@if (filled($settings->google_business_url))
@component('mail::button', ['url' => $settings->google_business_url])
Leave a Google review
@endcomponent
@endif
@endcomponent
