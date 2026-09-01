@extends('layouts.public')

@section('content')
    <x-public.section spacing="hero">
        <x-public.container narrow>
            <p class="ng-eyebrow">Error 404</p>
            <h1 class="ng-display ng-display-hero mt-0">We cannot find that page.</h1>
            <p class="ng-body-lg mt-[18px] text-ink-muted">
                The link may be out of date, or the page may have moved. Try one of the options below, or get a free estimate and we will help you from there.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-public.button href="{{ route('home') }}" size="lg">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/></svg>
                    Back to homepage
                </x-public.button>
                <x-public.estimate-cta label="Get a free estimate" size="lg" />
                <x-public.button href="{{ route('contact') }}" variant="outline" size="lg">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0" aria-hidden="true"><path d="M3.5 2.75a.75.75 0 0 0-1.5 0v14.5a.75.75 0 0 0 1.5 0v-4.392l1.657-.348a6.72 6.72 0 0 1 2.844.05l1.232.298a7.72 7.72 0 0 0 3.26.05l2.27-.454a.75.75 0 0 0-.238-1.474l-2.27.454a6.22 6.22 0 0 1-2.626-.04l-1.232-.298a8.22 8.22 0 0 0-3.48-.062l-.997.21V2.75Z"/></svg>
                    Contact us
                </x-public.button>
            </div>
        </x-public.container>
    </x-public.section>

    <x-public.section variant="sunken">
        <x-public.container>
            <h2 class="ng-display text-2xl">Useful links</h2>
            <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <li><a href="{{ route('services') }}" class="ng-link">Cleaning services</a></li>
                <li><a href="{{ route('areas') }}" class="ng-link">Areas we cover</a></li>
                <li><a href="{{ route('about') }}" class="ng-link">About NG Home Cleaners</a></li>
                <li><a href="{{ route('contact') }}" class="ng-link">Contact</a></li>
                <li><a href="{{ route('quote') }}" class="ng-link">Get a free estimate</a></li>
                <li><a href="{{ route('legal.privacy') }}" class="ng-link">Privacy policy</a></li>
            </ul>
        </x-public.container>
    </x-public.section>
@endsection
