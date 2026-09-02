@php
    $settings = \App\Models\SiteSetting::instance();
    $businessName = $settings->business_name ?: config('app.name');
    $logoUrl = \App\Support\Media::url($settings->logo_path);
@endphp
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
@if (filled($logoUrl))
<img src="{{ $logoUrl }}" alt="{{ $businessName }}" class="logo" style="height: 48px; width: auto; max-width: 220px; max-height: 48px;">
@else
{{ $businessName }}
@endif
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ $businessName }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
