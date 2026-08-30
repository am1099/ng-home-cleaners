@props(['faqs'])

@if ($faqs->isNotEmpty())
    <div {{ $attributes->class(['mx-auto max-w-3xl divide-y divide-border rounded-[var(--radius-card)] border border-border bg-surface-card']) }}>
        @foreach ($faqs as $faq)
            <details class="group px-5 py-4">
                <summary class="cursor-pointer list-none font-semibold text-ink marker:content-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 [&::-webkit-details-marker]:hidden">
                    <span class="flex items-start justify-between gap-4">
                        {{ $faq->question }}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="mt-0.5 size-5 shrink-0 text-brand-600 transition-transform group-open:rotate-180" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.08 1.04l-4.24 4.25a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                    </span>
                </summary>
                <p class="mt-3 text-sm leading-relaxed text-ink-muted">{{ $faq->answer }}</p>
            </details>
        @endforeach
    </div>
@else
    <x-public.empty-state title="No FAQs yet" message="Common questions will appear here once they are added in the admin." />
@endif
