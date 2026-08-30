@props([
    'items' => [],
])

@if (count($items) >= 2)
    <nav class="border-b border-border/70 bg-surface-page" aria-label="Breadcrumb">
        <x-public.container class="py-3">
            <ol class="flex flex-wrap items-center gap-1.5 text-sm text-ink-muted">
                @foreach ($items as $index => $item)
                    <li class="flex items-center gap-1.5">
                        @if ($index > 0)
                            <span aria-hidden="true" class="text-ink-subtle">/</span>
                        @endif
                        @if ($loop->last)
                            <span class="font-medium text-ink" aria-current="page">{{ $item['name'] }}</span>
                        @else
                            <a href="{{ $item['url'] }}" class="hover:text-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
                                {{ $item['name'] }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </x-public.container>
    </nav>
@endif
