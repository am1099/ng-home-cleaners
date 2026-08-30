@php
    $inputClass = 'mt-1 block w-full rounded-[var(--radius-md)] border border-border bg-surface-card px-3 py-2.5 text-sm text-ink shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200 aria-[invalid=true]:border-red-500';
    $labelClass = 'text-sm font-semibold text-ink';
    $chipClass = 'flex cursor-pointer items-center justify-center rounded-[var(--radius-md)] border border-border px-3 py-2.5 text-sm font-semibold text-ink transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/70 has-[:checked]:text-brand-900';
    $regularServiceId = $this->services->first(fn ($service) => $service->isRegularClean())?->id;
    $commercialServiceId = $this->services->first(fn ($service) => $service->requiresManualQuote())?->id;
    $deepLikeIds = $this->services
        ->filter(fn ($service) => $service->appliesPropertyStatusMultipliers())
        ->pluck('id')
        ->values()
        ->all();
@endphp

<div
    class="ng-estimate-form"
    x-data="{
        serviceId: {{ (int) $serviceId }},
        propertyType: @js($propertyType),
        splitLevel: {{ $splitLevelFlat ? 'true' : 'false' }},
        extrasOpen: {{ count($addonIds) > 0 ? 'true' : 'false' }},
        regularId: {{ (int) ($regularServiceId ?? 0) }},
        commercialId: {{ (int) ($commercialServiceId ?? 0) }},
        deepLikeIds: {{ json_encode($deepLikeIds) }},
        get isCommercial() { return this.serviceId === this.commercialId },
        get isRegular() { return this.serviceId === this.regularId },
        get needsStatus() { return this.deepLikeIds.includes(this.serviceId) },
        get showResidential() { return ! this.isCommercial },
    }"
    x-on:open-whatsapp.window="window.open($event.detail.url, '_blank')"
    x-on:estimate-validation-failed.window="$nextTick(() => {
        const el = document.querySelector('[aria-invalid=true], .ng-estimate-form .text-red-700');
        el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    })"
