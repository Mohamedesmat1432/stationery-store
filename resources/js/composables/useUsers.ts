import { useForm } from '@inertiajs/vue3';
import { store, update } from '@/actions/Modules/Identity/Http/Controllers/UserController';

export function useUsers(initialData?: Modules.Identity.Data.UserData) {
    const form = useForm({
        name: initialData?.name ?? '',
        email: initialData?.email ?? '',
        password: '',
        roles: initialData?.roles ?? [] as string[],
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
