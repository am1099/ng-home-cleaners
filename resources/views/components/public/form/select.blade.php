@props([
    'error' => false,
])

<select {{ $attributes->class([
    'ng-field',
    'border-red-400' => $error,
]) }}>
    {{ $slot }}
</select>
