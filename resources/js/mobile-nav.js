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
        // #region agent log
        fetch('http://127.0.0.1:7348/ingest/60f3acf6-e74c-49a4-b319-ca8e67d2d34c',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'0c7e53'},body:JSON.stringify({sessionId:'0c7e53',runId:'pre-fix',hypothesisId:'D',location:'mobile-nav.js:init',message:'mobile nav init aborted missing nodes',data:{hasToggle:!!toggle,hasPanel:!!panel,hasDialog:!!dialog},timestamp:Date.now()})}).catch(()=>{});
        // #endregion
        return;
    }

    // #region agent log
    fetch('http://127.0.0.1:7348/ingest/60f3acf6-e74c-49a4-b319-ca8e67d2d34c',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'0c7e53'},body:JSON.stringify({sessionId:'0c7e53',runId:'pre-fix',hypothesisId:'D',location:'mobile-nav.js:init',message:'mobile nav init ok',data:{linkCount:links.length,vw:window.innerWidth},timestamp:Date.now()})}).catch(()=>{});
    // #endregion

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

                // #region agent log
                const navEl = dialog.querySelector('nav[aria-label="Primary mobile"]');
                const navLinks = navEl ? Array.from(navEl.querySelectorAll('[data-mobile-nav-link]')) : [];
                const cs = (el) => el ? window.getComputedStyle(el) : null;
                const box = (el) => {
                    if (! el) return null;
                    const r = el.getBoundingClientRect();
                    return { w: Math.round(r.width), h: Math.round(r.height), t: Math.round(r.top), l: Math.round(r.left), b: Math.round(r.bottom), r: Math.round(r.right) };
                };
                const pCs = cs(panel);
                const dCs = cs(dialog);
                const nCs = cs(navEl);
                const firstLink = navLinks[0] || null;
                const lCs = cs(firstLink);
                                const ctaEl = dialog.querySelector('[data-mobile-nav-cta]');
                const cCs = cs(ctaEl);
                fetch('http://127.0.0.1:7348/ingest/60f3acf6-e74c-49a4-b319-ca8e67d2d34c',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'0c7e53'},body:JSON.stringify({sessionId:'0c7e53',runId:'post-fix',hypothesisId:'B',location:'mobile-nav.js:setOpen',message:'mobile nav open metrics',data:{dataOpen:panel.getAttribute('data-open'),panelHidden:panel.hidden,panelClassHidden:panel.classList.contains('hidden'),panelVis:pCs?.visibility,panelPE:pCs?.pointerEvents,panelDisplay:pCs?.display,panelBox:box(panel),drawerBg:dCs?.backgroundColor,drawerTransform:dCs?.transform,drawerDisplay:dCs?.display,drawerPos:dCs?.position,drawerBox:box(dialog),navDisplay:nCs?.display,navH:nCs?.height,navOverflow:nCs?.overflowY,navFlex:nCs?.flex,navBox:box(navEl),linkCount:navLinks.length,linkLabels:navLinks.map((a)=>a.textContent?.trim()),firstLinkBox:box(firstLink),firstLinkColor:lCs?.color,firstLinkOpacity:lCs?.opacity,firstLinkVis:lCs?.visibility,vw:window.innerWidth,vh:window.innerHeight,panelFillsViewport:!!(box(panel)&&box(panel).h>=(window.innerHeight-8)),linksInsideDrawer:!!(firstLink&&box(dialog)&&box(firstLink).t>=box(dialog).t&&box(firstLink).b<=box(dialog).b),drawerFullWidth:!!(box(dialog)&&box(dialog).w>=(window.innerWidth-2)),ctaBox:box(ctaEl),ctaVisible:!!(ctaEl&&box(ctaEl)&&box(ctaEl).h>40&&box(ctaEl).b<=window.innerHeight+2),firstLinkFontSize:lCs?.fontSize},timestamp:Date.now()})}).catch(()=>{});
                // #endregion
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
