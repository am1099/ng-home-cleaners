import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

/**
 * Site-wide corner toasts (Mobbin toast pattern):
 * desktop top-right; mobile bottom-center above sticky CTAs.
 */
function isMobileViewport() {
    return window.matchMedia('(max-width: 63.999rem)').matches;
}

function createNotyf() {
    const mobile = isMobileViewport();

    return new Notyf({
        duration: 4500,
        ripple: false,
        dismissible: true,
        position: {
            x: mobile ? 'center' : 'right',
            y: mobile ? 'bottom' : 'top',
        },
        types: [
            {
                type: 'success',
                background: '#124840',
                icon: false,
                className: 'ng-toast ng-toast--success',
            },
            {
                type: 'error',
                background: '#7f1d1d',
                icon: false,
                className: 'ng-toast ng-toast--error',
            },
            {
                type: 'info',
                background: '#1a6b62',
                icon: false,
                className: 'ng-toast ng-toast--info',
            },
        ],
    });
}

let notyf = null;
let lastMobile = null;

function getNotyf() {
    const mobile = isMobileViewport();

    if (! notyf || lastMobile !== mobile) {
        notyf = createNotyf();
        lastMobile = mobile;
    }

    return notyf;
}

export function showToast({ type = 'success', title = null, message = '' } = {}) {
    const text = [title, message].filter(Boolean).join(' — ');

    if (! text) {
        return;
    }

    const resolvedType = ['success', 'error', 'info'].includes(type) ? type : 'success';

    getNotyf().open({
        type: resolvedType,
        message: text,
    });
}

function bindLivewireToastListener() {
    if (! window.Livewire || window.__ngToastLivewireBound) {
        return;
    }

    window.__ngToastLivewireBound = true;

    Livewire.on('toast', (...args) => {
        const detail = args.length === 1 && args[0] && typeof args[0] === 'object' && ! Array.isArray(args[0])
            ? args[0]
            : (Array.isArray(args[0]) ? (args[0][0] ?? {}) : {});

        showToast(detail);
    });
}

export function initToasts() {
    getNotyf();

    document.addEventListener('ng-toast', (event) => {
        showToast(event.detail ?? {});
    });

    // Livewire may already be started by the time this runs.
    bindLivewireToastListener();
    document.addEventListener('livewire:init', bindLivewireToastListener);
}

window.ngToast = showToast;
