import { useForm } from '@inertiajs/vue3';

export function useResourceForm<T extends Record<string, any>>(
    initialData: T,
    options: {
        resourceId?: string | number | null;
        routes: {
            store: string;
            update: string;
        };
        onSuccess?: () => void;
    }
) {
    const form = useForm(initialData);

    const submit = () => {
        if (options.resourceId) {
            // Use method spoofing for file uploads with PUT
            form.transform((data) => ({
                ...data,
                _method: 'PUT',
            })).post(options.routes.update, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: options.onSuccess,
            });
        } else {
            form.post(options.routes.store, {
                preserveScroll: true,
                onSuccess: options.onSuccess,
            });
        }
    };

    return {
        form,
        submit,
    };
}
