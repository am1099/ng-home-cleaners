@php
    $labelClass = 'text-sm font-semibold text-ink';
@endphp

<h2 class="ng-display text-2xl">Rooms and layout</h2>
<p class="mt-2 text-sm text-ink-muted">The first bathroom, kitchen and reception room are included in your starting price.</p>

<div class="mt-6 grid gap-5 sm:grid-cols-2">
    <x-public.form.quantity
        id="bathrooms"
        label="Bathrooms"
        field="bathrooms"
        :value="$bathrooms"
        :min="1"
        :max="6"
    />
    <x-public.form.quantity
        id="wcs"
        label="Separate toilets (WC)"
        field="wcs"
        :value="$wcs"
        :min="0"
        :max="4"
    />
    <x-public.form.quantity
        id="kitchens"
        label="Kitchens"
        field="kitchens"
        :value="$kitchens"
        :min="1"
        :max="3"
    />
    <x-public.form.quantity
        id="receptionRooms"
        label="Reception / living rooms"
        field="receptionRooms"
        :value="$receptionRooms"
        :min="0"
        :max="6"
    />
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
