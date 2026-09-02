/**
 * Touch-friendly gallery carousel with scroll-snap and prev/next controls.
 */
export function ngGalleryCarousel(total) {
    return {
        index: 0,
        total,

        init() {
            this.$nextTick(() => this.updateIndex());
        },

        updateIndex() {
            const track = this.$refs.track;

            if (! track) {
                return;
            }

            const slides = track.querySelectorAll('[data-gallery-slide]');

            if (slides.length === 0) {
                return;
            }

            const trackRect = track.getBoundingClientRect();
            const trackCenter = trackRect.left + (trackRect.width / 2);

            let closest = 0;
            let closestDistance = Number.POSITIVE_INFINITY;

            slides.forEach((slide, slideIndex) => {
                const rect = slide.getBoundingClientRect();
                const slideCenter = rect.left + (rect.width / 2);
                const distance = Math.abs(slideCenter - trackCenter);

                if (distance < closestDistance) {
                    closestDistance = distance;
                    closest = slideIndex;
                }
            });

            this.index = closest;
        },

        scrollTo(targetIndex) {
            const track = this.$refs.track;
            const slide = track?.querySelector(`[data-gallery-slide="${targetIndex}"]`);

            if (! track || ! slide) {
                return;
            }

            const offset = slide.offsetLeft - ((track.clientWidth - slide.clientWidth) / 2);

            track.scrollTo({
                left: Math.max(0, offset),
                behavior: 'smooth',
            });

            this.index = targetIndex;
        },

        prev() {
            this.scrollTo(Math.max(0, this.index - 1));
        },

        next() {
            this.scrollTo(Math.min(this.total - 1, this.index + 1));
        },
    };
}
