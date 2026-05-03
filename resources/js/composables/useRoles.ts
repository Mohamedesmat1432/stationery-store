import { formatLabel } from '@/lib/format';
import type { InertiaForm } from '@inertiajs/vue3';

export function useRoles(form: InertiaForm<{ roles: string[]; [key: string]: any }>) {
    const formatRoleName = (str: string) => formatLabel(str);

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
