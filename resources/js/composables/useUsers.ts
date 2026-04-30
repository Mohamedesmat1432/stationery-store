import { useForm } from '@inertiajs/vue3';

export function useUsers(initialData?: any) {
    const form = useForm({
        name: initialData?.name ?? '',
        email: initialData?.email ?? '',
        password: '',
        roles: initialData?.roles ?? [] as string[],
    });

    const submit = (id?: string) => {
        if (id) {
            form.put(`/admin/users/${id}`);
        } else {
            form.post('/admin/users');
        }
    };

    return {
        form,
        submit,
    };
}
