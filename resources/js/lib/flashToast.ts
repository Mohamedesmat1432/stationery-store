import { router, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

/**
 * Listens for Inertia flash messages and displays them as toasts.
 */
export function initializeFlashToast(): void {
    router.on('finish', () => {
        const page = usePage();
        const flash = page.props.flash as Record<string, string | null>;

        if (!flash) {
return;
}

        if (flash.success) {
toast.success(flash.success);
}

        if (flash.error) {
toast.error(flash.error);
}

        if (flash.warning) {
toast.warning(flash.warning);
}

        if (flash.info) {
toast.info(flash.info);
}
    });
}
