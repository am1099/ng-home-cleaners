@php
    $labelClass = 'text-sm font-semibold text-ink';
@endphp

<div>
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
                    >
                    {{ $type->label() }}
                </label>
            @endforeach
        </div>
        @error('propertyType') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    @if ($propertyType === \App\Enums\PropertyType::Flat->value)
    <div class="mt-6">
        <label class="flex cursor-pointer items-start gap-3 rounded-[var(--radius-md)] border border-border p-4">
            <input
                type="checkbox"
                wire:model.live="splitLevelFlat"
                wire:island="estimate-summary"
                class="mt-1 accent-brand-600"
            >
            <span>
                <span class="font-semibold text-ink">Split-level flat / maisonette</span>
                <span class="mt-1 block text-sm text-ink-muted">Tick this if your flat has more than one level.</span>
            </span>
        </label>
    </div>
    @endif

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-public.form.quantity
            id="bedrooms"
            label="Bedrooms"
            field="bedrooms"
            :value="$bedrooms"
            :min="0"
            :max="5"
            :display="$bedrooms === 0 ? 'Studio' : ($bedrooms >= 5 ? '5+' : (string) $bedrooms)"
        />

        @if ($propertyType !== \App\Enums\PropertyType::Flat->value || $splitLevelFlat)
        <x-public.form.quantity
            id="floors"
            label="Number of floors"
            field="floors"
            :value="$floors"
            :min="1"
            :max="5"
        />
        @endif
    </div>
</div>
