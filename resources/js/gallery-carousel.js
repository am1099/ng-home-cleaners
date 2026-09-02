/**
 * Touch-friendly gallery carousel with wrap-around prev/next and edge swipe loop.
 */
export function ngGalleryCarousel(total) {
    return {
        index: 0,
        total,
        touchStartX: null,
        touchStartScroll: null,

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

        scrollTo(targetIndex, { smooth = true } = {}) {
            const track = this.$refs.track;
            const slide = track?.querySelector(`[data-gallery-slide="${targetIndex}"]`);

            if (! track || ! slide) {
                return;
            }

            const offset = slide.offsetLeft - ((track.clientWidth - slide.clientWidth) / 2);

            track.scrollTo({
                left: Math.max(0, offset),
                behavior: smooth ? 'smooth' : 'auto',
            });

            this.index = targetIndex;
        },

        wrappedIndex(delta) {
            if (this.total < 1) {
                return 0;
            }

            return (this.index + delta + this.total) % this.total;
        },

        prev() {
            this.scrollTo(this.wrappedIndex(-1));
        },

        next() {
            this.scrollTo(this.wrappedIndex(1));
        },

        onTouchStart(event) {
            this.touchStartX = event.changedTouches[0]?.clientX ?? null;
            this.touchStartScroll = this.$refs.track?.scrollLeft ?? null;
        },

        onTouchEnd(event) {
            if (this.touchStartX === null || this.total < 2) {
                this.touchStartX = null;
                this.touchStartScroll = null;

                return;
            }

            const endX = event.changedTouches[0]?.clientX ?? this.touchStartX;
            const delta = endX - this.touchStartX;
            const scrollDelta = Math.abs((this.$refs.track?.scrollLeft ?? 0) - (this.touchStartScroll ?? 0));

            this.touchStartX = null;
            this.touchStartScroll = null;
            this.updateIndex();

            // At an edge the track cannot scroll further — wrap to the other end.
            if (scrollDelta > 24 || Math.abs(delta) < 48) {
                return;
            }

            if (this.index === 0 && delta > 0) {
                this.scrollTo(this.total - 1);
            } else if (this.index === this.total - 1 && delta < 0) {
                this.scrollTo(0);
            }
        },
    };
}
