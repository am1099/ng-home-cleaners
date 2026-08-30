/**
 * Accessible right-drawer mobile navigation for the public site header.
 */
export function initMobileNav() {
    const root = document.querySelector('[data-mobile-nav]');

    if (! root) {
        return;
    }

    const toggle = root.querySelector('[data-mobile-nav-toggle]');
    const panel = root.querySelector('[data-mobile-nav-panel]');
    const dialog = root.querySelector('[data-mobile-nav-dialog]');
    const closeButtons = root.querySelectorAll('[data-mobile-nav-close], [data-mobile-nav-backdrop]');
    const links = root.querySelectorAll('[data-mobile-nav-link]');
    const iconMenu = root.querySelector('[data-icon-menu]');
    const iconClose = root.querySelector('[data-icon-close]');

    if (! toggle || ! panel || ! dialog) {
        return;
    }

    const focusableSelector = 'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])';
    const motionMs = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 220;

    let previouslyFocused = null;
    let closeTimer = null;

    const isOpen = () => panel.getAttribute('data-open') === 'true';

    const getFocusableElements = () => Array.from(dialog.querySelectorAll(focusableSelector))
        .filter((element) => ! element.hasAttribute('disabled') && element.getAttribute('tabindex') !== '-1');

    const setOpen = (open) => {
        if (closeTimer) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }

        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.setAttribute('data-open', open ? 'true' : 'false');
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.documentElement.classList.toggle('overflow-hidden', open);
        iconMenu?.classList.toggle('hidden', open);
        iconClose?.classList.toggle('hidden', ! open);

        const toggleLabel = toggle.querySelector('.sr-only');
        if (toggleLabel) {
            toggleLabel.textContent = open ? 'Close menu' : 'Open menu';
        }

        if (open) {
            panel.hidden = false;
            panel.classList.remove('hidden');
            previouslyFocused = document.activeElement;
            window.requestAnimationFrame(() => {
                const closeButton = dialog.querySelector('[data-mobile-nav-close]');
                (closeButton ?? getFocusableElements()[0])?.focus();
            });
        } else {
            closeTimer = window.setTimeout(() => {
                if (! isOpen()) {
                    panel.hidden = true;
                    panel.classList.add('hidden');
                }
            }, motionMs);
            previouslyFocused?.focus();
            previouslyFocused = null;
        }
    };

    const trapFocus = (event) => {
        if (! isOpen() || event.key !== 'Tab') {
            return;
        }

        const focusable = getFocusableElements();

        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (! event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    };

    toggle.addEventListener('click', () => {
        setOpen(! isOpen());
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => setOpen(false));
    });

    links.forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen()) {
            event.preventDefault();
            setOpen(false);

            return;
        }

        trapFocus(event);
    });

    window.addEventListener('pageshow', () => {
        if (isOpen()) {
            setOpen(false);
        }
    });
}
