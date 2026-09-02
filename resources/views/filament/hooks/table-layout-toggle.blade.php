{{-- Table / card layout toggle for Filament record tables --}}
<div
    x-data="{
        storageKey: 'ng.admin.tableLayout',
        layout: 'table',
        forced: false,
        mobileQuery: null,
        init() {
            this.mobileQuery = window.matchMedia('(max-width: 639px)')
            const saved = window.localStorage.getItem(this.storageKey)

            if (saved === 'cards' || saved === 'table') {
                this.layout = saved
                this.forced = true
            } else {
                this.layout = this.mobileQuery.matches ? 'cards' : 'table'
                this.forced = false
            }

            this.apply()

            const reapply = () => this.$nextTick(() => this.apply())
            document.addEventListener('livewire:navigated', reapply)

            if (window.Livewire) {
                Livewire.hook('morph.updated', reapply)
            }

            this.mobileQuery.addEventListener('change', (event) => {
                if (this.forced) {
                    return
                }

                this.layout = event.matches ? 'cards' : 'table'
                this.apply()
            })
        },
        isActive(layout) {
            return this.layout === layout
        },
        setLayout(layout) {
            this.layout = layout
            this.forced = true
            window.localStorage.setItem(this.storageKey, layout)
            this.apply()
        },
        apply() {
            document
                .querySelectorAll('.fi-ta-table.fi-ta-table-stacked-on-mobile')
                .forEach((table) => {
                    table.classList.toggle('ng-force-cards', this.forced && this.layout === 'cards')
                    table.classList.toggle('ng-force-table', this.forced && this.layout === 'table')
                })
        },
    }"
    class="ng-table-layout-toggle flex shrink-0 items-center gap-x-1 rounded-lg bg-gray-50 p-1 dark:bg-white/5"
    title="Switch between card and table layout"
>
    <button
        type="button"
        class="inline-flex items-center justify-center gap-1 rounded-md px-2.5 py-1.5 text-sm font-semibold transition"
        x-bind:class="isActive('cards')
            ? 'bg-white text-gray-950 shadow-sm dark:bg-white/10 dark:text-white'
            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
        x-on:click="setLayout('cards')"
        aria-label="Card view"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 8.25 20.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
        </svg>
        <span class="hidden sm:inline">Cards</span>
    </button>
    <button
        type="button"
        class="inline-flex items-center justify-center gap-1 rounded-md px-2.5 py-1.5 text-sm font-semibold transition"
        x-bind:class="isActive('table')
            ? 'bg-white text-gray-950 shadow-sm dark:bg-white/10 dark:text-white'
            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
        x-on:click="setLayout('table')"
        aria-label="Table view"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H11.625c-.621 0-1.125.504-1.125 1.125m11.25 0v3.75m0-3.75h-7.5c-.621 0-1.125.504-1.125 1.125M3.375 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h7.5c.621 0 1.125-.504 1.125-1.125M3.375 5.625h7.5c.621 0 1.125.504 1.125 1.125v3.75M14.25 18.375v-3.75c0-.621.504-1.125 1.125-1.125h3.75" />
        </svg>
        <span class="hidden sm:inline">Table</span>
    </button>
</div>
