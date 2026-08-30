@props(['exclusions'])

@php
    $items = $exclusions instanceof \Illuminate\Support\Collection
        ? $exclusions->values()
        : collect($exclusions)->values();

    $left = $items->filter(fn ($item, $index) => $index % 2 === 0)->values();
    $right = $items->filter(fn ($item, $index) => $index % 2 === 1)->values();
@endphp

@if ($items->isNotEmpty())
    {{-- Mobile: single column in numerical order --}}
    <div {{ $attributes->class(['flex flex-col gap-3 md:hidden']) }}>
        @foreach ($items as $exclusion)
            <x-public.exclusion-item :exclusion="$exclusion" :number="$loop->iteration" />
        @endforeach
    </div>

    {{-- Desktop: two independent flex stacks — shared CSS grid rows cannot stretch neighbours --}}
    <div class="hidden gap-3 md:grid md:grid-cols-2 md:items-start">
        <div class="flex flex-col gap-3">
            @foreach ($left as $exclusion)
                <x-public.exclusion-item :exclusion="$exclusion" :number="($loop->index * 2) + 1" />
            @endforeach
        </div>
        <div class="flex flex-col gap-3">
            @foreach ($right as $exclusion)
                <x-public.exclusion-item :exclusion="$exclusion" :number="($loop->index * 2) + 2" />
            @endforeach
        </div>
    </div>
@endif
