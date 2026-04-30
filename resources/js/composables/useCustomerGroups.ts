import { useForm } from '@inertiajs/vue3';

export function useCustomerGroups(initialData?: any) {
    const form = useForm({
        name: initialData?.name ?? '',
        slug: initialData?.slug ?? '',
        description: initialData?.description ?? '',
        discount_percentage: initialData?.discount_percentage ?? 0,
        is_active: initialData?.is_active ?? true,
        sort_order: initialData?.sort_order ?? 0,
    });

    const updateSlug = () => {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    };

    const submit = (id?: string) => {
        if (id) {
            form.put(`/admin/customer-groups/${id}`);
        } else {
            form.post('/admin/customer-groups');
        }
    };

    return {
        form,
        updateSlug,
        submit,
    };
}
