@php
    $inputClass = 'mt-1 block w-full rounded-[var(--radius-md)] border border-border bg-surface-card px-3 py-2.5 text-sm text-ink shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200';
    $labelClass = 'text-sm font-semibold text-ink';
@endphp

<h2 class="ng-display text-2xl">Condition and access</h2>
<p class="mt-2 text-sm text-ink-muted">Tick anything that applies so we can quote accurately.</p>

@if ($this->selectedService?->appliesPropertyStatusMultipliers())
    <div class="mt-6">
        <p class="{{ $labelClass }}">Is the property empty or furnished?</p>
        <div class="mt-3 grid gap-2 sm:grid-cols-3">
            @foreach (\App\Enums\PropertyStatus::cases() as $status)
                <label class="flex cursor-pointer items-center justify-center rounded-[var(--radius-md)] border border-border px-3 py-3 text-sm font-semibold text-center has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                    <input
                        type="radio"
                        name="propertyStatus"
                        wire:model.live="propertyStatus"
                        wire:island="estimate-summary"
                        value="{{ $status->value }}"
                        class="sr-only"
                    >
                    {{ $status->label() }}
                </label>
            @endforeach
        </div>
        @error('propertyStatus') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
@endif

<div class="mt-6">
    <p class="{{ $labelClass }}">Condition flags</p>
    <div class="mt-3 space-y-2">
        @foreach (\App\Enums\ConditionFlag::cases() as $flag)
            <label class="flex cursor-pointer items-start gap-3 rounded-[var(--radius-md)] border border-border px-3 py-3 text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                <input type="checkbox" wire:model.live="conditionFlags" wire:island="estimate-summary" value="{{ $flag->value }}" class="mt-0.5 accent-brand-600">
                {{ $flag->label() }}
            </label>
        @endforeach
    </div>
</div>

<div class="mt-6">
    <label class="{{ $labelClass }}" for="conditionNotes">Anything else we should know?</label>
    <textarea
        id="conditionNotes"
        wire:model="conditionNotes"
        rows="4"
        class="{{ $inputClass }}"
        placeholder="Optional notes about access, pets, parking or areas that need extra attention."
    ></textarea>
    @error('conditionNotes') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
</div>
