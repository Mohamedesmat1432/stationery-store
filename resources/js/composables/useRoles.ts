import type { InertiaForm } from '@inertiajs/vue3';
import { formatLabel } from '@/lib/format';

export function formatRoleName(str: string): string {
    return formatLabel(str);
}

export function useRoles(form: InertiaForm<{ roles: string[]; [key: string]: any }>) {
    const toggleRole = (role: string) => {
        const index = form.roles.indexOf(role);

        if (index === -1) {
            form.roles = [...form.roles, role];
        } else {
            form.roles = form.roles.filter(r => r !== role);
        }
    };

    return {
        formatRoleName,
        toggleRole,
    };
}
