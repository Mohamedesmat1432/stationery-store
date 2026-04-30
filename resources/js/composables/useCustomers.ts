import { useForm } from '@inertiajs/vue3';

export function useCustomers(initialData?: any) {
    const form = useForm({
        user_id: initialData?.user_id ?? '',
        phone: initialData?.phone ?? '',
        birth_date: initialData?.birth_date ?? '',
        gender: initialData?.gender ?? 'other',
        tax_number: initialData?.tax_number ?? '',
        company_name: initialData?.company_name ?? '',
        customer_group_id: initialData?.customer_group_id ?? '',
        metadata: initialData?.metadata ?? {},
    });

    const submit = (id?: string) => {
        if (id) {
            form.put(`/admin/customers/${id}`);
        } else {
            form.post('/admin/customers');
        }
    };

    return {
        form,
        submit,
    };
}
