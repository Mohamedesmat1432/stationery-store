import { router, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import type { MaybeRefOrGetter } from 'vue';
import { useSelection } from '@/composables/useSelection';

interface BulkActionsConfig {
    /** Plural entity label for confirm dialogs, e.g. "customers" */
    entityName: string;
    /** URL for the bulk-action POST endpoint, e.g. "/admin/customers/bulk-action" */
    bulkActionUrl: string;
    /** Base URL for single-item actions (restore/force-delete/destroy), e.g. "/admin/customers" */
    resourceUrl: string;
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

type ActionType = 'delete' | 'restore' | 'forceDelete';

/**
 * Composable that provides bulk actions, single-row trash actions,
 * and permission checking for admin index pages.
 */
export function useBulkActions<T extends { id: string | number }>(
    itemsSource: MaybeRefOrGetter<T[]>,
    config: BulkActionsConfig,
) {
    const { selectedIds, isAllSelected, isIndeterminate, toggleAll, toggleItem, clearSelection } =
        useSelection(itemsSource);

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

    const permissions = computed<string[]>(() => (usePage().props.auth as any).permissions || []);
    const can = (permission: string) => permissions.value.includes(permission);

    /**
     * Internal helper to open the confirmation dialog with common state.
     */
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
            label: action === 'restore' ? 'Restore All' : 'Delete All',
            variant: action === 'restore' ? 'success' : 'destructive',
        }, () => {
            router.post(config.bulkActionUrl, { ids: selectedIds.value, action }, {
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
            router.delete(`${config.resourceUrl}/${id}`, {
                onSuccess: closeConfirm,
                onError: () => confirmState.loading = false,
            });
        });
    };

    const restoreItem = (id: string | number) => {
        openConfirm({
            title: { key: 'single_confirm_title', params: { action: 'Restore' } },
            description: { 
                key: 'single_confirm_description', 
                params: { action: 'restore', entity: config.entityName.replace(/s$/, '') } 
            },
            label: 'Restore',
            variant: 'success',
        }, () => {
            router.post(`${config.resourceUrl}/${id}/restore`, {}, {
                onSuccess: closeConfirm,
                onError: () => confirmState.loading = false,
            });
        });
    };

    const forceDeleteItem = (id: string | number) => {
        openConfirm({
            title: { key: 'force_delete_confirm_title' },
            description: { 
                key: 'force_delete_confirm_description', 
                params: { entity: config.entityName.replace(/s$/, '') } 
            },
            label: 'Permanently Delete',
            variant: 'destructive',
        }, () => {
            router.delete(`${config.resourceUrl}/${id}/force-delete`, {
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
