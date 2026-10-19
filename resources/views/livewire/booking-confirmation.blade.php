@php
    $labelClass = 'text-sm font-semibold text-ink';
    $inputClass = 'ng-field';
    $fieldClass = 'flex flex-col gap-3 sm:gap-3.5';
    $radioGroupClass = 'flex flex-col gap-3 sm:gap-3.5';
    $stackClass = 'flex flex-col gap-8 sm:gap-9';
    $sectionIntroClass = 'flex flex-col gap-2';
@endphp

<div class="bg-surface">
    <x-public.section spacing="hero">
        <x-public.container narrow>
            <div class="max-w-2xl">
                @if ($submitted)
                    <p class="ng-eyebrow">Booking confirmation</p>
                    <h1 class="ng-display mt-3 text-3xl sm:text-4xl">We've got it.</h1>
                    <p class="ng-body-lg mt-4 text-ink-muted">
                        Thank you. We'll WhatsApp you a confirmation shortly.
                    </p>
                    @if ($whatsappHref)
                        <div class="mt-10">
                            <x-public.button href="{{ $whatsappHref }}" variant="whatsapp" target="_blank" rel="noopener noreferrer">
                                Message us on WhatsApp
                            </x-public.button>
                        </div>
                    @endif
                @else
                    <p class="ng-eyebrow">Booking confirmation</p>
                    <h1 class="ng-display mt-3 text-3xl sm:text-4xl">Almost there.</h1>
                    <p class="ng-body-lg mt-4 text-ink-muted">
                        Takes a minute. A few details so we turn up ready to go.
                    </p>

                    <x-public.alert variant="info" title="Payment" class="mt-8">
                        Your booking is only confirmed once payment has been received.
                    </x-public.alert>

                    @error('submit')
                        <x-public.alert variant="error" title="Could not send confirmation" class="mt-6" role="alert">
                            {{ $message }}
                        </x-public.alert>
                    @enderror

                    <form
                        wire:submit="submit"
                        class="ng-booking-confirm-form mt-10 rounded-[var(--radius-card)] border border-border bg-surface-card p-8 shadow-[var(--shadow-card)] sm:p-10 lg:p-12"
                    >
                        <div class="hidden" aria-hidden="true">
                            <label for="website">Leave this blank</label>
                            <input id="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="{{ $stackClass }}">
                            <div class="{{ $sectionIntroClass }}">
                                <h2 class="ng-display text-lg text-ink">Who and where</h2>
                                <p class="text-sm leading-relaxed text-ink-muted">The name and address we should have on file for this visit.</p>
                            </div>

                            <div class="{{ $fieldClass }}">
                                <label class="{{ $labelClass }}" for="fullName">Full name</label>
                                <input id="fullName" type="text" wire:model="fullName" required autocomplete="name" class="{{ $inputClass }}" placeholder="Jane Smith">
                                @error('fullName') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>

                            <div class="{{ $fieldClass }}">
                                <label class="{{ $labelClass }}" for="address">Full address and postcode</label>
                                <textarea id="address" wire:model="address" required rows="4" class="{{ $inputClass }} min-h-[7.5rem]" placeholder="42 Example Road, Nottingham, NG1 1AA"></textarea>
                                @error('address') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>

                            <div class="{{ $sectionIntroClass }}">
                                <h2 class="ng-display text-lg text-ink">Access on the day</h2>
                                <p class="text-sm leading-relaxed text-ink-muted">Let us know who will be home, or where to find a key. Send any key-safe or alarm codes on WhatsApp rather than in this form if you can.</p>
                            </div>

                            <div class="{{ $fieldClass }}">
                                <label class="{{ $labelClass }}" for="accessInstructions">Access instructions</label>
                                <textarea id="accessInstructions" wire:model="accessInstructions" required rows="4" class="{{ $inputClass }} min-h-[7.5rem]" placeholder="I'll be home to let you in"></textarea>
                                @error('accessInstructions') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>

                            <div class="{{ $sectionIntroClass }}">
                                <h2 class="ng-display text-lg text-ink">Parking and equipment</h2>
                                <p class="text-sm leading-relaxed text-ink-muted">We bring cleaning products as standard. This section is only about parking and whether you have a hoover and mop on site.</p>
                            </div>

                            <fieldset class="{{ $fieldClass }} min-w-0">
                                <legend class="{{ $labelClass }}">Is there parking on site?</legend>
                                <div class="{{ $radioGroupClass }}">
                                    @foreach (\App\Enums\BookingConfirmationParking::cases() as $option)
                                        <x-public.form.radio
                                            :label="$option->label()"
                                            name="parking"
                                            :value="$option->value"
                                            wire:model="parking"
                                            wire:key="parking-{{ $option->value }}"
                                        />
                                    @endforeach
                                </div>
                                @error('parking') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
                            </fieldset>

                            <fieldset class="{{ $fieldClass }} min-w-0">
                                <legend class="{{ $labelClass }}">Hoover and mop</legend>
                                <div class="{{ $radioGroupClass }}">
                                    @foreach (\App\Enums\BookingConfirmationEquipment::cases() as $option)
                                        <x-public.form.radio
                                            :label="$option->label()"
                                            name="equipmentPreference"
                                            :value="$option->value"
                                            wire:model="equipmentPreference"
                                            wire:key="equipment-{{ $option->value }}"
                                        />
                                    @endforeach
                                </div>
                                @error('equipmentPreference') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
                            </fieldset>

                            <div class="{{ $sectionIntroClass }}">
                                <h2 class="ng-display text-lg text-ink">Anything else?</h2>
                                <p class="text-sm leading-relaxed text-ink-muted">Optional — pets, fragile items, or areas you want extra attention on.</p>
                            </div>

                            <div class="{{ $fieldClass }}">
                                <label class="{{ $labelClass }}" for="notes">Notes <span class="font-normal text-ink-muted">(optional)</span></label>
                                <textarea id="notes" wire:model="notes" rows="4" class="{{ $inputClass }} min-h-[6.5rem]" placeholder="Pets, fragile items, areas to focus on"></textarea>
                                @error('notes') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>

                            <div class="{{ $sectionIntroClass }}">
                                <h2 class="ng-display text-lg text-ink">Before you confirm</h2>
                                <p class="text-sm leading-relaxed text-ink-muted">Both statements must be ticked to send this form.</p>
                            </div>

                            <div class="flex flex-col gap-4 sm:gap-5">
                                <label class="flex cursor-pointer items-start gap-4 rounded-[var(--radius-md)] border border-border bg-surface-sunken/40 p-5 sm:p-6">
                                    <input type="checkbox" wire:model="valuablesSecured" class="mt-0.5 size-4 shrink-0 accent-brand-600" required>
                                    <span class="text-sm leading-relaxed text-ink">I confirm all valuable and fragile items have been secured before the clean.</span>
                                </label>
                                @error('valuablesSecured') <p class="text-sm text-red-700">{{ $message }}</p> @enderror

                                <label class="flex cursor-pointer items-start gap-4 rounded-[var(--radius-md)] border border-border bg-surface-sunken/40 p-5 sm:p-6">
                                    <input type="checkbox" wire:model="paymentSent" class="mt-0.5 size-4 shrink-0 accent-brand-600" required>
                                    <span class="text-sm leading-relaxed text-ink">I have sent payment as agreed (in full by card, or the deposit we arranged).</span>
                                </label>
                                @error('paymentSent') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-8 sm:mt-9">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex min-h-12 w-full cursor-pointer items-center justify-center rounded-[var(--radius-pill)] bg-brand-600 px-8 text-sm font-semibold text-white shadow-md shadow-brand-700/25 transition hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:cursor-wait disabled:opacity-50 sm:w-auto"
                            >
                                <span wire:loading.remove wire:target="submit">Confirm booking</span>
                                <span wire:loading wire:target="submit">Sending…</span>
                            </button>
                            <p class="mt-4 text-sm text-ink-muted">We'll WhatsApp you a confirmation shortly after this is received.</p>
                        </div>
                    </form>

                    @if ($whatsappHref)
                        <p class="mt-8 text-sm text-ink-muted">
                            Need to change something? <a href="{{ $whatsappHref }}" class="font-semibold text-brand-800 underline" target="_blank" rel="noopener noreferrer">Message us on WhatsApp</a>.
                        </p>
                    @endif
                @endif
            </div>
        </x-public.container>
    </x-public.section>
</div>
