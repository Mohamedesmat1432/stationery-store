import { useForm } from '@inertiajs/vue3';
import { store, update } from '@/actions/Modules/CRM/Http/Controllers/CustomerController';

export function useCustomers(initialData?: Modules.CRM.Data.CustomerData) {
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
            form.put(update.url(id));
        } else {
            form.post(store.url());
        }
    };

    return {
        form,
        submit,
    };
}
