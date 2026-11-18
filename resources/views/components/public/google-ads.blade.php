@php
    $googleAdsId = config('analytics.google_ads_id');
    $googleAdsId = is_string($googleAdsId) && preg_match('/^AW-\d+$/', $googleAdsId) === 1
        ? $googleAdsId
        : null;

    $whatsappSendTo = config('analytics.google_ads_whatsapp_send_to');
    $whatsappSendTo = is_string($whatsappSendTo) && preg_match('/^AW-\d+\/[A-Za-z0-9_-]+$/', $whatsappSendTo) === 1
        ? $whatsappSendTo
        : null;

    $phoneSendTo = config('analytics.google_ads_phone_send_to');
    $phoneSendTo = is_string($phoneSendTo) && preg_match('/^AW-\d+\/[A-Za-z0-9_-]+$/', $phoneSendTo) === 1
        ? $phoneSendTo
        : null;
@endphp

@if ($googleAdsId)
    {{-- Google tag (gtag.js). Consent Mode v2 defaults to denied until the cookie banner is accepted. --}}
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        (function () {
            var granted = false;
            try {
                granted = window.localStorage.getItem('ng-cookie-consent') === 'accepted';
            } catch (e) {}

            gtag('consent', 'default', {
                ad_storage: 'denied',
                ad_user_data: 'denied',
                ad_personalization: 'denied',
                analytics_storage: 'denied',
                wait_for_update: 500
            });

            if (granted) {
                gtag('consent', 'update', {
                    ad_storage: 'granted',
                    ad_user_data: 'granted',
                    ad_personalization: 'granted',
                    analytics_storage: 'granted'
                });
            }
        })();
        gtag('js', new Date());
        gtag('config', @json($googleAdsId));
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAdsId }}"></script>
@endif
