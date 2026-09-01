<!DOCTYPE html>
<html
    lang="en-GB"
    data-analytics-enabled="{{ config('analytics.enabled') ? '1' : '0' }}"
    data-analytics-driver="{{ config('analytics.driver') ?? '' }}"
    data-plausible-domain="{{ config('analytics.plausible.domain') ?? '' }}"
    data-plausible-src="{{ config('analytics.plausible.script_url') ?? '' }}"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-public.seo :page="$seo ?? null" />

    @foreach (($jsonLd ?? []) as $block)
        <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) !!}</script>
    @endforeach

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
    </noscript>

    @livewireScriptConfig
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="flex min-h-screen flex-col @yield('body_class')">
    <x-public.header :show-gallery-nav="$showGalleryNav ?? false" />

    @isset($seo)
        <x-public.breadcrumbs :items="$seo->breadcrumbs" />
    @endisset

    <main id="main-content" class="flex flex-1 flex-col" tabindex="-1">
        @yield('content')
    </main>

    <x-public.footer :show-gallery-nav="$showGalleryNav ?? false" />

    @unless(request()->routeIs('quote'))
        <x-public.mobile-sticky-cta />
    @endunless

    <x-public.cookie-consent />

    @stack('scripts')
</body>
</html>
