@php
    $settings = $this->siteSettings;
    $service = $this->selectedService;
    $isRegular = (bool) $service?->isRegularClean();
    $isCommercial = (bool) $service?->requiresManualQuote();
    $cardClass = 'flex cursor-pointer flex-col justify-center rounded-[var(--radius-md)] border border-border bg-white px-3 py-2.5 text-left transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-brand-600 sm:min-h-[4.75rem] sm:px-4 sm:py-3';
    $area = trim((string) \Illuminate\Support\Str::before((string) $settings->service_area_summary, '('));
    $googleReviewsUrl = filled($settings->google_business_url)
        ? $settings->google_business_url
        : 'https://g.page/r/CQuoL3vUxt6mEAE';
    $fromPrice = $this->regularCleanFromPrice();
    $discountBadge = $this->selectedFrequencyDiscountBadge();
@endphp

<div>
    <x-public.section spacing="hero">
        <x-public.container>
            <div class="mx-auto max-w-6xl">
                <p class="ng-eyebrow">{{ $area !== '' ? $area : 'Nottingham and surrounding areas' }}</p>
                <h1 class="ng-display ng-display-hero mt-3 w-full text-balance text-left">
                    @if ($fromPrice)
                        House cleaning in Nottingham from
                        <span class="ng-price-emphasis">
                            {{ $fromPrice }}
                            <svg class="ng-price-emphasis__swoosh" viewBox="0 0 140 18" preserveAspectRatio="none" aria-hidden="true">
                                <path d="M3 12c12-5 22-8 34-7.2 9 .6 16 3.8 24 4.6 10 1 18-3.4 28-3.8 11-.5 22 3.2 34 6.2 5 1.4 11 2.8 17 2.4" />
                                <path d="M6 14.2c14-3.8 28-6 42-4.8 11 1 19 4.2 30 3.6 12-.7 22-4.6 36-3.2 8 .8 16 2.6 22 3.4" />
                            </svg>
                        </span>
                    @else
                        House cleaning in Nottingham
                    @endif
                </h1>
                <p class="ng-body-lg mt-4 max-w-2xl text-left text-ink-muted">
                    Your price in seconds, then book on WhatsApp.
                </p>
            </div>

            <div class="mx-auto mt-8 max-w-6xl">
                <ul class="grid rounded-2xl bg-[#f3f6f5] sm:grid-cols-2 lg:flex lg:divide-x lg:divide-[#e3e8e6]">
                    @if ($settings->show_dbs_statement)
                        <li class="flex flex-1 items-center gap-3 px-4 py-4 sm:px-5">
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-[#1f8f78] text-white" aria-hidden="true">
                                {{-- Shield with check — vetted / DBS --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 12 2.25c2.43 0 4.717.67 6.686 1.837A11.96 11.96 0 0 1 21.75 12c0 5.334-3.482 9.86-8.25 11.213A11.96 11.96 0 0 1 2.25 12c0-2.43.67-4.717 1.837-6.686A11.96 11.96 0 0 1 12 2.25Z" /></svg>
                            </span>
                            <span class="min-w-0 text-left">
                                <span class="block text-sm font-semibold text-ink">DBS-checked cleaners</span>
                                <span class="mt-0.5 block text-xs text-ink-muted">Trusted and vetted</span>
                            </span>
                        </li>
                    @endif
                    @if ($settings->show_insurance_statement && filled($settings->insurance_amount))
                        <li class="flex flex-1 items-center gap-3 border-t border-[#e3e8e6] px-4 py-4 sm:border-t-0 sm:px-5 lg:border-t-0">
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-[#1f8f78] text-white" aria-hidden="true">
                                {{-- Umbrella — public liability insurance --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5A9 9 0 0 1 12 4.5a9 9 0 0 1 9 9H3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 13.5V20a1.5 1.5 0 0 0 3 0" /></svg>
                            </span>
                            <span class="min-w-0 text-left">
                                <span class="block text-sm font-semibold text-ink">{{ $settings->insurance_amount }} insured</span>
                                <span class="mt-0.5 block text-xs text-ink-muted">Public liability cover</span>
                            </span>
                        </li>
                    @endif
                    <li class="flex flex-1 items-center gap-3 border-t border-[#e3e8e6] px-4 py-4 sm:px-5 lg:border-t-0">
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-[#1f8f78] text-white" aria-hidden="true">
                            {{-- Price tag — fixed price --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                        </span>
                        <span class="min-w-0 text-left">
                            <span class="block text-sm font-semibold text-ink">Fixed price agreed</span>
                            <span class="mt-0.5 block text-xs text-ink-muted">Before we start</span>
                        </span>
                    </li>
                    @if ($settings->show_google_reviews)
                        <li class="flex flex-1 border-t border-[#e3e8e6] lg:border-t-0">
                            <a
                                href="{{ $googleReviewsUrl }}"
                                class="flex min-h-full w-full items-center gap-3 rounded-b-[var(--radius-card)] px-4 py-4 text-sm font-semibold text-brand-800 transition-colors hover:bg-[#0e3b36] hover:text-white focus-visible:bg-[#0e3b36] focus-visible:text-white focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-brand-600 active:bg-[#0c322e] active:text-white lg:rounded-bl-none lg:rounded-br-[var(--radius-card)] sm:px-5"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <span class="inline-flex size-5 shrink-0 items-center justify-center overflow-visible" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20" class="block overflow-visible">
                                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                                        <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                                        <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                                    </svg>
                                </span>
                                <span>Read our Google reviews</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </x-public.container>
    </x-public.section>

    <x-public.section spacing="follow" class="!pt-4 lg:!pt-6">
        <x-public.container>
            <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input id="website" type="text" name="website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="mx-auto grid max-w-6xl items-start gap-8 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-stretch lg:gap-12">
                <div class="min-w-0 space-y-5">
                    <section class="rounded-[var(--radius-card)] border border-border bg-surface-card p-5 shadow-[var(--shadow-card)] sm:p-6" aria-labelledby="step-service">
                        <div class="flex items-baseline gap-3">
                            <span class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-700">Step 1</span>
                            <h2 id="step-service" class="ng-display text-xl sm:text-2xl">What do you need?</h2>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2 sm:mt-5 sm:gap-3">
                            @foreach ($this->services as $option)
                                <label class="{{ $cardClass }}" wire:key="service-{{ $option->id }}">
                                    <input type="radio" name="serviceId" wire:model.live="serviceId" value="{{ $option->id }}" class="sr-only">
                                    <span class="text-sm font-semibold leading-snug text-ink sm:text-base">{{ filled($option->card_title) ? $option->card_title : $option->name }}</span>
                                    @if (filled($option->estimate_description))
                                        <span class="mt-1 text-xs font-normal leading-snug text-ink-muted sm:text-sm">{{ \Illuminate\Support\Str::limit($option->estimate_description, 42) }}</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        @error('serviceId') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror

                        <p class="mt-3 text-sm leading-snug text-ink-muted">
                            <span class="mr-1.5 text-brand-700" aria-hidden="true">&#10003;</span>
                            @if ($isCommercial)
                                Includes the kitchen, washrooms and reception.
                            @else
                                Includes the kitchen and living room.
                            @endif
                        </p>

                        @if ($isRegular)
                            {{-- Mobbin: Sketch / Recraft / Luma — equal cards with percentage-only savings badges --}}
                            <div class="mt-5" wire:key="frequency-options">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <p class="text-sm font-semibold text-ink">How often?</p>
                                    <p class="text-xs font-medium text-brand-700">Save more with a regular clean</p>
                                </div>
                                <div
                                    class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3"
                                    role="radiogroup"
                                    aria-label="Cleaning frequency"
                                >
                                    @foreach (\App\Enums\CleaningFrequency::cases() as $option)
                                        @php
                                            $selected = $frequency === $option->value;
                                            $badge = $this->frequencyDiscountBadge($option);
                                            $saveLabel = $badge ? 'Save '.\Illuminate\Support\Str::before($badge, '%').'%' : null;
                                        @endphp
                                        <label
                                            class="flex min-h-[3.25rem] cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-left transition has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-brand-600 {{ $selected ? 'border-[#0e3b36] bg-[#0e3b36] text-white shadow-sm' : 'border-border bg-white text-ink hover:border-brand-200' }}"
                                            wire:key="frequency-{{ $option->value }}"
                                        >
                                            <input
                                                type="radio"
                                                name="frequency"
                                                wire:model.live="frequency"
                                                value="{{ $option->value }}"
                                                class="sr-only"
                                            >
                                            <span class="inline-flex size-4 shrink-0 items-center justify-center rounded-full border {{ $selected ? 'border-white' : 'border-neutral-300' }}" aria-hidden="true">
                                                @if ($selected)
                                                    <span class="size-2 rounded-full bg-white"></span>
                                                @endif
                                            </span>
                                            <span class="min-w-0 flex flex-1 flex-col gap-1.5">
                                                <span class="text-sm font-semibold leading-none">{{ $option->label() }}</span>
                                                @if ($saveLabel)
                                                    <span class="inline-flex w-fit max-w-full truncate rounded-md bg-[#d8f3e8] px-1.5 py-0.5 text-[0.625rem] font-semibold leading-none text-[#157a5b]">{{ $saveLabel }}</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('frequency') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        @if ($this->inclusions->isNotEmpty())
                            <div class="mt-4 rounded-[var(--radius-md)] border border-border">
                                <button
                                    type="button"
                                    class="flex w-full cursor-pointer items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-brand-800"
                                    wire:click="$toggle('inclusionsOpen')"
                                    aria-expanded="{{ $inclusionsOpen ? 'true' : 'false' }}"
                                >
                                    What's included in a {{ \Illuminate\Support\Str::lower($service?->name ?? 'clean') }}
                                    <span aria-hidden="true">{{ $inclusionsOpen ? '−' : '+' }}</span>
                                </button>
                                @if ($inclusionsOpen)
                                    <div class="border-t border-border px-6 py-5">
                                        <ul class="list-disc space-y-2.5 pl-5 text-sm leading-relaxed text-ink">
                                            @foreach ($this->inclusions as $inclusion)
                                                <li wire:key="inclusion-{{ $inclusion->id }}">{{ $inclusion->content }}</li>
                                            @endforeach
                                        </ul>
                                        @if ($isRegular || filled($service?->equipment_note))
                                            <p class="mt-3 text-sm text-ink-muted">
                                                @if ($isRegular)
                                                    Inside the oven, fridge and cupboards are add-ons below.
                                                @endif
                                                @if (filled($service?->equipment_note))
                                                    {{ $service->equipment_note }}
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                    </section>

                    <section class="rounded-[var(--radius-card)] border border-border bg-surface-card p-5 shadow-[var(--shadow-card)] sm:p-6" aria-labelledby="step-home">
                        <div class="flex items-baseline gap-3">
                            <span class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-700">Step 2</span>
                            <h2 id="step-home" class="ng-display text-xl sm:text-2xl">Your home</h2>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <x-public.form.quantity
                                id="bedrooms"
                                label="Bedrooms"
                                field="bedrooms"
                                :value="$bedrooms"
                                :min="0"
                                :max="5"
                                :display="$bedrooms === 0 ? 'Studio' : ($bedrooms >= 5 ? '5+' : (string) $bedrooms)"
                            />
                            <x-public.form.quantity
                                id="bathrooms"
                                label="Bathrooms"
                                field="bathrooms"
                                :value="$bathrooms"
                                :min="1"
                                :max="6"
                            />
                        </div>

                        @if ($this->availableAddons->isNotEmpty())
                            <div class="mt-4 rounded-[var(--radius-md)] border border-border">
                                <button
                                    type="button"
                                    class="flex w-full cursor-pointer items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-brand-800"
                                    wire:click="toggleExtras"
                                    aria-expanded="{{ $extrasOpen ? 'true' : 'false' }}"
                                >
                                    + Add extras (optional)
                                    <span aria-hidden="true">{{ $extrasOpen ? '−' : '+' }}</span>
                                </button>
                                @if ($extrasOpen)
                                    <div class="grid gap-2 border-t border-border p-3 sm:grid-cols-2">
                                        @foreach ($this->availableAddons as $addon)
                                            @php $selected = in_array((int) $addon->id, array_map('intval', $addonIds), true); @endphp
                                            <button
                                                type="button"
                                                wire:click="toggleAddon({{ $addon->id }})"
                                                wire:key="addon-{{ $addon->id }}"
                                                @class([
                                                    'cursor-pointer rounded-[var(--radius-md)] border px-3 py-2.5 text-left text-sm font-semibold transition',
                                                    'border-brand-600 bg-brand-50 text-brand-900' => $selected,
                                                    'border-border bg-white text-ink hover:border-brand-200' => ! $selected,
                                                ])
                                                aria-pressed="{{ $selected ? 'true' : 'false' }}"
                                            >
                                                {{ $this->addonDisplayLabel($addon) }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        <p class="mt-4 text-sm leading-snug text-ink-muted">
                            Longer gap between cleans? Send a quick photo on WhatsApp.
                        </p>
                    </section>

                    @if ($this->quoteGalleryPhotos !== [])
                        <x-public.quote-recent-work :photos="$this->quoteGalleryPhotos" />
                    @endif
                </div>

                <div class="lg:self-stretch">
                    <div class="ng-sticky-estimate-desktop flex flex-col gap-4">
                        {{-- Mobbin: Open / Pitch / X — rounded dark summary, % badge, high-contrast CTA --}}
                        <aside class="overflow-hidden rounded-2xl bg-[#0e3b36] p-6 text-white shadow-[var(--shadow-card)]" aria-label="Your guide price">
                            <p class="text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-white/70">Your guide price</p>

                            @if ($isCommercial)
                                <p class="ng-display mt-3 text-3xl leading-tight text-white">Quoted on WhatsApp</p>
                            @elseif ($this->priceFigure())
                                <p class="ng-display mt-2 text-5xl leading-none tracking-tight text-white">{{ $this->priceFigure() }}</p>
                                @if ($isRegular)
                                    <p class="mt-1.5 text-sm text-white/70">per visit</p>
                                @endif
                            @else
                                <p class="ng-display mt-3 text-3xl text-white">—</p>
                            @endif

                            <dl class="mt-5 space-y-3 border-t border-white/15 pt-4 text-sm">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="inline-flex items-center gap-2 text-white/70">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-white/50" aria-hidden="true"><path d="M7.5 2.5A1.5 1.5 0 0 0 6 4v12.5a.75.75 0 0 0 1.28.53L10 14.56l2.72 2.47A.75.75 0 0 0 14 16.5V4a1.5 1.5 0 0 0-1.5-1.5h-5Z" /></svg>
                                        Clean
                                    </dt>
                                    <dd class="text-right font-semibold">{{ $service?->name ?? 'Choose a clean' }}</dd>
                                </div>
                                @if ($isRegular && $this->frequencyLabel())
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="inline-flex items-center gap-2 text-white/70">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-white/50" aria-hidden="true"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.414 0-.75.336-.75.75v6.25c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25V8.25a.75.75 0 0 0-.75-.75H4.75Z" clip-rule="evenodd" /></svg>
                                            Frequency
                                        </dt>
                                        <dd class="text-right font-semibold">
                                            @if ($discountBadge)
                                                <span class="inline-flex rounded-full bg-[#d8f3e8] px-2.5 py-1 text-xs font-semibold text-[#146c52]">{{ $this->frequencyLabel() }} ({{ $discountBadge }})</span>
                                            @else
                                                {{ $this->frequencyLabel() }}
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="inline-flex items-center gap-2 text-white/70">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-white/50" aria-hidden="true"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2.5a.5.5 0 0 1-.5-.5V14a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v3.5a.5.5 0 0 1-.5.5H4a1 1 0 0 1-1-1v-6H2a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd" /></svg>
                                        Home
                                    </dt>
                                    <dd class="text-right font-semibold">{{ $this->homeSummary() }}</dd>
                                </div>
                                @if ($this->guideDurationLabel())
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="inline-flex items-center gap-2 text-white/70">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-white/50" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd" /></svg>
                                            Time
                                        </dt>
                                        <dd class="text-right font-semibold">{{ \Illuminate\Support\Str::ucfirst($this->guideDurationLabel()) }}</dd>
                                    </div>
                                @endif
                                @if ($addonIds !== [])
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-white/70">Extras</dt>
                                        <dd class="text-right font-semibold">
                                            {{ $this->availableAddons->whereIn('id', array_map('intval', $addonIds))->pluck('label')->join(', ') }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>

                            <button
                                type="button"
                                wire:click.prevent="submitViaWhatsApp"
                                wire:loading.attr="disabled"
                                @disabled($submitting)
                                class="mt-5 inline-flex min-h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-[#25D366] px-5 text-sm font-semibold text-white transition hover:bg-[#1ebe5d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#25D366] disabled:cursor-wait disabled:opacity-50"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 shrink-0" aria-hidden="true"><path d="M12.04 2C6.58 2 2.15 6.4 2.15 11.83c0 1.74.46 3.44 1.34 4.94L2 22l5.39-1.41a10.1 10.1 0 0 0 4.65 1.12h.01c5.46 0 9.89-4.4 9.89-9.83C21.94 6.4 17.5 2 12.04 2Zm5.76 13.92c-.24.68-1.4 1.3-1.94 1.38-.5.08-1.12.11-1.81-.11-.42-.14-.95-.31-1.64-.61-2.89-1.25-4.77-4.16-4.91-4.35-.14-.19-1.16-1.54-1.16-2.94 0-1.4.73-2.09 1-2.37.26-.28.57-.35.76-.35h.55c.17 0 .41-.06.64.49.24.57.81 1.97.88 2.11.07.15.12.32.02.51-.1.19-.15.31-.29.48-.14.17-.3.37-.43.5-.14.14-.29.29-.12.56.17.28.74 1.22 1.59 1.98 1.09.97 2.01 1.27 2.29 1.41.28.14.44.12.6-.07.17-.19.68-.79.86-1.06.18-.27.36-.22.6-.13.24.09 1.54.73 1.8.86.26.13.44.2.5.31.07.11.07.64-.17 1.32Z" /></svg>
                                <span wire:loading.remove wire:target="submitViaWhatsApp">Book via WhatsApp</span>
                                <span wire:loading wire:target="submitViaWhatsApp">Saving…</span>
                            </button>

                            @if (filled($this->whatsappFallbackUrl))
                                <p class="mt-3 text-sm leading-snug text-white/80">
                                    {{ $this->whatsappNotice }}
                                    <a href="{{ $this->whatsappFallbackUrl }}" class="mt-1 block font-semibold text-white underline underline-offset-2">Open WhatsApp</a>
                                </p>
                            @endif

                            @if (filled($settings->phone))
                                <a href="{{ $settings->phoneTel() }}" class="mt-3 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-full border border-white/25 px-4 text-sm font-semibold text-white transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 15.352V16.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd" /></svg>
                                    Call {{ $settings->phoneDisplay() }}
                                </a>
                            @endif

                            <p class="mt-4 flex items-start gap-2 text-xs leading-snug text-white/65">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="mt-0.5 size-3.5 shrink-0 text-white/50" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" /></svg>
                                Guide price — confirmed before we start.
                            </p>
                        </aside>

                        {{-- Mobbin: Me+ / Liven — compact reviews beside the booking decision --}}
                        @if ($this->quoteTestimonials->isNotEmpty())
                            <section class="rounded-[var(--radius-card)] border border-border bg-surface-card p-4 shadow-[var(--shadow-card)] sm:p-5" aria-labelledby="quote-reviews-heading">
                                <div class="flex items-baseline justify-between gap-3">
                                    <h2 id="quote-reviews-heading" class="text-sm font-semibold text-ink">What our customers say</h2>
                                    @if ($settings->show_google_reviews)
                                        <a href="{{ $googleReviewsUrl }}" class="shrink-0 rounded-md px-2 py-1 text-xs font-semibold text-brand-700 transition-colors hover:bg-[#0e3b36] hover:text-white focus-visible:bg-[#0e3b36] focus-visible:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600" target="_blank" rel="noopener noreferrer">
                                            Read all Google reviews
                                        </a>
                                    @endif
                                </div>
                                <div class="mt-3 grid gap-3 sm:grid-cols-1">
                                    @foreach ($this->quoteTestimonials->take(3) as $testimonial)
                                        <figure wire:key="quote-review-{{ $testimonial->id }}" class="rounded-[var(--radius-md)] border border-border/80 bg-white px-3.5 py-3">
                                            <div class="flex items-center justify-between gap-2">
                                                @if (filled($testimonial->rating))
                                                    <p class="text-xs font-semibold tracking-wide text-accent-brass" aria-label="{{ $testimonial->rating }} out of 5 stars">
                                                        {{ str_repeat('★', max(0, min(5, (int) $testimonial->rating))) }}
                                                    </p>
                                                @endif
                                                @if ($settings->show_google_reviews)
                                                    <span class="text-[0.65rem] font-medium uppercase tracking-wide text-ink-muted">Google</span>
                                                @endif
                                            </div>
                                            @if (filled($testimonial->review))
                                                <blockquote class="mt-1.5 text-sm leading-snug text-ink">&ldquo;{{ \Illuminate\Support\Str::limit($testimonial->review, 110) }}&rdquo;</blockquote>
                                            @endif
                                            <figcaption class="mt-2 text-xs text-ink-muted">
                                                <span class="font-semibold text-ink">{{ $testimonial->customer_name }}</span>
                                                @if (filled($testimonial->location))
                                                    <span aria-hidden="true"> · </span>{{ $testimonial->location }}
                                                @endif
                                            </figcaption>
                                        </figure>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <p class="text-center text-sm text-ink-muted">
                            Landlord, agency or a big job?
                            <a href="{{ route('quote.detailed') }}" class="mt-1 block font-semibold text-brand-800 underline underline-offset-4">Send full details instead</a>
                        </p>

                        <ul class="space-y-2 rounded-[var(--radius-card)] border border-border bg-surface-card p-4 text-sm leading-snug text-ink shadow-[var(--shadow-card)]">
                            @if (filled($service?->equipment_note))
                                <li class="flex gap-2"><span class="text-brand-700" aria-hidden="true">&#10003;</span><span>{{ $service->equipment_note }}</span></li>
                            @endif
                            <li class="flex gap-2"><span class="text-brand-700" aria-hidden="true">&#10003;</span><span>Not happy? Tell us within 48 hours and we'll re-clean free</span></li>
                            <li class="flex gap-2"><span class="text-brand-700" aria-hidden="true">&#10003;</span><span>No contract — cancel or reschedule free with 24 hours' notice</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-public.container>
    </x-public.section>

    <div class="ng-mobile-quote-bar fixed inset-x-0 bottom-0 z-40 border-t border-border bg-white/95 shadow-[0_-10px_30px_rgba(16,33,30,0.08)] backdrop-blur-md lg:hidden" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))">
        <div class="flex items-center justify-between gap-4 px-4 pt-3">
            <div class="min-w-0">
                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-ink-muted">Your price</p>
                @if ($isCommercial)
                    <p class="ng-display mt-1 text-xl leading-none text-ink">Quoted</p>
                @elseif ($this->priceFigure())
                    <p class="mt-1 flex items-baseline gap-1.5 leading-none text-ink">
                        <span class="ng-display text-[1.85rem] tracking-tight">{{ $this->priceFigure() }}</span>
                        @if ($isRegular)
                            <span class="text-sm font-medium text-ink-muted">/ visit</span>
                        @endif
                    </p>
                @else
                    <p class="ng-display mt-1 text-xl leading-none text-ink">—</p>
                @endif
            </div>
            <button
                type="button"
                wire:click.prevent="submitViaWhatsApp"
                wire:loading.attr="disabled"
                @disabled($submitting)
                class="inline-flex h-12 shrink-0 cursor-pointer items-center justify-center rounded-full bg-[#25D366] px-5 text-sm font-semibold text-white shadow-md shadow-[#25D366]/35 transition hover:bg-[#1ebe5d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#25D366] disabled:cursor-wait disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="submitViaWhatsApp">Book via WhatsApp</span>
                <span wire:loading wire:target="submitViaWhatsApp">Saving…</span>
            </button>
        </div>
    </div>
</div>
