@props(['testimonial'])

<x-public.card variant="flat" class="h-full">
    <div class="flex items-center justify-between gap-3">
        <div class="flex gap-1 text-brand-600" aria-label="{{ $testimonial->rating }} out of 5 stars">
            @for ($i = 0; $i < $testimonial->rating; $i++)
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" aria-hidden="true"><path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l3.957 2.385c.714.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd"/></svg>
            @endfor
        </div>
        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-ink-muted" title="Google review">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4 shrink-0" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 21c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 21 12 21z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 1.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            Google
        </span>
    </div>
    <blockquote class="mt-4 text-sm leading-relaxed text-ink">
        <p>&ldquo;{{ $testimonial->review }}&rdquo;</p>
    </blockquote>
    <footer class="mt-5 border-t border-border pt-4">
        <p class="font-semibold text-ink">{{ $testimonial->customer_name }}</p>
        <p class="text-sm text-ink-muted">
            @if ($testimonial->service?->is_active)
                {{ $testimonial->service->name }}
            @endif
            @if ($testimonial->location)
                · {{ $testimonial->location }}
            @endif
            @if ($testimonial->is_demo)
                · Demo review
            @endif
        </p>
    </footer>
</x-public.card>
