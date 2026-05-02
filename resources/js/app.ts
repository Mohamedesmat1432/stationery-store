import { createInertiaApp, router } from '@inertiajs/vue3';
import { createApp, h  } from 'vue';
import type {DefineComponent} from 'vue';
import { createI18n } from 'vue-i18n';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
    setup({ el, App, props, plugin }) {
        const i18n = createI18n({
            legacy: false,
            locale: props.initialPage.props.locale as string,
            fallbackLocale: 'en',
            messages: {
                [props.initialPage.props.locale as string]: props.initialPage.props.translations as Record<string, string>,
            },
        });

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n);

        // Listen for Inertia success events to update locale and RTL state
        router.on('success', (event) => {
            const page = event.detail.page;
            const newLocale = page.props.locale as string;
            const newTranslations = page.props.translations as Record<string, string>;

            if (newLocale && i18n.global.locale.value !== newLocale) {
                // Update translations first
                if (newTranslations) {
                    i18n.global.setLocaleMessage(newLocale, newTranslations);
                }
                
                // Then switch locale to trigger re-render
                i18n.global.locale.value = newLocale;

                // Update document attributes for layout mirroring
                if (typeof document !== 'undefined') {
                    document.documentElement.lang = newLocale;
                    document.documentElement.dir = newLocale === 'ar' ? 'rtl' : 'ltr';
                }
            }
        });

        if (el) {
            app.mount(el);
        }

        return app;
    },
});

if (typeof window !== 'undefined') {
    // This will set light / dark mode on page load...
    initializeTheme();

    // This will listen for flash toast data from the server...
    initializeFlashToast();
}
