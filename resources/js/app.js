import './bootstrap';
import '../css/app.css';
import 'primeicons/primeicons.css';

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import { createPinia } from 'pinia';
import { purchaseOrderPlugin } from './Plugins/purchaseOrderPlugin';

import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Core from 'primevue/config'

import { useTheme } from './Composables/useTheme.js';
import customThemePreset from './theme-preset.js';

// Import Orion SDK
import { Orion } from "@tailflow/laravel-orion/lib/orion";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Initialize Orion SDK
Orion.init(window.location.origin);

const pinia = createPinia();
pinia.use(purchaseOrderPlugin);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        // set site theme (light/dark mode)
        const { initSiteTheme } = useTheme();
        initSiteTheme();

        // start the app
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue, Ziggy)
            .use(PrimeVue, {
                theme: customThemePreset,
            })
            .component('FileUpload', FileUpload)
            .component('Steps', Steps)
            .component('Button', Button)
            .use(ToastService)
            .component('Head', Head)
            .component('Link', Link)
            .component('InputText', InputText)
            .mount(el);
    },
    progress: {
        color: 'var(--p-primary-500)',
    },
});
