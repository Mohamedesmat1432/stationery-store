import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

/**
 * Listens for Inertia flash messages and displays them as toasts.
 */
let lastFlash: string | null = null;
let lastTimestamp: number = 0;
let isInitialized = false;

export function initializeFlashToast(): void {
    if (isInitialized) {
return;
}

    isInitialized = true;

    router.on('success', (event) => {
        const flash = event.detail.page.props.flash as Record<string, string | null>;

        if (!flash || Object.keys(flash).length === 0) {
            return;
        }

        // Create a unique key for the current flash messages
        const currentFlash = JSON.stringify(flash);
        const now = Date.now();

        // Check if all flash values are null/empty
        const hasContent = Object.values(flash).some(v => v !== null && v !== undefined && v !== '');

        if (!hasContent) {
            return;
        }

        // If the flash is exactly the same as the last one and happened very recently (within 1000ms), skip it
        // This prevents double toasts during redirects or rapid re-renders
        if (currentFlash === lastFlash && now - lastTimestamp < 1000) {
            return;
        }

        let hasShown = false;

        if (flash.success) {
            toast.success(flash.success);
            hasShown = true;
        }

        if (flash.error) {
            toast.error(flash.error);
            hasShown = true;
        }

        if (flash.warning) {
            toast.warning(flash.warning);
            hasShown = true;
        }

        if (flash.info) {
            toast.info(flash.info);
            hasShown = true;
        }

        if (hasShown) {
            lastFlash = currentFlash;
            lastTimestamp = now;
        }
    });
}
