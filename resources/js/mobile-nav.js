/**
 * Accessible mobile navigation for the public site header.
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

    let previouslyFocused = null;

    const focusableSelector = 'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])';

    const getFocusableElements = () => Array.from(dialog.querySelectorAll(focusableSelector))
        .filter((element) => element.offsetParent !== null || element === toggle);

    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.hidden = ! open;
        panel.classList.toggle('hidden', ! open);
        document.documentElement.classList.toggle('overflow-hidden', open);
        iconMenu?.classList.toggle('hidden', open);
        iconClose?.classList.toggle('hidden', ! open);

        if (open) {
            previouslyFocused = document.activeElement;
            const firstFocusable = getFocusableElements()[0];
            firstFocusable?.focus();
        } else {
            previouslyFocused?.focus();
            previouslyFocused = null;
        }
    };

    const trapFocus = (event) => {
        if (panel.hidden) {
            return;
        }

        const focusable = getFocusableElements();

        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.key === 'Tab') {
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (! event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    };

    toggle.addEventListener('click', () => {
        setOpen(panel.hidden);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => setOpen(false));
    });

    links.forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && ! panel.hidden) {
            event.preventDefault();
            setOpen(false);
        }

        trapFocus(event);
    });
}
