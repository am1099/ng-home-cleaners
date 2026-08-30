/**
 * Provider-agnostic analytics queue.
 * Events are buffered on window.ngAnalyticsQueue and dispatched as CustomEvents.
 * No third-party tracker is loaded unless ANALYTICS is configured.
 */
(function () {
    const queue = (window.ngAnalyticsQueue = window.ngAnalyticsQueue || []);
    const driver = document.documentElement.dataset.analyticsDriver || '';
    const enabled = document.documentElement.dataset.analyticsEnabled === '1';

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

        if (!enabled) {
            return;
        }

        if (driver === 'console' && typeof console !== 'undefined') {
            console.info('[ng:analytics]', entry.event, entry.payload);
        }
    };
})();
