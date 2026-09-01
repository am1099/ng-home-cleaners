<div class="rounded-[var(--radius-card)] border border-border bg-surface-card p-6 shadow-[var(--shadow-card)]">
    <h2 class="ng-display text-xl text-ink">Do you cover my postcode?</h2>
    <p class="mt-2 text-sm text-ink-muted">Try a district such as NG7, or a full postcode such as NG7 1AA.</p>

    <form wire:submit="check" class="mt-5 flex flex-col gap-3 sm:flex-row">
        <label class="sr-only" for="coverage-postcode">Postcode</label>
        <input
            id="coverage-postcode"
            type="text"
            wire:model="postcode"
            autocomplete="postal-code"
            placeholder="NG7 1AA"
            class="block w-full rounded-[var(--radius-md)] border border-border bg-surface-card px-3 py-2.5 text-sm text-ink shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200"
        >
        <button
            type="submit"
            class="inline-flex min-h-11 items-center justify-center rounded-[var(--radius-pill)] bg-brand-700 px-5 text-sm font-semibold text-white hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="check">Check coverage</span>
            <span wire:loading wire:target="check">Checking…</span>
        </button>
    </form>

    @error('postcode')
        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
    @enderror

    @if ($message)
        <p @class([
            'mt-4 text-sm leading-relaxed',
            'text-brand-800' => $covered,
            'text-ink-muted' => ! $covered,
        ]) role="status">
            {{ $message }}
            @if ($areaUrl)
                <a href="{{ $areaUrl }}" class="ng-link">See {{ $areaName }}</a>
            @endif
        </p>
    @endif
</div>
