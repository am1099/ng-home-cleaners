@props([
    'error' => false,
])

<textarea {{ $attributes->class([
    'ng-field min-h-28',
    'border-red-400' => $error,
]) }}>{{ $slot }}</textarea>
