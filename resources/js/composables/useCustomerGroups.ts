import { useForm } from '@inertiajs/vue3';
import * as customerGroupsRoutes from '@/routes/admin/customer-groups/index';

export function useCustomerGroups(initialData?: Modules.CRM.Data.CustomerGroupData) {
    const form = useForm({
        name: initialData?.name ?? '',
        slug: initialData?.slug ?? '',
        description: initialData?.description ?? '',
        discount_percentage: initialData?.discount_percentage ?? 0,
        is_active: initialData?.is_active ?? true,
        sort_order: initialData?.sort_order ?? 0,
    });

    // Slug is auto-generated on the backend via model observer.
    // The frontend no longer duplicates this business logic.

    const submit = (id?: string) => {
        if (id) {
            form.put(customerGroupsRoutes.update.url(id));
        } else {
            form.post(customerGroupsRoutes.store.url());
        }
    };

    return {
        form,
        submit,
    };
}
