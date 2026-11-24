/**
 * Provider-agnostic analytics queue with optional Plausible after consent,
 * plus Google Ads conversions. Consent Mode v2 (default denied) decides
 * whether a conversion is cookieless or stored. Do not also gate the
 * gtag conversion call on the banner, or Google never receives the ping.
 */
(function () {
    const CONSENT_KEY = 'ng-cookie-consent';
    const queue = (window.ngAnalyticsQueue = window.ngAnalyticsQueue || []);
    const root = document.documentElement;
    const driver = root.dataset.analyticsDriver || '';
    const enabled = root.dataset.analyticsEnabled === '1';
    const plausibleDomain = root.dataset.plausibleDomain || '';
    const plausibleSrc = root.dataset.plausibleSrc || 'https://plausible.io/js/script.js';
    const whatsappSendTo = root.dataset.googleAdsWhatsappSendTo || '';
    const phoneSendTo = root.dataset.googleAdsPhoneSendTo || '';
    const conversionDedupeMs = 2500;
    const lastConversionAt = Object.create(null);

    const adsEventMap = {
        whatsapp_clicked: 'whatsapp',
        quote_whatsapp_clicked: 'whatsapp',
        whatsapp_quote: 'whatsapp',
        phone_clicked: 'phone',
    };

    function consentValue() {
        try {
            return window.localStorage.getItem(CONSENT_KEY);
        } catch {
            return null;
        }
    }

    function hasConsent() {
        return consentValue() === 'accepted';
    }

    function setConsent(value) {
        try {
            window.localStorage.setItem(CONSENT_KEY, value);
        } catch {
            // Ignore storage failures (private mode).
        }

        root.dataset.analyticsConsent = value;
        writeConsentCookie(value);
        window.dispatchEvent(new CustomEvent('ng:consent', { detail: { value } }));
        updateGoogleConsent(value === 'accepted');
    }

    function clearConsentChoice() {
        try {
            window.localStorage.removeItem(CONSENT_KEY);
        } catch {
            // Ignore storage failures (private mode).
        }

        root.dataset.analyticsConsent = '';
        writeConsentCookie('declined');
        clearAttributionCookie();
        updateGoogleConsent(false);
        window.dispatchEvent(new CustomEvent('ng:consent', { detail: { value: null } }));
        window.dispatchEvent(new CustomEvent('ng:consent-reopen'));
    }

    function updateGoogleConsent(granted) {
        if (typeof window.gtag !== 'function') {
            return;
        }

        const state = granted ? 'granted' : 'denied';

        window.gtag('consent', 'update', {
            ad_storage: state,
            ad_user_data: state,
            ad_personalization: state,
            analytics_storage: state,
        });
    }

    function sendToDriver(event, payload) {
        if (!enabled || !hasConsent()) {
            return;
        }

        if (driver === 'console' && typeof console !== 'undefined') {
            console.info('[ng:analytics]', event, payload);
        }

        if (driver === 'plausible' && typeof window.plausible === 'function') {
            window.plausible(event, { props: payload || {} });
        }
    }

    function loadPlausible() {
        if (!enabled || driver !== 'plausible' || !hasConsent() || !plausibleDomain) {
            return;
        }

        if (document.querySelector('script[data-ng-plausible]')) {
            return;
        }

        window.plausible = window.plausible || function plausible() {
            (window.plausible.q = window.plausible.q || []).push(arguments);
        };

        const script = document.createElement('script');
        script.defer = true;
        script.dataset.domain = plausibleDomain;
        script.dataset.ngPlausible = '1';
        script.src = plausibleSrc;
        document.head.appendChild(script);
    }

    function writeConsentCookie(value) {
        const secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `ng_cookie_consent=${value}; Max-Age=31536000; Path=/; SameSite=Lax${secure}`;
    }

    function clearAttributionCookie() {
        document.cookie = 'ng_attribution=; Max-Age=0; Path=/; SameSite=Lax';
    }

    function isWhatsAppHref(href) {
        if (!href) {
            return false;
        }

        return /(?:^https?:\/\/)?(?:wa\.me\/|api\.whatsapp\.com\/)/i.test(href)
            || href.toLowerCase().includes('whatsapp.com/send');
    }

    function fireGoogleAdsConversion(kind) {
        if (typeof window.gtag !== 'function') {
            return false;
        }

        const sendTo = kind === 'phone' ? phoneSendTo : whatsappSendTo;

        if (!sendTo) {
            return false;
        }

        const now = Date.now();
        const previous = lastConversionAt[kind] || 0;

        if (now - previous < conversionDedupeMs) {
            return false;
        }

        lastConversionAt[kind] = now;
        window.gtag('event', 'conversion', {
            send_to: sendTo,
            transport_type: 'beacon',
        });

        return true;
    }

    window.ngTrack = function ngTrack(event, payload = {}) {
        if (!event) {
            return;
        }

        const entry = {
            event,
            payload: payload || {},
            at: Date.now(),
        };

        queue.push(entry);
        window.dispatchEvent(new CustomEvent('ng:analytics', { detail: entry }));
        sendToDriver(entry.event, entry.payload);

        const conversionKind = adsEventMap[entry.event];

        if (conversionKind) {
            fireGoogleAdsConversion(conversionKind);
        }
    };

    window.ngCookieConsent = function ngCookieConsent() {
        return {
            visible: consentValue() === null,
            accept() {
                setConsent('accepted');
                this.visible = false;
                loadPlausible();
                queue.forEach((entry) => sendToDriver(entry.event, entry.payload));
            },
            decline() {
                setConsent('declined');
                this.visible = false;
            },
            reopen() {
                clearConsentChoice();
                this.visible = true;
            },
            init() {
                window.addEventListener('ng:consent-reopen', () => {
                    this.visible = true;
                });
            },
        };
    };

    window.ngReopenCookieConsent = function ngReopenCookieConsent() {
        clearConsentChoice();

        document.querySelectorAll('[aria-labelledby="cookie-consent-title"]').forEach((el) => {
            const data = el._x_dataStack && el._x_dataStack[0];

            if (data) {
                data.visible = true;
            }
        });
    };

    document.addEventListener('click', (event) => {
        const tracked = event.target instanceof Element
            ? event.target.closest('[data-ng-track]')
            : null;

        if (tracked) {
            window.ngTrack(tracked.getAttribute('data-ng-track'), {
                location: tracked.getAttribute('data-ng-track-location') || undefined,
            });
        }

        const anchor = event.target instanceof Element
            ? event.target.closest('a[href]')
            : null;

        if (!anchor) {
            return;
        }

        const href = anchor.getAttribute('href') || '';

        if (href.startsWith('tel:')) {
            window.ngTrack('phone_clicked', { location: 'tel_link' });

            return;
        }

        if (isWhatsAppHref(href)) {
            window.ngTrack('whatsapp_clicked', { location: 'whatsapp_link' });
        }
    }, true);

    if (hasConsent()) {
        root.dataset.analyticsConsent = 'accepted';
        writeConsentCookie('accepted');
        loadPlausible();
    } else if (consentValue() === 'declined') {
        root.dataset.analyticsConsent = 'declined';
        writeConsentCookie('declined');
    }
})();
