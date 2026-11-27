@php
    $dashboard = $this->dashboard();
    $business = $dashboard['business'];
    $ads = $dashboard['ads'];
    $attribution = $dashboard['attribution'];
    $activity = $dashboard['activity'];
    $google = $this->googleAccount();
    $card = 'ng-rise rounded-2xl border border-gray-200 bg-white p-5 transition dark:border-white/10 dark:bg-gray-900';
    $quoteTotal = array_sum(array_column($dashboard['services'], 'quotes'));
    $sliceColors = ['#208378', '#5eead4', '#0f766e', '#99f6e4'];
    $quoteSlices = [];
    $sliceCursor = 0.0;
    foreach ($dashboard['services'] as $sliceIndex => $sliceService) {
        if ($quoteTotal < 1 || $sliceService['quotes'] < 1) {
            continue;
        }
        $share = ($sliceService['quotes'] / $quoteTotal) * 100;
        $quoteSlices[] = [
            'name' => $sliceService['name'],
            'quotes' => $sliceService['quotes'],
            'share' => round($share, 1),
            'color' => $sliceColors[$sliceIndex % 4],
            'offset' => round($sliceCursor, 2),
        ];
        $sliceCursor += $share;
    }
@endphp

<x-filament-panels::page>
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">Your enquiries, bookings and revenue at a glance.</p>
                <p class="mt-1 text-sm text-gray-500">{{ $business['range'] }} <span class="text-gray-400">compared with {{ $business['previous_range'] }}</span></p>
            </div>
            <div class="flex gap-2" role="group" aria-label="Date range">
                @foreach ([7 => '7 days', 30 => '30 days', 90 => '90 days'] as $value => $label)
                    <x-filament::button
                        size="sm"
                        :color="$dashboard['period']->days === $value ? 'primary' : 'gray'"
                        wire:click="range({{ $value }})"
                    >
                        {{ $label }}
                    </x-filament::button>
                @endforeach
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Booked jobs', number_format($business['booked']['count']), $business['booked']['change'], $business['booked']['hint'], $business['booked']['href']],
                ['Money received', $business['received']['amount'], $business['received']['change'], $business['received']['hint'], $business['received']['href']],
                ['Saved quotes', number_format($business['quotes']['count']), $business['quotes']['change'], $business['quotes']['hint'], $business['quotes']['href']],
                ['Needs attention', number_format($business['attention']['count']), $business['attention']['change'], $business['attention']['hint'], $business['attention']['href']],
            ] as [$label, $value, $change, $hint, $href])
                <a href="{{ $href }}" class="ng-clickable ng-rise group rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900" style="animation-delay: {{ $loop->index * 70 }}ms">
                    <span class="block h-1 w-10 rounded-full bg-primary-600"></span>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 tabular-nums dark:text-white">{{ $value }}</p>
                    <p class="mt-2 text-sm font-medium text-primary-700 dark:text-primary-400">{{ $change }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ $hint }}</p>
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-primary-700 transition group-hover:gap-2 dark:text-primary-300">
                        Open
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                    </span>
                </a>
            @endforeach
        </div>

        <div
            class="flex flex-col gap-8"
            x-data="{
                order: $wire.entangle('sectionOrder'),
                dragging: null,
                index(id) { return this.order.indexOf(id) },
                start(id) { this.dragging = id },
                over(event) { event.preventDefault() },
                drop(id) {
                    if (! this.dragging || this.dragging === id) {
                        return
                    }
                    const next = [...this.order]
                    const from = next.indexOf(this.dragging)
                    next.splice(from, 1)
                    next.splice(next.indexOf(id), 0, this.dragging)
                    this.keep(next)
                    this.dragging = null
                },
                move(id, direction) {
                    const next = [...this.order]
                    const from = next.indexOf(id)
                    const to = from + direction
                    if (to < 0 || to >= next.length) {
                        return
                    }
                    const [item] = next.splice(from, 1)
                    next.splice(to, 0, item)
                    this.keep(next)
                },
                keep(next) {
                    this.order = next
                    $wire.set('sectionOrder', next)
                },
            }"
        >
        <section class="{{ $card }}" :style="{ order: index('enquiries') }" :class="dragging === 'enquiries' ? 'opacity-60' : ''" @dragover="over($event)" @drop="drop('enquiries')">
            @include('filament.pages.partials.analytics-move', ['id' => 'enquiries', 'label' => 'open enquiries'])
            <div class="grid items-start gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="flex items-baseline justify-between gap-3">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Open enquiries</h2>
                    @if ($business['attention']['older'] > 0)
                        <p class="text-sm text-gray-500">{{ $business['attention']['older'] }} older {{ $business['attention']['older'] === 1 ? 'lead is' : 'leads are' }} still open.</p>
                    @endif
                </div>
                @if ($business['attention']['items'] === [])
                    <p class="mt-4 text-sm text-gray-500">No open enquiries in this period.</p>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach ($business['attention']['items'] as $item)
                            <li wire:key="attention-{{ $item['reference'] }}">
                                <a href="{{ $item['href'] }}" class="ng-clickable flex flex-col gap-1 rounded-xl border border-transparent px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
                                    <span class="min-w-0">
                                        <span class="font-semibold text-primary-700 group-hover:underline dark:text-primary-300">{{ $item['reference'] }}</span>
                                        <span class="mt-0.5 block text-sm text-gray-500">{{ $item['service'] }} · {{ $item['status'] }}</span>
                                    </span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $item['note'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <a href="{{ $business['attention']['href'] }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-primary-700 transition hover:gap-2 hover:underline dark:text-primary-300">All leads</a>
            </div>
            <aside class="rounded-2xl border border-gray-100 bg-gray-50/80 p-4 dark:border-white/10 dark:bg-white/5">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Quotes by service</h3>
                <p class="mt-1 text-xs text-gray-500">Saved quotes in this period. Not bookings.</p>
                @if ($quoteSlices === [])
                    <p class="mt-6 text-sm text-gray-500">No quotes in this period.</p>
                @else
                    <div class="ng-donut mt-4 flex items-center gap-4">
                        <svg viewBox="0 0 42 42" class="size-28 shrink-0 -rotate-90" role="img" aria-label="Quotes by service">
                            <circle cx="21" cy="21" r="15.5" fill="none" class="stroke-gray-200 dark:stroke-white/10" stroke-width="6" />
                            @foreach ($quoteSlices as $slice)
                                <circle cx="21" cy="21" r="15.5" fill="none" stroke="{{ $slice['color'] }}" stroke-width="6" pathLength="100" stroke-dasharray="{{ $slice['share'] }} {{ 100 - $slice['share'] }}" stroke-dashoffset="{{ -$slice['offset'] }}" stroke-linecap="butt" />
                            @endforeach
                        </svg>
                        <ul class="min-w-0 space-y-2 text-sm">
                            @foreach ($quoteSlices as $slice)
                                <li class="flex items-center justify-between gap-3">
                                    <span class="flex min-w-0 items-center gap-2 text-gray-700 dark:text-gray-200">
                                        <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $slice['color'] }}"></span>
                                        <span class="truncate">{{ $slice['name'] }}</span>
                                    </span>
                                    <span class="tabular-nums text-gray-500">{{ $slice['quotes'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
            </div>
        </section>

        <section class="{{ $card }}" :style="{ order: index('advertising') }" :class="dragging === 'advertising' ? 'opacity-60' : ''" @dragover="over($event)" @drop="drop('advertising')">
            @include('filament.pages.partials.analytics-move', ['id' => 'advertising', 'label' => 'advertising'])
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Advertising</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ $ads['message'] }}</p>
                </div>
                <a href="{{ $ads['account_url'] }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-sm font-semibold text-primary-700 transition hover:gap-2 hover:bg-primary-50 hover:underline dark:text-primary-300 dark:hover:bg-white/5" target="_blank" rel="noopener noreferrer">Open Google Ads</a>
            </div>

            @if ($ads['available'])
                <p class="mt-2 text-xs font-medium uppercase tracking-wide text-gray-400">Source: Google Ads API @if ($ads['refreshed_at']) · refreshed {{ $ads['refreshed_at'] }} @endif</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Ad spend</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ $ads['spend'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Booked jobs from ads</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ number_format($attribution['bookings']) }}</p>
                        <p class="mt-1 text-sm text-gray-500">CRM bookings with a stored Google click ID. Not Google’s conversion count.</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cost per tracked booked job</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ $attribution['cost_per_booking'] ?? '—' }}</p>
                    </div>
                </div>
                <dl class="mt-5 grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 sm:grid-cols-5 dark:border-white/10">
                    @foreach ([
                        'Clicks' => number_format($ads['clicks']),
                        'Impressions' => number_format($ads['impressions']),
                        'Google Ads conversions' => $ads['conversions'] === null ? '—' : rtrim(rtrim(number_format((float) $ads['conversions'], 2, '.', ''), '0'), '.'),
                        'CTR' => $ads['ctr'] === null ? '—' : $ads['ctr'].'%',
                        'Average CPC' => $ads['average_cpc'] ?? '—',
                    ] as $label => $value)
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</dt>
                            <dd class="mt-1 text-lg font-semibold tabular-nums text-gray-950 dark:text-white">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-3 text-sm text-gray-500">{{ $attribution['cost_note'] }} A Google Ads conversion is not a completed clean.</p>

                @if ($ads['actions'] !== [])
                    <ul class="mt-4 space-y-2">
                        @foreach ($ads['actions'] as $action)
                            <li class="flex flex-wrap items-baseline justify-between gap-2 text-sm" wire:key="action-{{ $action['name'] }}">
                                <span class="text-gray-950 dark:text-white">{{ $action['name'] }} <span class="text-gray-500">· {{ $action['kind'] }}</span></span>
                                <span class="tabular-nums text-gray-500">{{ $action['conversions'] }}</span>
                                @if ($action['review'])
                                    <span class="w-full text-xs text-amber-700 dark:text-amber-300">Check this in Google Ads. It may not be a cleaning job.</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($ads['campaigns'] !== [])
                    <div class="mt-6 hidden md:block">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="py-2 font-medium">Campaign</th>
                                    <th class="py-2 font-medium">Status</th>
                                    <th class="py-2 text-right font-medium">Spend</th>
                                    <th class="py-2 text-right font-medium">Clicks</th>
                                    <th class="py-2 text-right font-medium">Ads conversions</th>
                                    <th class="py-2 text-right font-medium">Cost / Ads conversion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ads['campaigns'] as $campaign)
                                    <tr class="border-t border-gray-100 dark:border-white/10" wire:key="campaign-{{ $campaign['id'] }}">
                                        <td class="py-3 font-medium text-gray-950 dark:text-white">{{ $campaign['name'] }}</td>
                                        <td class="py-3 text-gray-500">{{ $campaign['status'] }}</td>
                                        <td class="py-3 text-right tabular-nums">{{ $campaign['spend'] ?? '—' }}</td>
                                        <td class="py-3 text-right tabular-nums">{{ number_format($campaign['clicks']) }}</td>
                                        <td class="py-3 text-right tabular-nums">{{ $campaign['conversions'] }}</td>
                                        <td class="py-3 text-right tabular-nums">{{ $campaign['cost_per_conversion'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="mt-2 text-xs text-gray-500">Booked jobs are not split by campaign. A click ID does not say which campaign it came from.</p>
                    </div>
                    <ul class="mt-6 space-y-3 md:hidden">
                        @foreach ($ads['campaigns'] as $campaign)
                            <li class="rounded-xl border border-gray-100 p-3 dark:border-white/10" wire:key="campaign-card-{{ $campaign['id'] }}">
                                <p class="font-semibold text-gray-950 dark:text-white">{{ $campaign['name'] }}</p>
                                <p class="text-sm text-gray-500">{{ $campaign['status'] }} · {{ $campaign['spend'] ?? '—' }} · {{ number_format($campaign['clicks']) }} clicks · {{ $campaign['conversions'] }} Ads conversions</p>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-sm text-gray-500">No campaign activity in this period.</p>
                @endif
            @elseif ($google['configured'] && ! $google['connected'])
                <a href="{{ route('google-reporting.connect') }}" class="mt-4 inline-flex text-sm font-semibold text-primary-700 hover:underline dark:text-primary-400">Connect Google Ads</a>
            @endif
        </section>

        <section class="{{ $card }}" :style="{ order: index('attribution') }" :class="dragging === 'attribution' ? 'opacity-60' : ''" @dragover="over($event)" @drop="drop('attribution')">
            @include('filament.pages.partials.analytics-move', ['id' => 'attribution', 'label' => 'recorded ad activity'])
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Recorded ad activity on the website</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $attribution['events_note'] }}</p>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">Saved quotes with a click ID</dt><dd class="mt-1 text-xl font-semibold tabular-nums">{{ number_format($attribution['quotes']) }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">Booked jobs with a click ID</dt><dd class="mt-1 text-xl font-semibold tabular-nums">{{ number_format($attribution['bookings']) }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">WhatsApp opens from ad visits</dt><dd class="mt-1 text-xl font-semibold tabular-nums">{{ number_format($attribution['whatsapp']) }}</dd><dd class="mt-1 text-xs text-gray-500">The link was opened. This is not a confirmed chat. Cost per open: {{ $attribution['cost_per_whatsapp'] ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">Phone taps from ad visits</dt><dd class="mt-1 text-xl font-semibold tabular-nums">{{ number_format($attribution['phone']) }}</dd></div>
            </dl>
        </section>

        <section class="{{ $card }}" :style="{ order: index('services') }" :class="dragging === 'services' ? 'opacity-60' : ''" @dragover="over($event)" @drop="drop('services')">
            @include('filament.pages.partials.analytics-move', ['id' => 'services', 'label' => 'cleaning services'])
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Cleaning services</h2>
            <p class="mt-1 text-sm text-gray-500">Agreed value is the price on the booking, not money received. The rate only counts quotes from this period that now have a booking.</p>
            <div class="mt-4 hidden md:block">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="py-2 font-medium">Service</th>
                            <th class="py-2 text-right font-medium">Quotes</th>
                            <th class="py-2 text-right font-medium">Bookings</th>
                            <th class="py-2 text-right font-medium">Quote to booking</th>
                            <th class="py-2 text-right font-medium">Agreed value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dashboard['services'] as $service)
                            <tr class="border-t border-gray-100 dark:border-white/10" wire:key="service-{{ $service['name'] }}">
                                <td class="py-3 font-medium text-gray-950 dark:text-white">{{ $service['name'] }}</td>
                                <td class="py-3 text-right"><a class="inline-flex min-w-8 justify-end rounded-md px-1.5 py-0.5 tabular-nums font-semibold text-primary-700 transition hover:bg-primary-50 hover:underline dark:text-primary-300 dark:hover:bg-white/10" href="{{ $service['quotes_href'] }}">{{ number_format($service['quotes']) }}</a></td>
                                <td class="py-3 text-right"><a class="inline-flex min-w-8 justify-end rounded-md px-1.5 py-0.5 tabular-nums font-semibold text-primary-700 transition hover:bg-primary-50 hover:underline dark:text-primary-300 dark:hover:bg-white/10" href="{{ $service['bookings_href'] }}">{{ number_format($service['bookings']) }}</a></td>
                                <td class="py-3 text-right tabular-nums">{{ $service['rate'] === null ? '—' : $service['rate'].'%' }}</td>
                                <td class="py-3 text-right tabular-nums">{{ $service['agreed'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <ul class="mt-4 space-y-3 md:hidden">
                @foreach ($dashboard['services'] as $service)
                    <li class="rounded-xl border border-gray-100 p-3 dark:border-white/10" wire:key="service-card-{{ $service['name'] }}">
                        <p class="font-semibold text-gray-950 dark:text-white">{{ $service['name'] }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ number_format($service['quotes']) }} quotes · {{ number_format($service['bookings']) }} bookings · {{ $service['rate'] === null ? '—' : $service['rate'].'%' }} · {{ $service['agreed'] ?? '—' }} agreed</p>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="{{ $card }}" :style="{ order: index('sources') }" :class="dragging === 'sources' ? 'opacity-60' : ''" @dragover="over($event)" @drop="drop('sources')">
            @include('filament.pages.partials.analytics-move', ['id' => 'sources', 'label' => 'enquiry sources'])
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Where enquiries were recorded</h2>
            <div class="mt-4 grid gap-6 lg:grid-cols-2">
                @foreach ([
                    'WhatsApp opened, by page' => $activity['whatsapp_pages'],
                    'Phone taps, by page' => $activity['phone_pages'],
                    'Saved quotes, by recorded source' => $activity['quote_sources'],
                ] as $title => $rows)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $title }}</h3>
                        @if ($rows === [])
                            <p class="mt-2 text-sm text-gray-500">Nothing recorded in this period.</p>
                        @else
                            <ul class="mt-2 divide-y divide-gray-100 dark:divide-white/10">
                                @foreach ($rows as $row)
                                    <li class="flex items-baseline justify-between gap-3 py-2 text-sm">
                                        <span class="min-w-0 truncate text-gray-950 dark:text-white">{{ $row['label'] }}</span>
                                        <span class="tabular-nums text-gray-500">{{ number_format($row['count']) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-sm text-gray-500">These counts are events and saved quotes, not unique customers. A page path is not the same thing as where the person came from.</p>
        </section>

        <section class="{{ $card }}" :style="{ order: index('profile') }" :class="dragging === 'profile' ? 'opacity-60' : ''" @dragover="over($event)" @drop="drop('profile')">
            @include('filament.pages.partials.analytics-move', ['id' => 'profile', 'label' => 'Google Business Profile'])
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Google Business Profile</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $activity['profile']['performance']['message'] }}</p>
            <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">Saved Google reviews</dt><dd class="mt-1 text-2xl font-semibold tabular-nums">{{ number_format($activity['profile']['total_reviews']) }}</dd><dd class="mt-1 text-xs text-gray-500">Imported reviews are marked Google Business Profile in Reviews. Reviews typed in here are not included in this count.</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">New in this period</dt><dd class="mt-1 text-2xl font-semibold tabular-nums">{{ number_format($activity['profile']['new_reviews']) }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">Average rating</dt><dd class="mt-1 text-2xl font-semibold tabular-nums">{{ $activity['profile']['total_reviews'] > 0 ? number_format($activity['profile']['average'], 1) : '—' }}</dd></div>
            </dl>
            @if ($activity['profile']['performance']['available'])
                <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                    @foreach ($activity['profile']['performance']['metrics'] as $label => $count)
                        <div><dt class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</dt><dd class="mt-1 text-xl font-semibold tabular-nums">{{ number_format($count) }}</dd></div>
                    @endforeach
                </dl>
            @endif
            <p class="mt-4 text-sm text-gray-500">Age and gender are not collected on this website.</p>
        </section>

        <details class="{{ $card }}" :style="{ order: index('technical') }" :class="dragging === 'technical' ? 'opacity-60' : ''" @dragover="over($event)" @drop="drop('technical')">
            @include('filament.pages.partials.analytics-move', ['id' => 'technical', 'label' => 'technical details'])
            <summary class="cursor-pointer text-sm font-semibold text-gray-950 dark:text-white">Technical details</summary>
            <div class="mt-4 space-y-4 text-sm text-gray-600 dark:text-gray-300">
                <p>Visits are counted until someone declines cookies. Google and Plausible still wait for Accept. Quote form starts only record when a field in the estimate form is focused, so saved quotes can be much higher. Devices and page views below are recorded page views, not unique people.</p>
                <p>Ads customer {{ $google['ads_customer_id'] ? preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $google['ads_customer_id']) : 'not set' }}. Analytics property {{ $google['analytics_property_id'] ?: 'not set' }}. {{ $google['connected'] ? 'OAuth is connected.' : 'OAuth is not connected yet.' }}</p>
                @if ($ads['diagnostics'] !== [])
                    <div>
                        <p class="font-medium text-gray-950 dark:text-white">Search terms with at least 5 clicks and no Ads-reported conversions</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($ads['diagnostics'] as $term)
                                <li>{{ $term['term'] }} · {{ number_format($term['clicks']) }} clicks @if ($term['spend']) · {{ $term['spend'] }} @endif</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="font-medium text-gray-950 dark:text-white">Pages with the most views</p>
                        <ul class="mt-2 space-y-1">
                            @forelse ($activity['top_pages'] as $row)
                                <li class="flex justify-between gap-3"><span class="truncate">{{ $row['label'] }}</span><span class="tabular-nums">{{ number_format($row['count']) }}</span></li>
                            @empty
                                <li>No page views in this period.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div>
                        <p class="font-medium text-gray-950 dark:text-white">Devices</p>
                        <ul class="mt-2 space-y-1">
                            @forelse ($activity['devices'] as $row)
                                <li class="flex justify-between gap-3"><span>{{ $row['label'] }}</span><span class="tabular-nums">{{ number_format($row['count']) }} page views</span></li>
                            @empty
                                <li>No device data yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </details>
        </div>
    </div>
</x-filament-panels::page>
