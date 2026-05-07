import { router, usePage } from '@inertiajs/vue3';
import { computed, reactive, toValue } from 'vue';
import type { MaybeRefOrGetter } from 'vue';
import { useSelection } from '@/composables/useSelection';
import type { Auth } from '@/types';

/**
 * Route helper shape matching Wayfinder-generated callable routes.
 * Wayfinder routes are functions with .url(), .definition, .form properties.
 * Also accepts plain { url, method } objects for ad-hoc routes.
 */
interface RouteHelper {
    (...args: any[]): { url: string; method?: string };
    url: (...args: any[]) => string;
    definition?: { methods: string[]; url: string };
    form?: { (...args: any[]): { action: string; method: string } };
}

interface BulkActionsConfig {
    /** Plural entity label for confirm dialogs, e.g. "customers" */
    entityName: string;
    /** Route for bulk-action POST endpoint (no args) */
    bulkActionRoute: RouteHelper;
    /** Route for single-item destroy */
    destroyRoute: (id: string | number) => { url: string; method?: string };
    /** Route for single-item restore (optional for resources without soft deletes) */
    restoreRoute?: (id: string | number) => { url: string; method?: string };
    /** Route for single-item force-delete (optional for resources without soft deletes) */
    forceDeleteRoute?: (id: string | number) => { url: string; method?: string };
}

interface ConfirmState {
    isOpen: boolean;
    title: string | { key: string, params?: any };
    description: string | { key: string, params?: any };
    confirmLabel: string;
    variant: 'default' | 'destructive' | 'warning' | 'success';
    onConfirm: () => void;
    loading: boolean;
}

type ActionType = 'delete' | 'restore' | 'forceDelete' | 'activate' | 'deactivate';

/**
 * Composable that provides bulk actions, single-row trash actions,
 * and permission checking for admin index pages.
 *
 * Uses generated route helpers instead of hardcoded URL strings.
 */
export function useBulkActions<T extends { id: string | number }>(
    itemsSource: MaybeRefOrGetter<T[]>,
    config: BulkActionsConfig,
) {
    const {
        selectedIds,
        isAllSelected,
        isIndeterminate,
        toggleAll,
        toggleItem,
        clearSelection,
    } = useSelection(() => (itemsSource ? toValue(itemsSource) : []));

    const confirmState = reactive<ConfirmState>({
        isOpen: false,
        title: '',
        description: '',
        confirmLabel: 'Confirm',
        variant: 'default',
        onConfirm: () => {},
        loading: false,
    });

    const closeConfirm = () => {
        confirmState.isOpen = false;
        confirmState.loading = false;
    };

    const permissions = computed<string[]>(() => (usePage().props.auth as Auth)?.permissions ?? []);
    const can = (permission: string) => permissions.value.includes(permission);

    const openConfirm = (
        { title, description, label, variant }: {
            title: ConfirmState['title'],
            description: ConfirmState['description'],
            label: string,
            variant: ConfirmState['variant']
        },
        onConfirm: () => void
    ) => {
        Object.assign(confirmState, {
            isOpen: true,
            title,
            description,
            confirmLabel: label,
            variant,
            onConfirm: () => {
                confirmState.loading = true;
                onConfirm();
            },
            loading: false,
        });
    };

    // ── Actions ──────────────────────────────────────────────────

    const bulkAction = (action: ActionType) => {
        const labels: Record<ActionType, string> = {
            delete: 'delete',
            restore: 'restore',
            forceDelete: 'permanently delete',
            activate: 'activate',
            deactivate: 'deactivate',
        };

        openConfirm({
            title: { key: 'bulk_confirm_title', params: { action: labels[action] } },
            description: {
                key: 'bulk_confirm_description',
                params: {
                    action: labels[action],
                    count: selectedIds.value.length,
                    entity: config.entityName
                }
            },
            label: action === 'restore' || action === 'activate' ? 'Confirm' : 'Delete All',
            variant: (action === 'restore' || action === 'activate') ? 'success' : (action === 'deactivate' ? 'warning' : 'destructive'),
        }, () => {
            router.post(config.bulkActionRoute.url(), { ids: selectedIds.value, action }, {
                onSuccess: () => {
                    clearSelection();
                    closeConfirm();
                },
                onError: () => confirmState.loading = false
            });
        });
    };

    const deleteItem = (id: string | number) => {
        openConfirm({
            title: { key: 'single_confirm_title', params: { action: 'Delete' } },
            description: {
                key: 'single_confirm_description',
                params: { action: 'delete', entity: config.entityName.replace(/s$/, '') }
            },
            label: 'Delete',
            variant: 'destructive',
        }, () => {
            router.delete(config.destroyRoute(id).url, {
                onSuccess: closeConfirm,
                onError: () => confirmState.loading = false,
            });
        });
    };

    const restoreItem = (id: string | number) => {
        if (! config.restoreRoute) {
            return;
        }

        openConfirm({
            title: { key: 'single_confirm_title', params: { action: 'Restore' } },
            description: {
                key: 'single_confirm_description',
                params: { action: 'restore', entity: config.entityName.replace(/s$/, '') }
            },
            label: 'Restore',
            variant: 'success',
        }, () => {
            router.post(config.restoreRoute!(id).url, {}, {
                onSuccess: closeConfirm,
                onError: () => confirmState.loading = false,
            });
        });
    };

    const forceDeleteItem = (id: string | number) => {
        if (! config.forceDeleteRoute) {
            return;
        }

        openConfirm({
            title: { key: 'force_delete_confirm_title' },
            description: {
                key: 'force_delete_confirm_description',
                params: { entity: config.entityName.replace(/s$/, '') }
            },
            label: 'Permanently Delete',
            variant: 'destructive',
        }, () => {
            router.delete(config.forceDeleteRoute!(id).url, {
                onSuccess: closeConfirm,
                onError: () => confirmState.loading = false,
            });
        });
    };

    return {
        selectedIds,
        isAllSelected,
        isIndeterminate,
        toggleAll,
        toggleItem,
        clearSelection,
        can,
        bulkAction,
        deleteItem,
        restoreItem,
        forceDeleteItem,
        confirmState,
        closeConfirm,
    };
}
