import { computed } from 'vue';
import type { InertiaForm } from '@inertiajs/vue3';

export function usePermissions(form: InertiaForm<{ permissions: string[]; [key: string]: any }>, availablePermissions: string[]) {
    // Group permissions by the entity name (e.g. view_users -> users)
    const groupedPermissions = computed(() => {
        const groups: Record<string, string[]> = {};
        
        availablePermissions.forEach(permission => {
            const parts = permission.split('_');
            let module = 'system';

            if (permission.startsWith('force_delete_')) {
                module = parts.slice(2).join('_');
            } else {
                module = parts.slice(1).join('_') || 'system';
            }
            
            if (!groups[module]) {
                groups[module] = [];
            }
            groups[module].push(permission);
        });
        
        return groups;
    });

    const formatName = (str: string) => {
        return str.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    };

    const formatPermissionLabel = (permission: string) => {
        const parts = permission.split('_');
        let label = parts[0];

        if (permission.startsWith('force_delete_')) {
            label = 'force_delete';
        }

        return formatName(label);
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
            // Uncheck all in this module
            form.permissions = form.permissions.filter(p => !modulePermissions.includes(p));
        } else {
            // Check all in this module (preventing duplicates)
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
