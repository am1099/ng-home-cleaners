@php($report = $this->report())

<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <p class="max-w-2xl text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                Visitors and page views are counted after someone accepts cookies. A WhatsApp or phone conversion is counted only when the link is opened, not when the button is merely hovered.
            </p>
            <div class="flex gap-2">
                @foreach ([7 => '7 days', 30 => '30 days', 90 => '90 days'] as $value => $label)
                    <x-filament::button
                        size="sm"
                        :color="$report['days'] === $value ? 'primary' : 'gray'"
                        wire:click="range({{ $value }})"
                    >
                        {{ $label }}
                    </x-filament::button>
                @endforeach
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                ['Visitors', $report['visitors'], 'People who accepted cookies'],
                ['Page views', $report['views'], 'Pages opened'],
                ['WhatsApp opened', $report['whatsapp'], 'Chat link actually opened'],
                ['Phone taps', $report['phone'], 'Call link opened'],
                ['Quote form started', $report['form_starts'], 'Someone began the form'],
                ['Quotes submitted', $report['quotes'], 'Saved enquiries'],
            ] as [$label, $count, $hint])
                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ number_format($count) }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
                </section>
            @endforeach
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @include('filament.pages.partials.analytics-list', ['title' => 'Pages with the most views', 'rows' => $report['top_pages'], 'empty' => 'No page views in this period yet.'])
            @include('filament.pages.partials.analytics-list', ['title' => 'WhatsApp opened, by page', 'rows' => $report['whatsapp_pages'], 'empty' => 'No WhatsApp chats opened in this period.'])
            @include('filament.pages.partials.analytics-list', ['title' => 'Where visitors came from', 'rows' => $report['traffic'], 'empty' => 'No consented visits in this period.'])
            @include('filament.pages.partials.analytics-list', ['title' => 'Devices', 'rows' => $report['devices'], 'empty' => 'No device data yet.'])
            @include('filament.pages.partials.analytics-list', ['title' => 'Submitted quotes, by source', 'rows' => $report['quote_sources'], 'empty' => 'No quotes submitted in this period.'])
            @include('filament.pages.partials.analytics-list', ['title' => 'Phone taps, by page', 'rows' => $report['phone_pages'], 'empty' => 'No phone taps in this period.'])
        </div>

        @php($google = $this->googleAccount())
        <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Linked Google accounts</h2>
            <p class="mt-1 text-sm text-gray-500">
                Ads customer {{ $google['ads_customer_id'] ? preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $google['ads_customer_id']) : 'not set' }}.
                Analytics property {{ $google['analytics_property_id'] ?: 'not set' }}.
                {{ $google['connected'] ? 'OAuth is connected.' : 'OAuth is not connected yet. Use Connect Google Ads and Analytics above, then sign in as the account that owns NG Home Cleaners.' }}
            </p>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Google Ads</h2>
            <p class="mt-1 text-sm text-gray-500">These are actions on your website that started from an ad click. Spend and impressions stay in Google Ads.</p>
            <dl class="mt-4 grid gap-4 sm:grid-cols-4">
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">Ad visits</dt><dd class="mt-1 text-2xl font-semibold">{{ number_format($report['ads']['visits']) }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">WhatsApp from ads</dt><dd class="mt-1 text-2xl font-semibold">{{ number_format($report['ads']['whatsapp']) }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">Quotes from ads</dt><dd class="mt-1 text-2xl font-semibold">{{ number_format($report['ads']['quotes']) }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">Bookings from ads</dt><dd class="mt-1 text-2xl font-semibold">{{ number_format($report['ads']['booked']) }}</dd></div>
            </dl>
            <p class="mt-4 text-sm"><a class="font-semibold text-primary-600" href="https://ads.google.com" target="_blank" rel="noopener noreferrer">Open Google Ads</a></p>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Google Business Profile</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $report['profile']['performance']['message'] }}</p>
            <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">Saved Google reviews</dt><dd class="mt-1 text-2xl font-semibold">{{ number_format($report['profile']['total_reviews']) }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">New in this period</dt><dd class="mt-1 text-2xl font-semibold">{{ number_format($report['profile']['new_reviews']) }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500">Average rating</dt><dd class="mt-1 text-2xl font-semibold">{{ $report['profile']['total_reviews'] > 0 ? number_format($report['profile']['average'], 1) : '—' }}</dd></div>
            </dl>
            @if ($report['profile']['performance']['available'])
                <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                    @foreach ($report['profile']['performance']['metrics'] as $label => $count)
                        <div><dt class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</dt><dd class="mt-1 text-2xl font-semibold">{{ number_format($count) }}</dd></div>
                    @endforeach
                </dl>
            @endif
            <p class="mt-4 text-sm text-gray-500">Age and gender are not collected on this website. Google only shows those inside Google Analytics, and this CRM is not connected to that report.</p>
        </section>
    </div>
</x-filament-panels::page>
