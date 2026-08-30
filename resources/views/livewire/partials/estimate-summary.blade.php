@php
    $expanded = $expanded ?? false;
    $service = $this->selectedService;
    $calculation = $calculation ?? $this->calculation;
@endphp

<dl class="space-y-4 text-sm">
    <div>
        <dt class="font-semibold text-ink">Clean</dt>
        <dd class="mt-1 text-ink-muted">
            {{ $service?->name ?? 'Not selected' }}
            @if ($service?->isRegularClean() && $frequency)
                · {{ \App\Enums\CleaningFrequency::from($frequency)->label() }}
            @endif
        </dd>
    </div>

    @if ($service && ! $service->requiresManualQuote())
        <div>
            <dt class="font-semibold text-ink">Property</dt>
            <dd class="mt-1 text-ink-muted">
                {{ \App\Enums\PropertyType::tryFrom($propertyType)?->label() ?? $propertyType }}
                · {{ $bedrooms === 0 ? 'Studio' : $bedrooms.($bedrooms === 5 ? '+' : '').' bed' }}
                @if ($propertyType === \App\Enums\PropertyType::Flat->value && $splitLevelFlat)
                    · Split-level
                @endif
                · {{ $floors }} {{ Str::plural('floor', $floors) }}
            </dd>
        </div>

        <div>
            <dt class="font-semibold text-ink">Rooms</dt>
            <dd class="mt-1 text-ink-muted">
                {{ $bathrooms }} {{ Str::plural('bathroom', $bathrooms) }},
                {{ $wcs }} WC,
                {{ $kitchens }} {{ Str::plural('kitchen', $kitchens) }},
                {{ $receptionRooms }} reception
                @if ($extraRooms !== [])
                    · Extra: {{ collect($extraRooms)->map(fn ($slug) => \App\Enums\ExtraRoomType::tryFrom($slug)?->label() ?? $slug)->join(', ') }}
                @endif
            </dd>
        </div>

        <div>
            <dt class="font-semibold text-ink">Condition</dt>
            <dd class="mt-1 text-ink-muted">
                @if ($propertyStatus)
                    {{ \App\Enums\PropertyStatus::tryFrom($propertyStatus)?->label() }}.
                @endif
                @if ($conditionFlags === [] && blank($conditionNotes))
                    Standard condition.
                @else
                    @if ($conditionFlags !== [])
                        {{ collect($conditionFlags)->map(fn ($flag) => \App\Enums\ConditionFlag::tryFrom($flag)?->label())->filter()->join(', ') }}.
                    @endif
                    @if (filled($conditionNotes))
                        {{ $conditionNotes }}
                    @endif
                @endif
            </dd>
        </div>

        <div>
            <dt class="font-semibold text-ink">Extras</dt>
            <dd class="mt-1 text-ink-muted">
                @if ($addonIds === [])
                    None selected.
                @else
                    <ul class="space-y-1">
                        @foreach ($this->availableAddons->whereIn('id', $addonIds) as $addon)
                            <li class="flex justify-between gap-3">
                                <span>{{ $addon->label }}</span>
                                <span class="shrink-0 font-semibold text-brand-800">{{ $this->addonDisplayLabel($addon) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </dd>
        </div>
    @elseif ($service?->requiresManualQuote())
        <div>
            <dt class="font-semibold text-ink">Property</dt>
            <dd class="mt-1 text-ink-muted">
                {{ \App\Enums\PropertyType::tryFrom($propertyType)?->label() ?? $propertyType }}
                · {{ $bedrooms === 0 ? 'Studio' : $bedrooms.($bedrooms === 5 ? '+' : '').' bed' }}
            </dd>
        </div>
    @endif

    @if ($preferredDate)
        <div>
            <dt class="font-semibold text-ink">Preferred visit</dt>
            <dd class="mt-1 text-ink-muted">
                {{ \Illuminate\Support\Carbon::parse($preferredDate)->format('l j F Y') }}
                @if ($arrivalWindow)
                    · {{ \App\Enums\ArrivalWindow::tryFrom($arrivalWindow)?->label() }}
                @endif
            </dd>
        </div>
    @endif

    @if ($expanded && filled($firstName))
        <div>
            <dt class="font-semibold text-ink">Contact</dt>
            <dd class="mt-1 text-ink-muted">
                {{ $firstName }} {{ $lastName }}<br>
                {{ $phone }}<br>
                {{ $email }}<br>
                @if ($postcode)
                    {{ $postcode }}@if($addressLine1), {{ $addressLine1 }}@endif
                @endif
            </dd>
        </div>
    @endif
</dl>

<div class="mt-5 border-t border-border pt-5">
    @if ($calculation?->isNumericEstimate)
        <p class="text-2xl font-semibold text-brand-900 transition-all duration-300">{{ $calculation->displayHeadline }}</p>
        <p class="mt-1 text-xs text-ink-muted">{{ $calculation->displayDetail }}</p>
    @elseif ($service?->requiresManualQuote())
        <p class="text-lg font-semibold text-brand-900">Priced per visit</p>
        <p class="mt-1 text-xs text-ink-muted">We will quote after a walk-round.</p>
    @else
        <p class="text-sm text-ink-muted">Answer a few questions to see your guide estimate.</p>
    @endif
</div>
