@props([
    'error' => false,
])

<input {{ $attributes->class([
    'ng-field',
    'border-red-400' => $error,
]) }} />
