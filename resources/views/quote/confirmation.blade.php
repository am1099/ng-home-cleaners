<x-layouts.quote>
    <x-public.section>
        <x-public.container narrow>
            <div class="max-w-2xl">
                <p class="ng-eyebrow">Request received</p>
                <h1 class="ng-display mt-3 text-3xl sm:text-4xl">Thank you, {{ $quoteRequest->first_name }}.</h1>
                <p class="ng-body-lg mt-3 text-neutral-700">
                    Your estimate request is saved. We will confirm availability and send your fixed price in writing within one working day.
                </p>
            </div>

            <div class="mt-10 rounded-[var(--radius-card)] border border-border bg-surface-card p-6 shadow-[var(--shadow-card)] sm:p-8">
                <dl class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-[var(--tracking-eyebrow)] text-ink-muted">Reference</dt>
                        <dd class="ng-display mt-1 text-2xl text-brand-700">{{ $quoteRequest->reference }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-[var(--tracking-eyebrow)] text-ink-muted">Service</dt>
                        <dd class="mt-1 text-lg font-semibold text-ink">{{ $quoteRequest->service?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-[var(--tracking-eyebrow)] text-ink-muted">Preferred visit</dt>
                        <dd class="mt-1 text-ink">
                            {{ $quoteRequest->preferred_date->format('l j F Y') }}
                            <span class="text-ink-muted">· {{ \App\Enums\ArrivalWindow::tryFrom($quoteRequest->arrival_window)?->label() }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-[var(--tracking-eyebrow)] text-ink-muted">Guide estimate</dt>
                        <dd class="mt-1 text-lg font-semibold text-ink">{{ $quoteRequest->guide_estimate_headline }}</dd>
                        @if ($quoteRequest->guide_estimate_detail)
                            <dd class="mt-1 text-sm text-ink-muted">{{ $quoteRequest->guide_estimate_detail }}</dd>
                        @endif
                    </div>
                </dl>

                <x-public.alert variant="info" title="Guide estimate only" class="mt-8">
                    This guide price helps you plan ahead. It is not yet your final confirmed quote. We will check your details and follow up in writing.
                </x-public.alert>
            </div>

            <div class="mt-10">
                <h2 class="ng-display text-xl">What happens next</h2>
                <ol class="mt-4 space-y-3 text-ink-muted">
                    <li class="flex gap-3">
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-800">1</span>
                        <span>We review your property details and preferred visit window.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-800">2</span>
                        <span>We confirm availability and send your fixed price in writing within one working day.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-800">3</span>
                        <span>Nothing is booked until you reply to confirm.</span>
                    </li>
                </ol>
            </div>

            @if ($whatsappUrl)
                <div class="mt-10 rounded-[var(--radius-card)] border border-border bg-surface-sunken/40 p-6">
                    <h2 class="ng-display text-xl">Want to add anything?</h2>
                    <p class="mt-2 text-sm text-ink-muted">
                        You can open WhatsApp with your reference already included if you would like to share parking details, access notes, or photos.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <x-public.button href="{{ $whatsappUrl }}" variant="whatsapp" target="_blank" rel="noopener noreferrer">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 shrink-0" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                            Continue on WhatsApp
                        </x-public.button>
                        <x-public.button href="{{ route('home') }}" variant="outline">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/></svg>
                            Back to home
                        </x-public.button>
                    </div>
                </div>
            @else
                <div class="mt-10 flex flex-wrap gap-3">
                    <x-public.button href="{{ route('home') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/></svg>
                        Back to home
                    </x-public.button>
                    <x-public.button href="{{ route('contact') }}" variant="outline">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path d="M3.5 2.75a.75.75 0 0 0-1.5 0v14.5a.75.75 0 0 0 1.5 0v-4.392l1.657-.348a6.72 6.72 0 0 1 2.844.05l1.232.298a7.72 7.72 0 0 0 3.26.05l2.27-.454a.75.75 0 0 0-.238-1.474l-2.27.454a6.22 6.22 0 0 1-2.626-.04l-1.232-.298a8.22 8.22 0 0 0-3.48-.062l-.997.21V2.75Z"/></svg>
                        Contact us
                    </x-public.button>
                </div>
            @endif
        </x-public.container>
    </x-public.section>
</x-layouts.quote>
