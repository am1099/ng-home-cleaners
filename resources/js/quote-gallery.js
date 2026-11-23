const WINDOW = 6;
const INTERVAL_MS = 5000;

function mod(value, total) {
    return ((value % total) + total) % total;
}

function windowAt(images, offset) {
    const total = images.length;

    if (total === 0) {
        return [];
    }

    const size = Math.min(WINDOW, total);

    return Array.from({ length: size }, (_, index) => images[mod(offset + index, total)]);
}

/**
 * Six-up recent-work gallery. Advances one photo at a time so every frame
 * stays full and free of duplicates. Autoplay pauses while hovered, focused,
 * or while the lightbox is open.
 */
export function ngQuoteGallery(images) {
    const list = Array.isArray(images) ? images : [];
    const first = windowAt(list, 0);

    return {
        images: list,
        offset: 0,
        front: 'a',
        slotsA: first,
        slotsB: first,
        hovered: false,
        focused: false,
        lightboxOpen: false,
        reduced: false,
        liveMessage: '',
        timer: null,

        get rotating() {
            return this.images.length > WINDOW && ! this.reduced;
        },

        get paused() {
            return this.hovered || this.focused || this.lightboxOpen;
        },

        init() {
            this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            this.preload(windowAt(this.images, 1));
            this.onLightboxToggle = (event) => {
                this.lightboxOpen = Boolean(event.detail?.open);
            };
            document.addEventListener('ng-lightbox-toggle', this.onLightboxToggle);
            this.arm();
        },

        destroy() {
            this.clearTimer();

            if (this.onLightboxToggle) {
                document.removeEventListener('ng-lightbox-toggle', this.onLightboxToggle);
            }
        },

        tile(slot) {
            const slots = this.front === 'a' ? this.slotsA : this.slotsB;

            return slots[slot] || this.slotsA[slot] || null;
        },

        tileLabel(slot) {
            const photo = this.tile(slot);
            const total = Math.min(WINDOW, this.images.length);

            if (photo?.alt) {
                return `View photo: ${photo.alt}`;
            }

            return `View full-size photo ${slot + 1} of ${total}`;
        },

        openAt(slot) {
            if (! this.images.length) {
                return;
            }

            const index = (this.offset + slot) % this.images.length;
            this.lightboxOpen = true;

            document.dispatchEvent(new CustomEvent('ng-lightbox-open', {
                detail: {
                    items: this.images.map((photo) => ({
                        src: photo.src,
                        alt: photo.alt || '',
                        caption: photo.caption || '',
                    })),
                    index,
                },
            }));
        },

        step(direction) {
            if (this.images.length <= WINDOW) {
                return;
            }

            const total = this.images.length;
            const next = (this.offset + direction + total) % total;
            const incoming = windowAt(this.images, next);

            this.preload(windowAt(this.images, next + direction));

            if (this.reduced) {
                this.slotsA = incoming;
                this.slotsB = incoming;
                this.front = 'a';
            } else if (this.front === 'a') {
                this.slotsB = incoming;
                this.$nextTick(() => {
                    this.front = 'b';
                });
            } else {
                this.slotsA = incoming;
                this.$nextTick(() => {
                    this.front = 'a';
                });
            }

            this.offset = next;
            this.liveMessage = direction < 0 ? 'Showing the previous photos.' : 'Showing the next photos.';
        },

        preload(list) {
            list.forEach((photo) => {
                if (! photo?.src) {
                    return;
                }

                const image = new Image();
                image.src = photo.src;
            });
        },

        arm() {
            this.clearTimer();

            if (! this.rotating) {
                return;
            }

            this.timer = window.setInterval(() => {
                if (this.paused) {
                    return;
                }

                this.step(1);
            }, INTERVAL_MS);
        },

        clearTimer() {
            if (this.timer !== null) {
                window.clearInterval(this.timer);
                this.timer = null;
            }
        },
    };
}
