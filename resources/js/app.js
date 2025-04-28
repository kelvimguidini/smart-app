import './bootstrap';


import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import VCalendar from 'v-calendar';
import { Inertia } from '@inertiajs/inertia';
import 'v-calendar/style.css';


const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

Inertia.on('success', (event) => {
    const flash = event.detail?.page?.props?.flash;
    const errors = event.detail?.page?.props?.errors;
    const error = event.detail?.page?.props?.error;

    // Exibe mensagens de flash
    if (flash?.message) {
        window.dispatchEvent(new CustomEvent('global-flash', {
            detail: {
                message: flash.message,
                type: flash.type || 'info',
            },
        }));
    }

    // Exibe mensagens de flash
    if (error) {
        window.dispatchEvent(new CustomEvent('global-flash', {
            detail: {
                message: error,
                type: 'danger',
            },
        }));
    }

    // Exibe erros de validação
    if (errors && Object.keys(errors).length > 0) {
        const errorMessage = Object.values(errors).join(', ');
        window.dispatchEvent(new CustomEvent('global-flash', {
            detail: {
                message: errorMessage,
                type: 'danger',
            },
        }));
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, app, props, plugin }) {
        return createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(VCalendar, {})
            .mount(el);
    }
});

InertiaProgress.init({ color: '#4B5563' });
