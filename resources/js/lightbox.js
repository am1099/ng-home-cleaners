/**
 * Full-size image lightbox with optional gallery carousel (swipe / arrows / wrap).
 */
export function initImageLightbox() {
    const triggers = Array.from(document.querySelectorAll('[data-lightbox-src]'));

    if (! triggers.length) {
        return;
    }

    let dialog = document.getElementById('ng-image-lightbox');

    if (! dialog) {
        dialog = document.createElement('dialog');
        dialog.id = 'ng-image-lightbox';
        dialog.className = 'ng-lightbox';
        dialog.innerHTML = `
            <form method="dialog" class="ng-lightbox__close-wrap">
                <button type="submit" class="ng-lightbox__close" aria-label="Close image">
                    <span aria-hidden="true">&times;</span>
                </button>
            </form>
            <div class="ng-lightbox__stage">
                <button type="button" class="ng-lightbox__nav ng-lightbox__nav--prev" aria-label="Previous photo" hidden>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true">
                        <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                    </svg>
                </button>
                <img class="ng-lightbox__image" alt="" />
                <button type="button" class="ng-lightbox__nav ng-lightbox__nav--next" aria-label="Next photo" hidden>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <p class="ng-lightbox__caption" hidden></p>
            <p class="ng-lightbox__counter" hidden></p>
        `;
        document.body.appendChild(dialog);

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    }

    const image = dialog.querySelector('.ng-lightbox__image');
    const caption = dialog.querySelector('.ng-lightbox__caption');
    const counter = dialog.querySelector('.ng-lightbox__counter');
    const prevButton = dialog.querySelector('.ng-lightbox__nav--prev');
    const nextButton = dialog.querySelector('.ng-lightbox__nav--next');
    const focusableSelector = 'a[href], button:not([disabled]):not([hidden]), textarea, input, select, [tabindex]:not([tabindex="-1"])';

    let items = [];
    let index = 0;
    let touchStartX = null;

    const getFocusableElements = () => Array.from(dialog.querySelectorAll(focusableSelector))
        .filter((element) => ! element.hasAttribute('disabled') && element.getAttribute('tabindex') !== '-1');

    const showItem = (targetIndex) => {
        if (! items.length || ! image) {
            return;
        }

        index = (targetIndex + items.length) % items.length;
        const item = items[index];

        image.src = item.src;
        image.alt = item.alt;

        if (caption) {
            if (item.caption) {
                caption.textContent = item.caption;
                caption.hidden = false;
            } else {
                caption.textContent = '';
                caption.hidden = true;
            }
        }

        const multi = items.length > 1;

        if (prevButton) {
            prevButton.hidden = ! multi;
        }

        if (nextButton) {
            nextButton.hidden = ! multi;
        }

        if (counter) {
            if (multi) {
                counter.textContent = `${index + 1} / ${items.length}`;
                counter.hidden = false;
            } else {
                counter.textContent = '';
                counter.hidden = true;
            }
        }
    };

    const trapFocus = (event) => {
        if (! dialog.open || event.key !== 'Tab') {
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

    const onKeydown = (event) => {
        if (! dialog.open) {
            return;
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            showItem(index - 1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            showItem(index + 1);
        } else {
            trapFocus(event);
        }
    };

    dialog.addEventListener('keydown', onKeydown);

    prevButton?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        showItem(index - 1);
    });

    nextButton?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        showItem(index + 1);
    });

    const stage = dialog.querySelector('.ng-lightbox__stage');

    stage?.addEventListener('touchstart', (event) => {
        touchStartX = event.changedTouches[0]?.clientX ?? null;
    }, { passive: true });

    stage?.addEventListener('touchend', (event) => {
        if (touchStartX === null || items.length < 2) {
            touchStartX = null;

            return;
        }

        const endX = event.changedTouches[0]?.clientX ?? touchStartX;
        const delta = endX - touchStartX;
        touchStartX = null;

        if (Math.abs(delta) < 48) {
            return;
        }

        showItem(delta > 0 ? index - 1 : index + 1);
    }, { passive: true });

    triggers.forEach((trigger) => {
        if (trigger.dataset.lightboxBound === '1') {
            return;
        }

        trigger.dataset.lightboxBound = '1';
        trigger.addEventListener('click', (event) => {
            event.preventDefault();

            const src = trigger.getAttribute('data-lightbox-src');

            if (! src || ! image) {
                return;
            }

            const group = trigger.getAttribute('data-lightbox-group');

            if (group) {
                items = triggers
                    .filter((candidate) => candidate.getAttribute('data-lightbox-group') === group)
                    .map((candidate) => ({
                        src: candidate.getAttribute('data-lightbox-src') || '',
                        alt: candidate.getAttribute('data-lightbox-alt') || '',
                        caption: candidate.getAttribute('data-lightbox-caption') || '',
                    }))
                    .filter((item) => item.src);

                const start = Number.parseInt(trigger.getAttribute('data-lightbox-index') || '0', 10);
                showItem(Number.isFinite(start) ? start : 0);
            } else {
                items = [{
                    src,
                    alt: trigger.getAttribute('data-lightbox-alt') || '',
                    caption: trigger.getAttribute('data-lightbox-caption') || '',
                }];
                showItem(0);
            }

            dialog.showModal();
            window.requestAnimationFrame(() => {
                dialog.querySelector('.ng-lightbox__close')?.focus();
            });
        });
    });
}
