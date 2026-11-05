@php
    $settings = $this->siteSettings;
    $service = $this->selectedService;
    $isRegular = (bool) $service?->isRegularClean();
    $isCommercial = (bool) $service?->requiresManualQuote();
    $cardClass = 'flex cursor-pointer flex-col justify-center rounded-[var(--radius-md)] border border-border bg-white px-3 py-2.5 text-left transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-brand-600 sm:min-h-[4.75rem] sm:px-4 sm:py-3';
    $chipClass = 'flex cursor-pointer flex-col items-center justify-center rounded-[var(--radius-md)] border border-border bg-white px-3 py-2.5 text-center transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-brand-600';
    $area = trim((string) \Illuminate\Support\Str::before((string) $settings->service_area_summary, '('));
@endphp

<div>
    <x-public.section spacing="hero">
        <x-public.container>
            <div class="mx-auto max-w-6xl">
                <p class="ng-eyebrow">{{ $area !== '' ? $area : 'Nottingham and surrounding areas' }}</p>
                <h1 class="ng-display ng-display-hero mt-3">
                    Your cleaning price, <em class="italic text-brand-800">in seconds.</em>
                </h1>
                <p class="ng-body-lg mt-4 max-w-2xl text-ink-muted">
                    A few taps for your price. Happy with it? Message us on WhatsApp and we'll sort the rest there.
                </p>
            </div>

            @php
                $googleReviewsUrl = filled($settings->google_business_url)
                    ? $settings->google_business_url
                    : 'https://g.page/r/CQuoL3vUxt6mEAE';
            @endphp

            <div class="mx-auto mt-8 max-w-6xl">
                <ul class="flex flex-wrap items-center gap-x-8 gap-y-3 rounded-[var(--radius-card)] border border-border bg-surface-card px-6 py-4 text-sm text-ink shadow-[var(--shadow-card)]">
                    @if ($settings->show_google_reviews)
                        <li>
                            <a href="{{ $googleReviewsUrl }}" class="inline-flex items-center gap-2 font-semibold text-brand-800 underline-offset-4 hover:underline" target="_blank" rel="noopener noreferrer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4 shrink-0" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 21c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 21 12 21z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 1.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                Read our Google reviews
                            </a>
                        </li>
                    @endif
                    @if ($settings->show_insurance_statement && filled($settings->insurance_amount))
                        <li class="inline-flex items-center gap-2">
                            <span class="text-brand-700" aria-hidden="true">&#10003;</span>
                            {{ $settings->insurance_amount }} public liability insured
                        </li>
                    @endif
                    <li class="inline-flex items-center gap-2">
                        <span class="text-brand-700" aria-hidden="true">&#10003;</span>
                        Fixed price agreed before we start
                    </li>
                    @if (filled($settings->hoursSummary()))
                        <li class="inline-flex items-center gap-2">
                            <span class="text-brand-700" aria-hidden="true">&#10003;</span>
                            {{ $settings->hoursSummary() }}
                        </li>
                    @endif
                    @if ($settings->show_dbs_statement)
                        <li class="inline-flex items-center gap-2">
                            <span class="text-brand-700" aria-hidden="true">&#10003;</span>
                            DBS-checked cleaners
                        </li>
                    @endif
                    <li class="inline-flex items-center gap-2">
                        <span class="text-brand-700" aria-hidden="true">&#10003;</span>
                        Free re-clean if you're not happy
                    </li>
                </ul>
            </div>
        </x-public.container>
    </x-public.section>

    <x-public.section spacing="follow" class="!pt-4 lg:!pt-6">
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
                <div class="mb-6 rounded-[var(--radius-md)] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <p class="font-semibold">Please check the highlighted fields.</p>
                </div>
            @endif

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
                                        <span class="mt-1 text-xs font-normal leading-snug text-ink-muted sm:text-sm">{{ $option->estimate_description }}</span>
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
                                        <p class="mt-3 text-sm text-ink-muted">Inside the oven, fridge and cupboards are add-ons below. We bring the products — you provide a vacuum and mop.</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($isRegular)
                            <div class="mt-5">
                                <p class="text-sm font-semibold text-ink">How often?</p>
                                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                                    @foreach ([
                                        \App\Enums\CleaningFrequency::Weekly,
                                        \App\Enums\CleaningFrequency::Fortnightly,
                                        \App\Enums\CleaningFrequency::Monthly,
                                        \App\Enums\CleaningFrequency::OneOff,
                                    ] as $option)
                                        <label class="{{ $chipClass }}" wire:key="frequency-{{ $option->value }}">
                                            <input type="radio" name="frequency" wire:model.live="frequency" value="{{ $option->value }}" class="sr-only">
                                            <span class="text-sm font-semibold text-ink">{{ $option->label() }}</span>
                                            @if (filled($this->frequencyPrices[$option->value] ?? null))
                                                <span class="mt-0.5 text-xs font-medium text-ink-muted">{{ $this->frequencyPrices[$option->value] }}</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                                @error('frequency') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
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

                        <p class="mt-4 text-sm leading-relaxed text-ink-muted">
                            Not been cleaned in a while? Just mention it in the chat — we'll confirm your price from a quick photo before you book.
                        </p>
                    </section>

                    <section class="rounded-[var(--radius-card)] border border-border bg-surface-card p-5 shadow-[var(--shadow-card)] sm:p-6" aria-labelledby="step-details">
                        <h2 id="step-details" class="ng-display text-xl">Your details</h2>
                        <p class="mt-2 text-sm text-ink-muted">Phone is optional if you'd rather carry on by WhatsApp.</p>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="text-sm font-semibold text-ink" for="fullName">Full name</label>
                                <input id="fullName" type="text" wire:model.blur="firstName" autocomplete="name" class="ng-field" placeholder="Jane Smith" @error('firstName') aria-invalid="true" @enderror>
                                @error('firstName') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-ink" for="phone">Phone <span class="font-normal text-ink-muted">(optional)</span></label>
                                <input id="phone" type="tel" wire:model.blur="phone" autocomplete="tel" class="ng-field" placeholder="07…" @error('phone') aria-invalid="true" @enderror>
                                @error('phone') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-ink" for="postcode">Postcode</label>
                                <input id="postcode" type="text" wire:model.blur="postcode" autocomplete="postal-code" class="ng-field" placeholder="NG1 1AA" @error('postcode') aria-invalid="true" @enderror>
                                @error('postcode') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>
                </div>

                <div class="lg:self-stretch">
                    <div class="ng-sticky-estimate-desktop flex flex-col gap-3">
                    <aside class="rounded-[1.25rem] bg-[#10211e] p-6 text-white shadow-[var(--shadow-card)]" aria-label="Your price">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-100">Your price</p>
                        @if ($isCommercial)
                            <p class="ng-display mt-3 text-3xl leading-tight text-white">Quoted on WhatsApp</p>
                        @elseif ($this->priceFigure())
                            <p class="ng-display mt-2 text-5xl leading-none tracking-tight text-white">{{ $this->priceFigure() }}</p>
                            @if ($isRegular)
                                <p class="mt-2 text-sm text-white/70">per visit</p>
                            @endif
                        @else
                            <p class="ng-display mt-3 text-3xl text-white">—</p>
                        @endif

                        <dl class="mt-4 space-y-2 border-t border-white/15 pt-3 text-sm">
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-white/60">Clean</dt>
                                <dd class="text-right font-semibold">
                                    {{ $service?->name ?? 'Choose a clean' }}
                                    @if ($isRegular && $frequency)
                                        · {{ \Illuminate\Support\Str::lower(\App\Enums\CleaningFrequency::from($frequency)->label()) }}
                                    @endif
                                </dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-white/60">Home</dt>
                                <dd class="text-right font-semibold">{{ $this->homeSummary() }}</dd>
                            </div>
                            @if ($this->guideDurationLabel())
                                <div class="flex items-baseline justify-between gap-4">
                                    <dt class="text-white/60">Time</dt>
                                    <dd class="text-right font-semibold">{{ $this->guideDurationLabel() }}</dd>
                                </div>
                            @endif
                            @if ($addonIds !== [])
                                <div class="flex items-baseline justify-between gap-4">
                                    <dt class="text-white/60">Extras</dt>
                                    <dd class="text-right font-semibold">
                                        {{ $this->availableAddons->whereIn('id', array_map('intval', $addonIds))->pluck('label')->join(', ') }}
                                    </dd>
                                </div>
                            @endif
                        </dl>

                        <p class="mt-3 text-sm leading-relaxed text-white/75">
                            Guide price — confirmed from a quick photo on WhatsApp. It only changes if the photo shows something extra, and you'll know before you book.
                        </p>

                        <button
                            type="button"
                            wire:click.prevent="submitViaWhatsApp"
                            wire:loading.attr="disabled"
                            @disabled($submitting)
                            class="mt-4 hidden min-h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-[#25D366] px-5 text-sm font-semibold text-white shadow-md shadow-[#25D366]/30 transition hover:bg-[#1ebe5d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#25D366] disabled:cursor-wait disabled:opacity-50 lg:inline-flex"
                        >
                            <span wire:loading.remove wire:target="submitViaWhatsApp">Book via WhatsApp</span>
                            <span wire:loading wire:target="submitViaWhatsApp">Saving…</span>
                        </button>
                        <p class="mt-3 text-center text-xs text-white/60">Local Nottingham team · no obligation</p>

                        @if (filled($settings->phone))
                            <a href="{{ $settings->phoneTel() }}" class="mt-3 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-full border border-white/25 px-4 text-sm font-semibold text-white hover:bg-white/10">
                                Call {{ $settings->phoneDisplay() }}
                            </a>
                        @endif
                    </aside>

                    <p class="text-center text-sm text-ink-muted">
                        Landlord, agency or a big job?
                        <a href="{{ route('quote.detailed') }}" class="mt-1 block font-semibold text-brand-800 underline underline-offset-4">Send full details instead</a>
                    </p>

                    <ul class="space-y-2 rounded-[var(--radius-card)] border border-border bg-surface-card p-4 text-sm leading-snug text-ink shadow-[var(--shadow-card)]">
                        <li class="flex gap-2"><span class="text-brand-700" aria-hidden="true">&#10003;</span><span>We bring all cleaning products — you provide a vacuum and mop</span></li>
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
                <span wire:loading.remove wire:target="submitViaWhatsApp">WhatsApp us</span>
                <span wire:loading wire:target="submitViaWhatsApp">Saving…</span>
            </button>
        </div>
    </div>
</div>
