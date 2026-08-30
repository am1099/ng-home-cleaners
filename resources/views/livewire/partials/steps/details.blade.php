@php
    $inputClass = 'mt-1 block w-full rounded-[var(--radius-md)] border border-border bg-surface-card px-3 py-2.5 text-sm text-ink shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200 aria-[invalid=true]:border-red-500 aria-[invalid=true]:ring-red-200';
    $labelClass = 'text-sm font-semibold text-ink';
@endphp

<h2 class="ng-display text-2xl">Your details</h2>
<p class="mt-2 text-sm text-ink-muted">We will use these details to send your fixed price in writing.</p>

<div class="mt-6 grid gap-5 sm:grid-cols-2">
    <div>
        <label class="{{ $labelClass }}" for="firstName">First name</label>
        <input id="firstName" type="text" wire:model="firstName" autocomplete="given-name" class="{{ $inputClass }}" @error('firstName') aria-invalid="true" aria-describedby="firstName-error" @enderror>
        @error('firstName') <p id="firstName-error" class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $labelClass }}" for="lastName">Last name</label>
        <input id="lastName" type="text" wire:model="lastName" autocomplete="family-name" class="{{ $inputClass }}" @error('lastName') aria-invalid="true" aria-describedby="lastName-error" @enderror>
        @error('lastName') <p id="lastName-error" class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $labelClass }}" for="phone">Phone</label>
        <input id="phone" type="tel" wire:model="phone" autocomplete="tel" inputmode="tel" class="{{ $inputClass }}" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
        @error('phone') <p id="phone-error" class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $labelClass }}" for="email">Email</label>
        <input id="email" type="email" wire:model="email" autocomplete="email" class="{{ $inputClass }}" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
        @error('email') <p id="email-error" class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 grid gap-5 sm:grid-cols-[140px_1fr]">
    <div>
        <label class="{{ $labelClass }}" for="postcode">Postcode</label>
        <input id="postcode" type="text" wire:model="postcode" autocomplete="postal-code" class="{{ $inputClass }}" placeholder="NG1 1AA" @error('postcode') aria-invalid="true" aria-describedby="postcode-error" @enderror>
        @error('postcode') <p id="postcode-error" class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $labelClass }}" for="addressLine1">Address line 1</label>
        <input id="addressLine1" type="text" wire:model="addressLine1" autocomplete="address-line1" class="{{ $inputClass }}" @error('addressLine1') aria-invalid="true" aria-describedby="addressLine1-error" @enderror>
        @error('addressLine1') <p id="addressLine1-error" class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-5 grid gap-5 sm:grid-cols-2">
    <div>
        <label class="{{ $labelClass }}" for="addressLine2">Address line 2 <span class="font-normal text-ink-muted">(optional)</span></label>
        <input id="addressLine2" type="text" wire:model="addressLine2" autocomplete="address-line2" class="{{ $inputClass }}">
    </div>
    <div>
        <label class="{{ $labelClass }}" for="city">City / town</label>
        <input id="city" type="text" wire:model="city" autocomplete="address-level2" class="{{ $inputClass }}" @error('city') aria-invalid="true" aria-describedby="city-error" @enderror>
        @error('city') <p id="city-error" class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 grid gap-5 sm:grid-cols-2">
    <div>
        <label class="{{ $labelClass }}" for="parkingNotes">Parking notes <span class="font-normal text-ink-muted">(optional)</span></label>
        <input id="parkingNotes" type="text" wire:model="parkingNotes" class="{{ $inputClass }}" placeholder="e.g. permit zone, driveway, on-street">
    </div>
    <div>
        <label class="{{ $labelClass }}" for="accessNotes">Access notes <span class="font-normal text-ink-muted">(optional)</span></label>
        <input id="accessNotes" type="text" wire:model="accessNotes" class="{{ $inputClass }}" placeholder="e.g. key safe, concierge, alarm code">
    </div>
</div>
