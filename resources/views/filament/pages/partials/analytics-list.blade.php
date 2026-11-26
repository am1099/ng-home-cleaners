<section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
    <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $title }}</h2>
    @if ($rows === [])
        <p class="mt-3 text-sm text-gray-500">{{ $empty }}</p>
    @else
        @php($max = max(1, collect($rows)->max('count')))
        <ul class="mt-4 space-y-3">
            @foreach ($rows as $row)
                <li>
                    <div class="flex items-baseline justify-between gap-3 text-sm">
                        <span class="min-w-0 truncate font-medium text-gray-950 dark:text-white">{{ $row['label'] }}</span>
                        <span class="shrink-0 tabular-nums text-gray-500">{{ number_format($row['count']) }}</span>
                    </div>
                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                        <div class="h-full rounded-full bg-primary-600" style="width: {{ (int) round(($row['count'] / $max) * 100) }}%"></div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>