>
    <div wire:ignore>
        <x-public.section spacing="hero">
            <x-public.container>
                <div class="max-w-3xl">
                    <p class="ng-eyebrow">Instant estimate</p>
                    <h1 class="ng-display ng-display-hero mt-0">
                        Get your <em class="italic">instant estimate</em>.
                    </h1>
                    <p class="ng-body-lg mt-[18px] text-neutral-700">
                        Answer a few quick questions and see a guide price straight away. Send your request when you are ready - nothing is booked until you say yes.
                    </p>
                </div>
            </x-public.container>
        </x-public.section>
    </div>

    <x-public.section spacing="follow">
        <x-public.container>
            @if ($whatsappNotice)
                <x-public.alert variant="success" title="Request saved" class="mb-6" role="status">
                    {{ $whatsappNotice }}
                </x-public.alert>
            @endif

            @error('submit')
                <x-public.alert variant="error" title="Could not send request" class="mb-6" role="alert">
                    {{ $message }}
                </x-public.alert>
            @enderror

            @if ($errors->any() && ! $errors->has('submit'))
                <div class="mb-6 rounded-[var(--radius-md)] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert" aria-live="assertive">
                    <p class="font-semibold">Please fix the highlighted fields below.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Mobile estimate bar --}}
            @island(name: 'estimate-summary', always: true)
                <div class="mb-5 rounded-[var(--radius-card)] border border-border bg-surface-card p-4 shadow-[var(--shadow-card)] lg:hidden" aria-live="polite">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-ink">Guide estimate</p>
                        <p class="text-lg font-semibold text-brand-800">
                            @if ($this->calculation?->isNumericEstimate)
                                {{ $this->calculation->displayHeadline }}
                            @elseif ($this->selectedService?->requiresManualQuote())
                                Priced per visit
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <p class="mt-1 text-xs text-ink-muted">Updates as you answer.</p>
                </div>
            @endisland

            <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_300px] xl:grid-cols-[minmax(0,1fr)_320px]">
                <div class="space-y-5">
                    <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" type="text" name="website" wire:model="website" tabindex="-1" autocomplete="off">
                    </div>

                    {{-- Step 1: Service --}}
                    <section class="rounded-[var(--radius-card)] border border-border bg-surface-card p-5 shadow-[var(--shadow-card)] sm:p-6" x-intersect.once="$el.classList.add('ng-reveal-in')">
                        <div class="flex items-baseline gap-3">
                            <span class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-600">Step 1</span>
                            <h2 class="ng-display text-xl sm:text-2xl">Which service do you need?</h2>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($this->services as $service)
                                <label class="{{ $chipClass }} min-h-[3.25rem] justify-start gap-3 px-4 text-left">
                                    <input
                                        type="radio"
                                        name="serviceId"
                                        wire:model.live="serviceId"
                                        value="{{ $service->id }}"
                                        class="accent-brand-600"
                                        x-on:change="serviceId = {{ $service->id }}"
                                    >
                                    <span>
                                        <span class="block">{{ $service->name }}</span>
                                        <span
                                            class="mt-1 block text-xs font-normal leading-relaxed text-ink-muted"
                                            x-show="serviceId === {{ $service->id }}"
                                            x-cloak
                                        >{{ $service->estimate_description }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('serviceId') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror

                        <div class="mt-5" x-show="isRegular" x-cloak x-transition>
                            <p class="{{ $labelClass }}">How often?</p>
                            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach (\App\Enums\CleaningFrequency::cases() as $option)
                                    <label class="{{ $chipClass }}">
                                        <input type="radio" name="frequency" wire:model.live="frequency" wire:island="estimate-summary" value="{{ $option->value }}" class="sr-only">
                                        {{ $option->label() }}
                                    </label>
                                @endforeach
                            </div>
                            @error('frequency') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="mt-5" x-show="isCommercial" x-cloak x-transition>
                            <x-public.alert variant="info" title="Commercial quote">
                                Commercial premises are quoted after a short walk-round. We still need your visit preference and contact details below.
                            </x-public.alert>
                        </div>
                    </section>

                    {{-- Step 2: Property --}}
                    <section class="rounded-[var(--radius-card)] border border-border bg-surface-card p-5 shadow-[var(--shadow-card)] sm:p-6">
                        <div class="flex items-baseline gap-3">
                            <span class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-600">Step 2</span>
                            <h2 class="ng-display text-xl sm:text-2xl">About the property</h2>
                        </div>

                        <div class="mt-5 grid gap-5 lg:grid-cols-2">
                            <div>
                                <p class="{{ $labelClass }}">Property type</p>
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    @foreach (\App\Enums\PropertyType::cases() as $type)
                                        <label class="{{ $chipClass }}">
                                            <input
                                                type="radio"
                                                name="propertyType"
                                                wire:model.live="propertyType"
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

                            <div>
                                <label class="{{ $labelClass }}" for="bedrooms">Bedrooms</label>
                                <select id="bedrooms" wire:model.live="bedrooms" wire:island="estimate-summary" class="{{ $inputClass }}">
                                    <option value="0">Studio</option>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">{{ $i }}{{ $i === 5 ? '+' : '' }}</option>
                                    @endfor
                                </select>
                                @error('bedrooms') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-5" x-show="propertyType === 'flat'" x-cloak x-transition>
                            <label class="flex cursor-pointer items-start gap-3 rounded-[var(--radius-md)] border border-border p-4">
                                <input
                                    type="checkbox"
                                    wire:model.live="splitLevelFlat"
                                    class="mt-1 accent-brand-600"
                                    x-on:change="splitLevel = $event.target.checked"
                                >
                                <span>
                                    <span class="font-semibold text-ink">Split-level flat / maisonette</span>
                                    <span class="mt-1 block text-sm text-ink-muted">Tick if your flat has more than one level.</span>
                                </span>
                            </label>
                        </div>

                        <div class="mt-5 max-w-xs" x-show="propertyType !== 'flat' || splitLevel" x-cloak x-transition>
                            <label class="{{ $labelClass }}" for="floors">Number of floors</label>
                            <select id="floors" wire:model.live="floors" wire:island="estimate-summary" class="{{ $inputClass }}">
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            @error('floors') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    {{-- Step 3: Rooms (residential) --}}
                    <section class="rounded-[var(--radius-card)] border border-border bg-surface-card p-5 shadow-[var(--shadow-card)] sm:p-6" x-show="showResidential" x-cloak x-transition>
                        <div class="flex items-baseline gap-3">
                            <span class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-600">Step 3</span>
                            <h2 class="ng-display text-xl sm:text-2xl">Rooms and layout</h2>
                        </div>
                        <p class="mt-2 text-sm text-ink-muted">The first bathroom, kitchen and reception room are included in your starting price.</p>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <label class="{{ $labelClass }}" for="bathrooms">Bathrooms</label>
                                <input id="bathrooms" type="number" min="1" max="6" wire:model.live.debounce.300ms="bathrooms" wire:island="estimate-summary" class="{{ $inputClass }}">
                                @error('bathrooms') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="{{ $labelClass }}" for="wcs">Separate WCs</label>
                                <input id="wcs" type="number" min="0" max="4" wire:model.live.debounce.300ms="wcs" wire:island="estimate-summary" class="{{ $inputClass }}">
                                @error('wcs') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="{{ $labelClass }}" for="kitchens">Kitchens</label>
                                <input id="kitchens" type="number" min="1" max="3" wire:model.live.debounce.300ms="kitchens" wire:island="estimate-summary" class="{{ $inputClass }}">
                                @error('kitchens') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="{{ $labelClass }}" for="receptionRooms">Reception rooms</label>
                                <input id="receptionRooms" type="number" min="0" max="6" wire:model.live.debounce.300ms="receptionRooms" wire:island="estimate-summary" class="{{ $inputClass }}">
                                @error('receptionRooms') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-5">
                            <p class="{{ $labelClass }}">Optional extra rooms</p>
                            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach (\App\Enums\ExtraRoomType::cases() as $room)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-[var(--radius-md)] border border-border px-3 py-2.5 text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                                        <input type="checkbox" wire:model.live="extraRooms" wire:island="estimate-summary" value="{{ $room->value }}" class="accent-brand-600">
                                        {{ $room->label() }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    {{-- Step 4: Condition --}}
                    <section class="rounded-[var(--radius-card)] border border-border bg-surface-card p-5 shadow-[var(--shadow-card)] sm:p-6" x-show="showResidential" x-cloak x-transition>
                        <div class="flex items-baseline gap-3">
                            <span class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-600">Step 4</span>
                            <h2 class="ng-display text-xl sm:text-2xl">Condition</h2>
                        </div>

                        <div class="mt-5" x-show="needsStatus" x-cloak x-transition>
                            <p class="{{ $labelClass }}">Is the property empty or furnished?</p>
                            <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                @foreach (\App\Enums\PropertyStatus::cases() as $status)
                                    <label class="{{ $chipClass }} text-center">
                                        <input type="radio" name="propertyStatus" wire:model.live="propertyStatus" wire:island="estimate-summary" value="{{ $status->value }}" class="sr-only">
                                        {{ $status->label() }}
                                    </label>
                                @endforeach
                            </div>
                            @error('propertyStatus') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="mt-5">
                            <p class="{{ $labelClass }}">Anything that applies</p>
                            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                @foreach (\App\Enums\ConditionFlag::cases() as $flag)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-[var(--radius-md)] border border-border px-3 py-2.5 text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                                        <input type="checkbox" wire:model.live="conditionFlags" wire:island="estimate-summary" value="{{ $flag->value }}" class="mt-0.5 accent-brand-600">
                                        {{ $flag->label() }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="{{ $labelClass }}" for="conditionNotes">Anything else? <span class="font-normal text-ink-muted">(optional)</span></label>
                            <textarea id="conditionNotes" wire:model="conditionNotes" rows="3" class="{{ $inputClass }}" placeholder="Access, pets, parking, or areas that need extra attention."></textarea>
                        </div>
                    </section>

                    {{-- Step 5: Extras accordion --}}
                    <section class="rounded-[var(--radius-card)] border border-border bg-surface-card p-5 shadow-[var(--shadow-card)] sm:p-6" x-show="showResidential" x-cloak x-transition>
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 text-left"
                            x-on:click="extrasOpen = ! extrasOpen"
                            x-bind:aria-expanded="extrasOpen.toString()"
                        >
                            <span class="flex items-baseline gap-3">
                                <span class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-600">Step 5 · optional</span>
                                <span class="ng-display text-xl sm:text-2xl">Any extras?</span>
                            </span>
                            <span class="text-brand-700" x-text="extrasOpen ? '−' : '+'" aria-hidden="true"></span>
                        </button>
                        <p class="mt-2 text-sm text-ink-muted">Tick anything you would like included in the guide estimate.</p>

                        <div class="mt-5 space-y-3" x-show="extrasOpen" x-cloak x-transition>
                            @forelse ($this->availableAddons as $addon)
                                <label class="group block cursor-pointer rounded-[var(--radius-md)] border border-border p-4 transition hover:border-brand-200 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                                    <span class="flex items-start gap-3">
                                        <input type="checkbox" wire:model.live="addonIds" wire:island="estimate-summary" value="{{ $addon->id }}" class="mt-1 accent-brand-600">
                                        <span class="min-w-0 flex-1">
                                            <span class="font-semibold text-ink">{{ $this->addonDisplayLabel($addon) }}</span>
                                            @if ($addon->description)
                                                <span class="mt-1 block text-sm text-ink-muted">{{ $addon->description }}</span>
                                            @endif
                                            @if ($addon->disclaimer)
                                                <span class="mt-2 hidden text-xs text-ink-muted group-has-[:checked]:block">{{ $addon->disclaimer }}</span>
                                            @endif
                                        </span>
                                    </span>
                                </label>
                            @empty
                                <p class="text-sm text-ink-muted">No extras for this service.</p>
                            @endforelse
                        </div>
                    </section>

                    {{-- Step 6: When + details --}}
                    <section class="rounded-[var(--radius-card)] border border-border bg-surface-card p-5 shadow-[var(--shadow-card)] sm:p-6">
                        <div class="flex items-baseline gap-3">
                            <span class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-600">Step 6</span>
                            <h2 class="ng-display text-xl sm:text-2xl">When &amp; your details</h2>
                        </div>

                        <div class="mt-5 grid gap-8 lg:grid-cols-2">
                            <div>
                                <label class="{{ $labelClass }}" for="preferredDate">Preferred date</label>
                                <input id="preferredDate" type="date" wire:model="preferredDate" min="{{ now()->toDateString() }}" class="{{ $inputClass }}">
                                @error('preferredDate') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror

                                <p class="{{ $labelClass }} mt-5">Preferred arrival</p>
                                <div class="mt-3 space-y-2">
                                    @foreach (\App\Enums\ArrivalWindow::cases() as $window)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-[var(--radius-md)] border border-border px-3 py-2.5 text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                                            <input type="radio" name="arrivalWindow" wire:model="arrivalWindow" value="{{ $window->value }}" class="accent-brand-600">
                                            {{ $window->label() }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('arrivalWindow') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="{{ $labelClass }}" for="firstName">First name</label>
                                    <input id="firstName" type="text" wire:model.blur="firstName" autocomplete="given-name" class="{{ $inputClass }}" @error('firstName') aria-invalid="true" @enderror>
                                    @error('firstName') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}" for="lastName">Last name</label>
                                    <input id="lastName" type="text" wire:model.blur="lastName" autocomplete="family-name" class="{{ $inputClass }}" @error('lastName') aria-invalid="true" @enderror>
                                    @error('lastName') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}" for="phone">Phone</label>
                                    <input id="phone" type="tel" wire:model.blur="phone" autocomplete="tel" class="{{ $inputClass }}" @error('phone') aria-invalid="true" @enderror>
                                    @error('phone') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}" for="email">Email</label>
                                    <input id="email" type="email" wire:model.blur="email" autocomplete="email" class="{{ $inputClass }}" @error('email') aria-invalid="true" @enderror>
                                    @error('email') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}" for="postcode">Postcode</label>
                                    <input id="postcode" type="text" wire:model.blur="postcode" autocomplete="postal-code" class="{{ $inputClass }}" placeholder="NG1 1AA" @error('postcode') aria-invalid="true" @enderror>
                                    @error('postcode') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}" for="city">City / town</label>
                                    <input id="city" type="text" wire:model.blur="city" autocomplete="address-level2" class="{{ $inputClass }}" @error('city') aria-invalid="true" @enderror>
                                    @error('city') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="{{ $labelClass }}" for="addressLine1">Address line 1</label>
                                    <input id="addressLine1" type="text" wire:model.blur="addressLine1" autocomplete="address-line1" class="{{ $inputClass }}" @error('addressLine1') aria-invalid="true" @enderror>
                                    @error('addressLine1') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="{{ $labelClass }}" for="addressLine2">Address line 2 <span class="font-normal text-ink-muted">(optional)</span></label>
                                    <input id="addressLine2" type="text" wire:model="addressLine2" autocomplete="address-line2" class="{{ $inputClass }}">
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}" for="parkingNotes">Parking <span class="font-normal text-ink-muted">(optional)</span></label>
                                    <input id="parkingNotes" type="text" wire:model="parkingNotes" class="{{ $inputClass }}" placeholder="Permit, driveway…">
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}" for="accessNotes">Access <span class="font-normal text-ink-muted">(optional)</span></label>
                                    <input id="accessNotes" type="text" wire:model="accessNotes" class="{{ $inputClass }}" placeholder="Key safe, concierge…">
                                </div>
                            </div>
                        </div>

                        <p class="mt-6 text-sm text-ink-muted">
                            By sending this request, you are asking for a written quote. Nothing is booked and no payment is taken at this stage.
                        </p>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <button
                                type="button"
                                wire:click="submit"
                                wire:loading.attr="disabled"
                                @disabled($submitting)
                                class="inline-flex min-h-12 items-center gap-2 rounded-[var(--radius-pill)] bg-brand-600 px-6 text-sm font-semibold text-white shadow-md shadow-brand-700/25 transition hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:opacity-50"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true" wire:loading.remove wire:target="submit"><path d="M3 4a2 2 0 0 0-2 2v1.161l8.441 4.221a1.25 1.25 0 0 0 1.118 0L19 7.162V6a2 2 0 0 0-2-2H3Z"/><path d="m19 8.839-7.77 3.885a2.75 2.75 0 0 1-2.46 0L1 8.839V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.839Z"/></svg>
                                <span wire:loading.remove wire:target="submit">Send my estimate request</span>
                                <span wire:loading wire:target="submit">Sending…</span>
                            </button>
                            <button
                                type="button"
                                wire:click="submitViaWhatsApp"
                                wire:loading.attr="disabled"
                                @disabled($submitting)
                                class="inline-flex min-h-12 items-center gap-2 rounded-[var(--radius-pill)] bg-[#25D366] px-5 text-sm font-semibold text-white shadow-md shadow-[#25D366]/30 transition hover:bg-[#1ebe5d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#25D366] disabled:opacity-50"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 shrink-0" aria-hidden="true" wire:loading.remove wire:target="submitViaWhatsApp"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                <span wire:loading.remove wire:target="submitViaWhatsApp">Continue on WhatsApp</span>
                                <span wire:loading wire:target="submitViaWhatsApp">Saving…</span>
                            </button>
                        </div>
                    </section>
                </div>

                {{-- Desktop sticky estimate --}}
                <aside class="hidden lg:block" aria-label="Guide estimate summary">
                    @island(name: 'estimate-summary', always: true)
                        <div class="ng-sticky-estimate rounded-[var(--radius-card)] border border-border bg-surface-card p-6 shadow-[var(--shadow-card)]">
                            <h2 class="ng-display text-xl">Your guide estimate</h2>
                            <p class="mt-2 text-sm text-ink-muted">Updates as you answer.</p>
                            <div class="mt-5 border-t border-border pt-5" aria-live="polite">
                                @include('livewire.partials.estimate-summary', ['calculation' => $this->calculation])
                            </div>
                        </div>
                    @endisland
                </aside>
            </div>
        </x-public.container>
    </x-public.section>
</div>
