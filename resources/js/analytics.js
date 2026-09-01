/**
 * Provider-agnostic analytics queue with optional Plausible after consent.
 */
(function () {
    const CONSENT_KEY = 'ng-cookie-consent';
    const queue = (window.ngAnalyticsQueue = window.ngAnalyticsQueue || []);
    const root = document.documentElement;
    const driver = root.dataset.analyticsDriver || '';
    const enabled = root.dataset.analyticsEnabled === '1';
    const plausibleDomain = root.dataset.plausibleDomain || '';
    const plausibleSrc = root.dataset.plausibleSrc || 'https://plausible.io/js/script.js';

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
        window.dispatchEvent(new CustomEvent('ng:consent', { detail: { value } }));
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
        };
    };

    if (hasConsent()) {
        root.dataset.analyticsConsent = 'accepted';
        loadPlausible();
    } else if (consentValue() === 'declined') {
        root.dataset.analyticsConsent = 'declined';
    }
})();
