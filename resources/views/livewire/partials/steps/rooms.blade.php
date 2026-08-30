@php
    $inputClass = 'mt-1 block w-full rounded-[var(--radius-md)] border border-border bg-surface-card px-3 py-2.5 text-sm text-ink shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200';
    $labelClass = 'text-sm font-semibold text-ink';
@endphp

<h2 class="ng-display text-2xl">Rooms and layout</h2>
<p class="mt-2 text-sm text-ink-muted">The first bathroom, kitchen and reception room are included in your starting price.</p>

<div class="mt-6 grid gap-5 sm:grid-cols-2">
    <div>
        <label class="{{ $labelClass }}" for="bathrooms">Bathrooms</label>
        <input id="bathrooms" type="number" min="1" max="6" wire:model.live.debounce.300ms="bathrooms" wire:island="estimate-summary" class="{{ $inputClass }}">
        @error('bathrooms') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $labelClass }}" for="wcs">Separate toilets (WC)</label>
        <input id="wcs" type="number" min="0" max="4" wire:model.live.debounce.300ms="wcs" wire:island="estimate-summary" class="{{ $inputClass }}">
        @error('wcs') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $labelClass }}" for="kitchens">Kitchens</label>
        <input id="kitchens" type="number" min="1" max="3" wire:model.live.debounce.300ms="kitchens" wire:island="estimate-summary" class="{{ $inputClass }}">
        @error('kitchens') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $labelClass }}" for="receptionRooms">Reception / living rooms</label>
        <input id="receptionRooms" type="number" min="0" max="6" wire:model.live.debounce.300ms="receptionRooms" wire:island="estimate-summary" class="{{ $inputClass }}">
        @error('receptionRooms') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6">
    <p class="{{ $labelClass }}">Optional extra rooms</p>
    <div class="mt-3 grid gap-2 sm:grid-cols-2">
        @foreach (\App\Enums\ExtraRoomType::cases() as $room)
            <label class="flex cursor-pointer items-center gap-3 rounded-[var(--radius-md)] border border-border px-3 py-3 text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                <input type="checkbox" wire:model.live="extraRooms" wire:island="estimate-summary" value="{{ $room->value }}" class="accent-brand-600">
                {{ $room->label() }}
            </label>
        @endforeach
    </div>
</div>
