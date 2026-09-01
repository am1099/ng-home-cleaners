@props([
    'id',
    'min' => null,
    'placeholder' => 'Choose a date',
    'model' => 'preferredDate',
    'value' => null,
])

@php
    $buttonClass = 'ng-field mt-1 flex cursor-pointer items-center justify-between text-left';
    $initial = $value ?? $this->preferredDate ?? '';
@endphp

<div
    x-data="ngDatePicker({ min: @js($min), initial: @js($initial), model: @js($model) })"
    x-on:keydown.escape.window="open = false"
    x-on:click.outside="open = false"
    class="relative"
>
    <button
        type="button"
        id="{{ $id }}"
        class="{{ $buttonClass }}"
        x-on:click="open = ! open"
        x-bind:aria-expanded="open.toString()"
        aria-haspopup="dialog"
        aria-controls="{{ $id }}-calendar"
    >
        <span x-text="label || @js($placeholder)" x-bind:class="label ? 'text-ink' : 'text-ink-subtle'"></span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0 text-brand-700" aria-hidden="true"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd"/></svg>
    </button>

    <div
        id="{{ $id }}-calendar"
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-30 mt-2 w-80 rounded-[var(--radius-lg)] border border-border bg-surface-card p-4 shadow-[var(--shadow-elevated)]"
        role="dialog"
        aria-label="Choose a preferred date"
    >
        <div class="mb-3 flex items-center justify-between">
            <button type="button" class="cursor-pointer rounded-[var(--radius-md)] p-2 text-ink hover:bg-brand-50" x-on:click="prevMonth()" aria-label="Previous month">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
            </button>
            <p class="text-sm font-semibold text-ink" x-text="monthLabel"></p>
            <button type="button" class="cursor-pointer rounded-[var(--radius-md)] p-2 text-ink hover:bg-brand-50" x-on:click="nextMonth()" aria-label="Next month">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
            </button>
        </div>
        <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-ink-muted">
            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
        </div>
        <div class="mt-2 grid grid-cols-7 gap-1">
            <template x-for="day in days" :key="day.iso">
                <button
                    type="button"
                    class="min-h-9 rounded-[var(--radius-md)] text-sm"
                    x-bind:class="{
                        'text-ink-subtle': ! day.inMonth,
                        'bg-brand-700 text-white': day.iso === iso,
                        'hover:bg-brand-50': day.inMonth && day.iso !== iso && ! day.disabled,
                        'cursor-not-allowed opacity-40': day.disabled,
                        'cursor-pointer': ! day.disabled,
                    }"
                    x-bind:disabled="day.disabled"
                    x-on:click="select(day)"
                    x-text="day.date"
                ></button>
            </template>
        </div>
        <div class="mt-3 flex justify-between border-t border-border pt-3">
            <button type="button" class="cursor-pointer text-sm font-semibold text-ink-muted hover:text-ink" x-on:click="clear()">Clear</button>
            <button type="button" class="cursor-pointer text-sm font-semibold text-brand-800 hover:text-brand-600" x-on:click="today()">Today</button>
        </div>
    </div>
</div>
