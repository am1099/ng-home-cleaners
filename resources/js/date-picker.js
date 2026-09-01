function pad(value) {
    return String(value).padStart(2, '0');
}

function toIso(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

function parseIso(iso) {
    if (!iso || typeof iso !== 'string') {
        return null;
    }

    const [year, month, day] = iso.split('-').map(Number);

    if (!year || !month || !day) {
        return null;
    }

    return new Date(year, month - 1, day);
}

function formatUk(iso) {
    const date = parseIso(iso);

    if (!date) {
        return '';
    }

    return `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()}`;
}

export function ngDatePicker(config = {}) {
    return {
        open: false,
        iso: config.initial || '',
        min: config.min || null,
        model: config.model || 'preferredDate',
        viewYear: 0,
        viewMonth: 0,
        init() {
            const start = parseIso(this.iso) || new Date();
            this.viewYear = start.getFullYear();
            this.viewMonth = start.getMonth();
        },
        get label() {
            return formatUk(this.iso);
        },
        get monthLabel() {
            return new Date(this.viewYear, this.viewMonth, 1).toLocaleDateString('en-GB', {
                month: 'long',
                year: 'numeric',
            });
        },
        get days() {
            const first = new Date(this.viewYear, this.viewMonth, 1);
            const mondayOffset = (first.getDay() + 6) % 7;
            const cursor = new Date(first);
            cursor.setDate(1 - mondayOffset);

            const days = [];

            for (let i = 0; i < 42; i++) {
                const date = new Date(cursor);
                date.setDate(cursor.getDate() + i);
                const iso = toIso(date);

                days.push({
                    iso,
                    date: date.getDate(),
                    inMonth: date.getMonth() === this.viewMonth,
                    disabled: this.min ? iso < this.min : false,
                });
            }

            return days;
        },
        prevMonth() {
            if (this.viewMonth === 0) {
                this.viewMonth = 11;
                this.viewYear -= 1;
                return;
            }

            this.viewMonth -= 1;
        },
        nextMonth() {
            if (this.viewMonth === 11) {
                this.viewMonth = 0;
                this.viewYear += 1;
                return;
            }

            this.viewMonth += 1;
        },
        select(day) {
            if (day.disabled) {
                return;
            }

            this.commit(day.iso);
            this.open = false;
        },
        clear() {
            this.commit('');
            this.open = false;
        },
        today() {
            const iso = toIso(new Date());

            if (this.min && iso < this.min) {
                return;
            }

            this.commit(iso);
            this.viewYear = new Date().getFullYear();
            this.viewMonth = new Date().getMonth();
            this.open = false;
        },
        commit(iso) {
            this.iso = iso;

            if (this.$wire) {
                this.$wire.set(this.model, iso || null);
            }
        },
    };
}
