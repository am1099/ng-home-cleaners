@props([
    'page' => null,
    'title' => null,
    'description' => null,
    'canonical' => null,
    'ogImage' => null,
    'robots' => null,
])

@php
    /** @var \App\Support\Seo\SeoPage|null $page */
    $site = $settings ?? app(\App\Services\SiteSettingsService::class)->get();
    $seo = $page;

    $pageTitle = $seo?->title
        ?? $title
        ?? $site->default_seo_title
        ?? $site->business_name;

    $pageDescription = $seo?->description
        ?? $description
        ?? $site->default_seo_description
        ?? 'Professional house cleaning in Nottingham and surrounding areas.';

    $canonicalUrl = $seo?->canonical ?? $canonical ?? url()->current();
    $image = $seo?->ogImage ?? $ogImage;
    $robotsContent = $seo?->robots ?? $robots ?? 'index,follow';
    $ogType = $seo?->ogType ?? 'website';
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
<meta name="robots" content="{{ $robotsContent }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:site_name" content="{{ $site->business_name }}">
<meta property="og:locale" content="en_GB">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if ($image)
    <meta property="og:image" content="{{ $image }}">
@endif

<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
@if ($image)
    <meta name="twitter:image" content="{{ $image }}">
@endif

@if ($site->favicon_path)
    <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::url($site->favicon_path) }}">
@endif
