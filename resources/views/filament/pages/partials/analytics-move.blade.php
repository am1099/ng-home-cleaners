@props(['id', 'label'])

<div class="mb-4 flex items-center justify-between gap-3">
    <button
        type="button"
        draggable="true"
        class="inline-flex cursor-grab items-center gap-2 rounded-lg px-2 py-1 text-xs font-semibold uppercase tracking-wide text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 active:cursor-grabbing dark:hover:bg-white/10 dark:hover:text-gray-200"
        @dragstart="start('{{ $id }}')"
        @dragend="dragging = null"
        aria-label="Drag {{ $label }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" aria-hidden="true"><path d="M7 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM7 10a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM7 15.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM16 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM16 10a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM16 15.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg>
        Move
    </button>
    <div class="flex gap-1">
        <button type="button" class="rounded-lg px-2 py-1 text-xs font-semibold text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-white/10 dark:hover:text-white" @click="move('{{ $id }}', -1)" aria-label="Move {{ $label }} up">Up</button>
        <button type="button" class="rounded-lg px-2 py-1 text-xs font-semibold text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-white/10 dark:hover:text-white" @click="move('{{ $id }}', 1)" aria-label="Move {{ $label }} down">Down</button>
    </div>
</div>
