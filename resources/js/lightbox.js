/**
 * Full-size image lightbox for gallery and recent-work thumbnails.
 */
export function initImageLightbox() {
    const triggers = document.querySelectorAll('[data-lightbox-src]');

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
            <img class="ng-lightbox__image" alt="" />
            <p class="ng-lightbox__caption" hidden></p>
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

    triggers.forEach((trigger) => {
        if (trigger.dataset.lightboxBound === '1') {
            return;
        }

        trigger.dataset.lightboxBound = '1';
        trigger.addEventListener('click', (event) => {
            event.preventDefault();

            const src = trigger.getAttribute('data-lightbox-src');
            const alt = trigger.getAttribute('data-lightbox-alt') || '';
            const captionText = trigger.getAttribute('data-lightbox-caption') || '';

            if (! src || ! image) {
                return;
            }

            image.src = src;
            image.alt = alt;

            if (caption) {
                if (captionText) {
                    caption.textContent = captionText;
                    caption.hidden = false;
                } else {
                    caption.textContent = '';
                    caption.hidden = true;
                }
            }

            dialog.showModal();
        });
    });
}
