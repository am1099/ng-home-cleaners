@php
    $inputClass = 'mt-1 block w-full rounded-[var(--radius-md)] border border-border bg-surface-card px-3 py-2.5 text-sm text-ink shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200';
    $labelClass = 'text-sm font-semibold text-ink';
@endphp

<div
    x-data="{
        propertyType: @js($propertyType),
        splitLevel: {{ $splitLevelFlat ? 'true' : 'false' }},
    }"
>
    <h2 class="ng-display text-2xl">About the property</h2>
    <p class="mt-2 text-sm text-ink-muted">This helps us match the right starting price.</p>

    <div class="mt-6">
        <p class="{{ $labelClass }}">Property type</p>
        <div class="mt-3 grid gap-2 sm:grid-cols-3">
            @foreach (\App\Enums\PropertyType::cases() as $type)
                <label class="flex cursor-pointer items-center justify-center rounded-[var(--radius-md)] border border-border px-3 py-3 text-sm font-semibold text-ink has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60 has-[:checked]:text-brand-900">
                    <input
                        type="radio"
                        name="propertyType"
                        wire:model.live="propertyType"
                        wire:island="estimate-summary"
                        value="{{ $type->value }}"
                        class="sr-only"
                        x-on:change="propertyType = @js($type->value); splitLevel = false"
                    >
                    {{ $type->label() }}
                </label>
            @endforeach
        </div>
        @error('propertyType') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div class="mt-6">
        <label class="{{ $labelClass }}" for="bedrooms">Bedrooms</label>
        <select
            id="bedrooms"
            wire:model.live="bedrooms"
            wire:island="estimate-summary"
            class="{{ $inputClass }}"
        >
            <option value="0">Studio</option>
            @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}">{{ $i }}{{ $i === 5 ? '+' : '' }}</option>
            @endfor
        </select>
        @error('bedrooms') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div class="mt-6" x-show="propertyType === 'flat'" x-cloak>
        <label class="flex cursor-pointer items-start gap-3 rounded-[var(--radius-md)] border border-border p-4">
            <input
                type="checkbox"
                wire:model.live="splitLevelFlat"
                wire:island="estimate-summary"
                class="mt-1 accent-brand-600"
                x-on:change="splitLevel = $event.target.checked"
            >
            <span>
                <span class="font-semibold text-ink">Split-level flat / maisonette</span>
                <span class="mt-1 block text-sm text-ink-muted">Tick this if your flat has more than one level.</span>
            </span>
        </label>
    </div>

    <div class="mt-6" x-show="propertyType !== 'flat' || splitLevel" x-cloak>
        <label class="{{ $labelClass }}" for="floors">Number of floors</label>
        <select
            id="floors"
            wire:model.live="floors"
            wire:island="estimate-summary"
            class="{{ $inputClass }}"
        >
            @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}">{{ $i }}</option>
            @endfor
        </select>
        @error('floors') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
</div>
