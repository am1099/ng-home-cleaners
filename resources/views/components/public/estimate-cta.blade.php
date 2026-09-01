@props([
    'label' => 'Get a free estimate',
    'size' => 'md',
    'variant' => 'primary',
    'location' => 'cta',
    'href' => null,
])

<x-public.button
    :href="$href ?: route('quote')"
    :variant="$variant"
    :size="$size"
    onclick="window.ngTrack && window.ngTrack('quote_started', { location: @js($location) })"
    {{ $attributes }}
>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true">
        <path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm4.75 6.75a.75.75 0 0 0 0 1.5h2.5a.75.75 0 0 0 0-1.5h-2.5Zm0 3a.75.75 0 0 0 0 1.5h2.5a.75.75 0 0 0 0-1.5h-2.5Z" clip-rule="evenodd"/>
    </svg>
    {{ $label }}
</x-public.button>
