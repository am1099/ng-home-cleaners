@php
    $googleAdsId = config('analytics.google_ads_id');
    $googleAdsId = is_string($googleAdsId) && preg_match('/^AW-\d+$/', $googleAdsId) === 1
        ? $googleAdsId
        : null;
@endphp

@if ($googleAdsId)
    {{-- Google tag (gtag.js). Consent defaults to denied until the cookie banner is accepted. --}}
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        (function () {
            var granted = false;
            try {
                granted = window.localStorage.getItem('ng-cookie-consent') === 'accepted';
            } catch (e) {}
            var state = granted ? 'granted' : 'denied';
            gtag('consent', 'default', {
                ad_storage: state,
                ad_user_data: state,
                ad_personalization: state,
                analytics_storage: state
            });
        })();
        gtag('js', new Date());
        gtag('config', @json($googleAdsId));
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAdsId }}"></script>
@endif
