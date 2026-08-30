@php
    $inputClass = 'mt-1 block w-full rounded-[var(--radius-md)] border border-border bg-surface-card px-3 py-2.5 text-sm text-ink shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200';
    $labelClass = 'text-sm font-semibold text-ink';
@endphp

<h2 class="ng-display text-2xl">Preferred visit</h2>
<p class="mt-2 text-sm text-ink-muted">Tell us when would suit you. We will confirm availability before anything is booked.</p>

<div class="mt-6">
    <label class="{{ $labelClass }}" for="preferredDate">Preferred date</label>
    <input id="preferredDate" type="date" wire:model="preferredDate" min="{{ now()->toDateString() }}" class="{{ $inputClass }}">
    @error('preferredDate') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
</div>

<div class="mt-6">
    <p class="{{ $labelClass }}">Preferred arrival</p>
    <div class="mt-3 space-y-2">
        @foreach (\App\Enums\ArrivalWindow::cases() as $window)
            <label class="flex cursor-pointer items-center gap-3 rounded-[var(--radius-md)] border border-border px-3 py-3 text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                <input type="radio" name="arrivalWindow" wire:model="arrivalWindow" value="{{ $window->value }}" class="accent-brand-600">
                {{ $window->label() }}
            </label>
        @endforeach
    </div>
    @error('arrivalWindow') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
</div>

<x-public.alert variant="info" class="mt-6">
    This is a preference, not live booking. NG Home Cleaners will confirm the visit time with you before the clean is booked.
</x-public.alert>
