@props([
    'title' => 'Something went wrong',
    'message' => null,
])

<div {{ $attributes->class([
    'rounded-[var(--radius-card)] border border-red-200 bg-red-50 px-6 py-10 text-center',
]) }} role="alert">
    <p class="ng-display text-xl text-red-950">{{ $title }}</p>
    @if ($message)
        <p class="mt-2 text-sm text-red-800">{{ $message }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
