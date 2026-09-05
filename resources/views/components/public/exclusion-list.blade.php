@props(['exclusions'])

@php
    $items = $exclusions instanceof \Illuminate\Support\Collection
        ? $exclusions->values()
        : collect($exclusions)->values();
@endphp

@if ($items->isNotEmpty())
    <div {{ $attributes->class(['ng-exclusion-list columns-1 md:columns-2 md:gap-x-6']) }}>
        @foreach ($items as $exclusion)
            <div class="mb-3 break-inside-avoid">
                <x-public.exclusion-item :exclusion="$exclusion" :number="$loop->iteration" />
            </div>
        @endforeach
    </div>
@endif
