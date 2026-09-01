@extends('layouts.public')

@section('content')
    {{-- Hero — Heartfelt-style: copy left, circular image right --}}
    <x-public.section spacing="hero" class="relative overflow-hidden bg-gradient-to-br from-brand-50 via-surface-page to-surface-sunken">
        <x-public.container>
            <div class="grid items-center gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:gap-12">
                <div class="relative z-10">
                    <p class="ng-eyebrow">Cleaning · {{ $settings->service_area_summary }}</p>
                    <h1 class="ng-display ng-display-hero mt-0 max-w-xl text-brand-950">
                        {{ $settings->home_hero_title ?? 'There are better uses for a Saturday morning.' }}
                    </h1>
                    <p class="ng-body-lg mt-[18px] max-w-lg text-ink-muted">
                        {{ $settings->home_hero_subtitle ?? 'Your home cleaned by a vetted, DBS-checked cleaner working to a written standard, with a fixed price agreed before we start.' }}
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <x-public.button
                            href="{{ $settings->phoneTel() }}"
                            variant="phone"
                            size="lg"
                            class="rounded-full"
                            onclick="window.ngTrack && window.ngTrack('phone_clicked', { location: 'home_hero' })"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 15.352V16.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd"/></svg>
                            {{ $settings->phoneDisplay() }}
                        </x-public.button>
                        <x-public.estimate-cta
                            label="Free quote"
                            size="lg"
                            variant="outline"
                            class="rounded-full"
                            location="home_hero"
                        />
                        <x-public.whatsapp-cta
                            label="WhatsApp"
                            size="lg"
                            class="rounded-full"
                            onclick="window.ngTrack && window.ngTrack('whatsapp_clicked', { location: 'home_hero' })"
                        />
                    </div>
                    @if ($settings->guarantee_statement)
                        <p class="mt-5 max-w-xl text-sm text-ink-muted">{{ $settings->guarantee_statement }}</p>
                    @endif
                    <div class="mt-6">
                        <x-public.trust-strip />
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                    <div class="relative aspect-square w-full max-w-[28rem] justify-self-end lg:ml-auto">
                        {{-- Brand arc --}}
                        <div class="pointer-events-none absolute inset-[-0.35rem] rounded-full border-[6px] border-brand-600/90" aria-hidden="true"></div>
                        <div class="pointer-events-none absolute -bottom-2 -left-2 size-[42%] rounded-full border-[5px] border-brand-600/40" aria-hidden="true"></div>
                        <div class="relative size-full overflow-hidden rounded-full bg-brand-100 shadow-[var(--shadow-elevated)] ring-4 ring-white">
                            @if ($settings->home_hero_image)
                                <img
                                    src="{{ \App\Support\Media::url($settings->home_hero_image) }}"
                                    alt="{{ $settings->home_hero_image_alt ?: ($settings->home_hero_title ?: 'NG Home Cleaners') }}"
                                    class="size-full object-cover"
                                    width="640"
                                    height="640"
                                    fetchpriority="high"
                                >
                            @else
                                <div class="flex size-full flex-col items-center justify-center gap-3 bg-gradient-to-br from-brand-100 to-brand-50 p-8 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-14 text-brand-700" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"/></svg>
                                    <p class="text-sm font-medium text-brand-900">Upload a hero photo in Site settings → Homepage</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-public.container>
    </x-public.section>

    {{-- Services --}}
    <x-public.section variant="sunken">
        <x-public.container>
            <x-public.heading
                eyebrow="What we clean"
                title="Homes and small commercial premises across Nottingham"
                subtitle="Whatever you book, the same checklist and the same standard."
            />
            @if ($services->isNotEmpty())
                <div class="mt-10 grid items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($services as $service)
                        <x-public.service-card :service="$service" />
                    @endforeach
                </div>
                <p class="mt-8">
                    <a href="{{ route('services') }}" class="ng-link">Compare all cleaning services</a>
                </p>
            @endif
        </x-public.container>
    </x-public.section>

    {{-- Why choose --}}
    @if (! empty($settings->why_choose_items))
        <x-public.section>
            <x-public.container>
                <x-public.heading
                    eyebrow="Why homeowners choose us"
                    title="The part you only notice when it goes wrong"
                    subtitle="Cover, someone to call, and insurance: the things that matter when a clean does not go to plan."
                    align="center"
                    class="mx-auto"
                />
                <ul class="mx-auto mt-12 grid max-w-5xl gap-6 sm:grid-cols-3">
                    @foreach ($settings->why_choose_items as $item)
                        <li class="rounded-[var(--radius-card)] border border-border bg-surface-card p-6 shadow-[var(--shadow-card)]">
                            <h3 class="ng-display text-xl text-ink">{{ $item['title'] ?? '' }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-ink-muted">{{ $item['body'] ?? '' }}</p>
                        </li>
                    @endforeach
                </ul>
            </x-public.container>
        </x-public.section>
    @endif

    {{-- How it works --}}
    @if (! empty($settings->how_it_works_steps))
        <x-public.section variant="sunken">
            <x-public.container>
                <x-public.heading eyebrow="How it works" title="Simple from first enquiry to first clean" align="center" class="mx-auto" />
                <ol class="mx-auto mt-12 grid max-w-5xl gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($settings->how_it_works_steps as $index => $step)
                        <li class="rounded-[var(--radius-card)] border border-border bg-surface-card p-6 shadow-[var(--shadow-card)]">
                            <p class="text-sm font-semibold text-brand-600">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</p>
                            <h3 class="ng-display mt-3 text-xl">{{ $step['title'] ?? '' }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-ink-muted">{{ $step['body'] ?? '' }}</p>
                        </li>
                    @endforeach
                </ol>
            </x-public.container>
        </x-public.section>
    @endif

    {{-- Recent work (before & after) --}}
    @if ($settings->show_recent_work && $recentWorks->isNotEmpty())
        <x-public.recent-work :items="$recentWorks" />
    @endif

    {{-- Gallery --}}
    @if ($galleryItems->isNotEmpty())
        <x-public.section id="gallery" class="scroll-mt-[calc(var(--height-header)+1rem)]">
            <x-public.container>
                <x-public.heading
                    eyebrow="Gallery"
                    title="A look at the finish"
                    subtitle="Tap any photo to view it full size."
                    align="center"
                    class="mx-auto"
                />
                <div class="mt-10">
                    <x-public.gallery-grid :items="$galleryItems" />
                </div>
            </x-public.container>
        </x-public.section>
    @endif

    {{-- Areas / coverage --}}
    <x-public.coverage-panel
        :areas="$areas"
        :summary="$settings->service_area_summary"
    />

    {{-- Testimonials --}}
    @if ($testimonials->isNotEmpty())
        <x-public.section variant="sunken">
            <x-public.container>
                <x-public.heading eyebrow="What customers say" title="Reviews from local customers" align="center" class="mx-auto" />
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <x-public.testimonial-card :testimonial="$testimonial" />
                    @endforeach
                </div>
                @if ($settings->google_business_url)
                    <p class="mt-8 text-center">
                        <a href="{{ $settings->google_business_url }}" target="_blank" rel="noopener noreferrer" class="ng-link">Read more reviews on Google</a>
                    </p>
                @endif
            </x-public.container>
        </x-public.section>
    @endif

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <x-public.section>
            <x-public.container>
                <x-public.heading
                    eyebrow="FAQ"
                    title="The things people ask first"
                    subtitle="Anything else, ring {{ $settings->phoneDisplay() }}. You will get a straight answer, not a sales call."
                    align="center"
                    class="mx-auto"
                    style="justify-self: center;"
                />
                <div class="mt-10">
                    <x-public.faq-list :faqs="$faqs" />
                </div>
            </x-public.container>
        </x-public.section>
    @endif

    <x-public.final-cta />

    {{-- Fill leftover main height (flex-1) with ink so mobile never shows a white strip above the footer --}}
    <div class="flex-1 bg-surface-ink" aria-hidden="true"></div>

@endsection
