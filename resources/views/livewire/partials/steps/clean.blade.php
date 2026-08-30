@php
    $inputClass = 'mt-1 block w-full rounded-[var(--radius-md)] border border-border bg-surface-card px-3 py-2.5 text-sm text-ink shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200';
    $labelClass = 'text-sm font-semibold text-ink';
    $regularServiceId = $this->services->first(fn ($service) => $service->isRegularClean())?->id;
    $commercialServiceId = $this->services->first(fn ($service) => $service->requiresManualQuote())?->id;
@endphp

<div
    x-data="{
        serviceId: {{ (int) $serviceId }},
        regularId: {{ (int) ($regularServiceId ?? 0) }},
        commercialId: {{ (int) ($commercialServiceId ?? 0) }},
    }"
>
    <h2 class="ng-display text-2xl">Which clean do you need?</h2>
    <p class="mt-2 text-sm text-ink-muted">Choose a service to see the right questions and guide price.</p>

    <div class="mt-6 space-y-3">
        @foreach ($this->services as $service)
            <label class="flex cursor-pointer gap-4 rounded-[var(--radius-md)] border p-4 transition border-border hover:border-brand-200 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60 has-[:checked]:ring-1 has-[:checked]:ring-brand-600">
                <input
                    type="radio"
                    name="serviceId"
                    wire:model.live="serviceId"
                    wire:island="estimate-summary"
                    value="{{ $service->id }}"
                    class="mt-1 size-4 shrink-0 accent-brand-600"
                    x-on:change="serviceId = {{ $service->id }}"
                >
                <span>
                    <span class="font-semibold text-ink">{{ $service->name }}</span>
                    <span
                        class="mt-1 block text-sm leading-relaxed text-ink-muted"
                        x-show="serviceId === {{ $service->id }}"
                        x-cloak
                    >{{ $service->estimate_description }}</span>
                </span>
            </label>
        @endforeach
    </div>
    @error('serviceId') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror

    <div class="mt-8" x-show="serviceId === regularId" x-cloak>
        <p class="{{ $labelClass }}">How often would you like a clean?</p>
        <div class="mt-3 grid gap-2 sm:grid-cols-2">
            @foreach (\App\Enums\CleaningFrequency::cases() as $option)
                <label class="flex cursor-pointer items-center gap-3 rounded-[var(--radius-md)] border border-border px-3 py-3 text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                    <input
                        type="radio"
                        name="frequency"
                        wire:model.live="frequency"
                        wire:island="estimate-summary"
                        value="{{ $option->value }}"
                        class="accent-brand-600"
                    >
                    {{ $option->label() }}
                </label>
            @endforeach
        </div>
        @error('frequency') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div class="mt-6" x-show="serviceId === commercialId" x-cloak>
        <x-public.alert variant="info" title="Commercial quote">
            Commercial premises are quoted after a short walk-round. We will still collect your preferred visit time and contact details.
        </x-public.alert>
    </div>
</div>
