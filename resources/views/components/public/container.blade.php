@props([
    'tag' => 'div',
    'narrow' => false,
])

<{{ $tag }} {{ $attributes->class([
    'ng-container',
    'max-w-4xl' => $narrow,
]) }}>
    {{ $slot }}
</{{ $tag }}>
