<h2 class="ng-display text-2xl">Optional extras</h2>
<p class="mt-2 text-sm text-ink-muted">Add any extras you would like included in your guide estimate.</p>

@if ($this->availableAddons->isEmpty())
    <x-public.empty-state class="mt-6" title="No extras for this service" message="You can continue to the next step." />
@else
    <div class="mt-6 space-y-3">
        @foreach ($this->availableAddons as $addon)
            <label class="group block cursor-pointer rounded-[var(--radius-md)] border border-border p-4 transition hover:border-brand-200 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60 has-[:checked]:ring-1 has-[:checked]:ring-brand-600">
                <span class="flex items-start gap-3">
                    <input
                        type="checkbox"
                        wire:model.live="addonIds"
                        wire:island="estimate-summary"
                        value="{{ $addon->id }}"
                        class="mt-1 accent-brand-600"
                    >
                    <span class="min-w-0 flex-1">
                        <span class="font-semibold text-ink">{{ $this->addonDisplayLabel($addon) }}</span>
                        @if ($addon->description)
                            <span class="mt-1 block text-sm text-ink-muted">{{ $addon->description }}</span>
                        @endif
                        @if ($addon->disclaimer)
                            <span class="mt-2 hidden text-xs leading-relaxed text-ink-muted group-has-[:checked]:block">{{ $addon->disclaimer }}</span>
                        @endif
                    </span>
                </span>
            </label>
        @endforeach
    </div>
@endif
@error('addonIds') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
