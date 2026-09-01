@props([
    'tag' => 'div',
    'narrow' => false,
    'wide' => false,
])

<{{ $tag }} {{ $attributes->class([
    'ng-container',
    'max-w-4xl' => $narrow,
    'ng-container-wide' => $wide,
]) }}>
    {{ $slot }}
</{{ $tag }}>
