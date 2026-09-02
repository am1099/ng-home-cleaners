@php
    $isInk = $variant === 'ink';
@endphp

<div @class([
    'rounded-[var(--radius-card)] border p-6 shadow-[var(--shadow-card)]',
    'border-border bg-surface-card' => ! $isInk,
    'border-white/15 bg-white/5 backdrop-blur-sm' => $isInk,
])>
    <h2 @class([
        'ng-display text-xl',
        'text-ink' => ! $isInk,
        'text-ink-inverse' => $isInk,
    ])>Do you cover my postcode?</h2>
    <p @class([
        'mt-2 text-sm',
        'text-ink-muted' => ! $isInk,
        'text-brand-100/85' => $isInk,
    ])>Try a district such as NG7, or a full postcode such as NG7 1AA.</p>

    <form wire:submit="check" class="mt-5 flex flex-col gap-3 sm:flex-row">
        <label class="sr-only" for="coverage-postcode-{{ $this->getId() }}">Postcode</label>
        <input
            id="coverage-postcode-{{ $this->getId() }}"
            type="text"
            wire:model="postcode"
            autocomplete="postal-code"
            placeholder="NG7 1AA"
            @class([
                'block w-full rounded-[var(--radius-md)] border px-3 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2',
                'border-border bg-surface-card text-ink focus:border-brand-500 focus:ring-brand-200' => ! $isInk,
                'border-white/20 bg-brand-950/40 text-ink-inverse placeholder:text-brand-100/50 focus:border-brand-200 focus:ring-brand-200/40' => $isInk,
            ])
        >
        <button
            type="submit"
            @class([
                'inline-flex min-h-11 items-center justify-center rounded-[var(--radius-pill)] px-5 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2',
                'bg-brand-700 text-white hover:bg-brand-800 focus-visible:outline-brand-700' => ! $isInk,
                'bg-ink-inverse text-brand-950 hover:bg-white focus-visible:outline-ink-inverse' => $isInk,
            ])
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="check">Check coverage</span>
            <span wire:loading wire:target="check">Checking…</span>
        </button>
    </form>

    @error('postcode')
        <p @class(['mt-2 text-sm', 'text-red-700' => ! $isInk, 'text-red-200' => $isInk])>{{ $errors->first('postcode') }}</p>
    @enderror

    @if ($message)
        <div @class([
            'mt-4 text-sm leading-relaxed',
            'text-brand-800' => $covered && ! $isInk,
            'text-brand-100' => $covered && $isInk,
            'text-ink-muted' => ! $covered && ! $isInk,
            'text-brand-100/90' => ! $covered && $isInk,
        ]) role="status">
            <p>{{ $message }}</p>

            @if ($areaUrl)
                <p class="mt-2">
                    <a href="{{ $areaUrl }}" @class(['ng-link', 'text-brand-100 underline decoration-brand-200/60 hover:text-white' => $isInk])>
                        See {{ $areaName }}
                    </a>
                </p>
            @endif

            @if ($covered === false)
                <p class="mt-3 flex flex-wrap gap-x-4 gap-y-2">
                    <a href="{{ route('quote') }}" @class(['ng-link font-semibold', 'text-brand-100 underline decoration-brand-200/60 hover:text-white' => $isInk])>
                        Send it on the estimate form
                    </a>
                    <a href="{{ route('contact') }}" @class(['ng-link font-semibold', 'text-brand-100 underline decoration-brand-200/60 hover:text-white' => $isInk])>
                        Contact us directly
                    </a>
                </p>
            @endif
        </div>
    @endif
</div>
