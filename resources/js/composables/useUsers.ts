import { useForm } from '@inertiajs/vue3';
import * as usersRoutes from '@/routes/admin/users/index';

export function useUsers(initialData?: Modules.Identity.Data.UserData) {
    const form = useForm({
        name: initialData?.name ?? '',
        email: initialData?.email ?? '',
        password: '',
        roles: initialData?.roles ?? [] as string[],
    });

    const submit = (id?: string) => {
        if (id) {
            form.put(usersRoutes.update.url(id));
        } else {
            form.post(usersRoutes.store.url());
        }
    };

    return {
        form,
        submit,
    };
}
