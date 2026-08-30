<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-end gap-3">
                <x-filament::button wire:click="previousMonth" color="gray" size="sm">
                    Previous month
                </x-filament::button>
                <x-filament::button wire:click="goToToday" color="gray" size="sm">
                    Today
                </x-filament::button>
                <x-filament::button wire:click="nextMonth" color="gray" size="sm">
                    Next month
                </x-filament::button>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Revenue this month (received):
                <span class="font-semibold text-gray-950 dark:text-white">{{ $this->monthRevenue() }}</span>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
            {{ $this->filtersForm }}
            @if ($serviceId || $postcode)
                <div class="mt-3">
                    <x-filament::button wire:click="clearFilters" color="gray" size="sm">
                        Clear filters
                    </x-filament::button>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-7 gap-px overflow-hidden rounded-xl bg-gray-200 dark:bg-white/10">
            @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                <div class="bg-gray-50 px-2 py-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                    {{ $weekday }}
                </div>
            @endforeach

            @foreach ($this->calendarDays() as $day)
                <div @class([
                    'min-h-28 bg-white p-2 dark:bg-gray-900',
                    'opacity-45' => ! $day['inMonth'],
                ])>
                    <div @class([
                        'mb-2 text-sm font-medium',
                        'text-primary-600 dark:text-primary-400' => $day['date']->isToday(),
                        'text-gray-950 dark:text-white' => ! $day['date']->isToday(),
                    ])>
                        {{ $day['date']->format('j') }}
                    </div>

                    <div class="space-y-1">
                        @forelse ($day['bookings'] as $booking)
                            @php
                                $tone = $booking->service?->calendarColors() ?? [
                                    'bg' => 'bg-gray-100 dark:bg-white/10',
                                    'border' => 'border-gray-400 dark:border-white/30',
                                    'text' => 'text-gray-950 dark:text-gray-100',
                                    'pill' => 'bg-gray-700 text-white',
                                ];
                            @endphp
                            <button
                                type="button"
                                wire:click="openBooking({{ $booking->id }})"
                                @class([
                                    'block w-full rounded-md border-2 px-1.5 py-1 text-left shadow-sm transition hover:brightness-95',
                                    $tone['bg'],
                                    $tone['border'],
                                    $tone['text'],
                                ])
                            >
                                <div class="mb-0.5 flex items-center justify-between gap-1">
                                    <span @class(['rounded-full px-1.5 py-px text-[10px] font-semibold leading-none', $tone['pill']])>
                                        {{ $this->postcodePill($booking) }}
                                    </span>
                                    <span class="truncate text-[10px] opacity-80">{{ $booking->arrival_window->shortLabel() }}</span>
                                </div>
                                <div class="truncate text-xs font-semibold">
                                    {{ $booking->customer?->fullName() ?? 'Customer' }}
                                </div>
                                <div class="truncate text-[11px] font-medium opacity-90">
                                    {{ $booking->service?->name }}
                                </div>
                            </button>
                        @empty
                            <div class="text-[11px] text-gray-400 dark:text-gray-500">—</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @php $preview = $this->previewBooking(); @endphp
    @if ($preview)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4"
            wire:click.self="closeBookingPreview"
            role="dialog"
            aria-modal="true"
            aria-labelledby="booking-preview-title"
        >
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $preview->reference }}</p>
                        <h2 id="booking-preview-title" class="text-xl font-semibold text-gray-950 dark:text-white">
                            {{ $preview->customer?->fullName() ?? 'Customer' }}
                        </h2>
                    </div>
                    <x-filament::button wire:click="closeBookingPreview" color="gray" size="sm">
                        Close
                    </x-filament::button>
                </div>

                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Service</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $preview->service?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $preview->status->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Date</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $preview->booking_date?->format('j M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Arrival</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $preview->arrival_window->label() }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-gray-500 dark:text-gray-400">Address</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $preview->fullAddress() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Postcode district</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $this->postcodePill($preview) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Agreed price</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $preview->agreedDisplay() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Paid</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $preview->paidDisplay() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Outstanding</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $preview->outstandingDisplay() }}</dd>
                    </div>
                    @if (filled($preview->internal_notes))
                        <div class="col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400">Internal notes</dt>
                            <dd class="whitespace-pre-wrap text-gray-950 dark:text-white">{{ $preview->internal_notes }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-6 flex flex-wrap gap-2">
                    <x-filament::button tag="a" :href="$this->bookingUrl($preview)" color="primary" size="sm">
                        Open booking
                    </x-filament::button>
                    <x-filament::button tag="a" :href="$this->editBookingUrl($preview)" color="gray" size="sm">
                        Edit
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
