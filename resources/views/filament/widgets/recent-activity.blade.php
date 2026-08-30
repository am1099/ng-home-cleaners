<x-filament-widgets::widget class="fi-wi-recent-activity">
    <x-filament::section>
        <x-slot name="heading">
            Recent activity
        </x-slot>

        <x-slot name="description">
            Latest leads, bookings, and payments
        </x-slot>

        @php
            $activities = $this->getActivities();
        @endphp

        @if (count($activities) === 0)
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Activity will appear here as leads, bookings, and payments are recorded.
            </p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($activities as $activity)
                    <li class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            @if ($activity['url'])
                                <a href="{{ $activity['url'] }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">
                                    {{ $activity['title'] }}
                                </a>
                            @else
                                <div class="text-sm font-medium text-gray-950 dark:text-white">
                                    {{ $activity['title'] }}
                                </div>
                            @endif
                            <div class="mt-0.5 truncate text-sm text-gray-500 dark:text-gray-400">
                                {{ $activity['subtitle'] }}
                            </div>
                        </div>
                        <div class="shrink-0 text-xs text-gray-400 dark:text-gray-500">
                            {{ $activity['at']?->diffForHumans() }}
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
