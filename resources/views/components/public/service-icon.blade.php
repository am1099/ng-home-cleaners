@props(['icon'])

@php
    use App\Enums\ServiceIcon;
    $resolved = $icon instanceof ServiceIcon ? $icon : ServiceIcon::tryFrom((string) $icon) ?? ServiceIcon::House;
@endphp

<span {{ $attributes->class(['inline-flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-800']) }} aria-hidden="true">
    {!! $resolved->svg('size-6') !!}
</span>
