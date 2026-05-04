import type { InertiaForm } from '@inertiajs/vue3';
import { computed, toValue } from 'vue';
import type { MaybeRefOrGetter } from 'vue';
import { formatLabel } from '@/lib/format';

export interface GroupedPermissions {
    [module: string]: string[];
}

export function usePermissions(
    form: InertiaForm<{ permissions: string[]; [key: string]: any }>,
    availablePermissions: MaybeRefOrGetter<GroupedPermissions>,
) {
    // Permissions are now pre-grouped by the backend (IdentityCacheService).
    // No frontend parsing of naming conventions needed.
    const groupedPermissions = computed<GroupedPermissions>(() => {
        return toValue(availablePermissions) || {};
    });

    const formatName = (str: string) => formatLabel(str);

    const formatPermissionLabel = (permission: string) => {
        const parts = permission.split('_');

        // Extract the action portion (everything before the entity suffix)
        // e.g. 'update_customers' -> 'update', 'force_delete_orders' -> 'force_delete'
        let actionParts: string[];

        if (parts.length >= 3 && parts[0] === 'force' && parts[1] === 'delete') {
            actionParts = parts.slice(0, 2);
        } else {
            actionParts = parts.slice(0, 1);
        }

        return formatName(actionParts.join('_'));
    };

    const togglePermission = (permission: string) => {
        const index = form.permissions.indexOf(permission);

        if (index === -1) {
            form.permissions = [...form.permissions, permission];
        } else {
            form.permissions = form.permissions.filter(p => p !== permission);
        }
    };

    const toggleModule = (modulePermissions: string[]) => {
        const allChecked = modulePermissions.every(p => form.permissions.includes(p));

        if (allChecked) {
            form.permissions = form.permissions.filter(p => !modulePermissions.includes(p));
        } else {
            const toAdd = modulePermissions.filter(p => !form.permissions.includes(p));
            form.permissions = [...form.permissions, ...toAdd];
        }
    };

    return {
        groupedPermissions,
        formatName,
        formatPermissionLabel,
        togglePermission,
        toggleModule,
    };
}
