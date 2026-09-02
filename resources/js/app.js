import './bootstrap';
import './analytics';
import { initMobileNav } from './mobile-nav';
import { initImageLightbox } from './lightbox';
import { ngDatePicker } from './date-picker';
import { ngGalleryCarousel } from './gallery-carousel';
import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';

/**
 * Livewire's ESM bundle already registers Alpine plugins (including persist
 * and intersect). Starting Livewire twice, or starting it before
 * @livewireScriptConfig is on the page, throws "$persist" / ".uri" errors
 * and kills wire:model / wire:click.
 */
Alpine.data('ngDatePicker', ngDatePicker);
Alpine.data('ngGalleryCarousel', ngGalleryCarousel);
Alpine.data('ngStickyEstimateBar', () => ({
    hide: false,
    init() {
        const target = document.getElementById('when-details');

        if (! target || typeof IntersectionObserver === 'undefined') {
            return;
        }

        const observer = new IntersectionObserver(([entry]) => {
            this.hide = entry.isIntersecting;
        }, { threshold: 0.12 });

        observer.observe(target);
    },
}));

if (typeof window.ngCookieConsent === 'function') {
    Alpine.data('ngCookieConsent', window.ngCookieConsent);
}

window.Alpine = Alpine;

function startLivewireOnce() {
    if (window.__ngLivewireStarted) {
        return;
    }

    if (! window.livewireScriptConfig?.uri) {
        return;
    }

    window.__ngLivewireStarted = true;
    Livewire.start();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startLivewireOnce);
} else {
    startLivewireOnce();
}

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initImageLightbox();
});
