import { useResourceForm } from '@/composables/useResourceForm';
import * as categoryRoutes from '@/routes/admin/categories/index';

type CategoryData = Modules.Catalog.Data.CategoryData;

export function useCategories(initialData?: CategoryData) {
    return useResourceForm({
        name: initialData?.name ?? '',
        slug: initialData?.slug ?? '',
        description: initialData?.description ?? '',
        parent_id: initialData?.parent_id ?? null,
        is_active: initialData?.is_active ?? true,
        is_featured: initialData?.is_featured ?? false,
        sort_order: initialData?.sort_order ?? 0,
        meta_title: initialData?.meta_title ?? '',
        meta_description: initialData?.meta_description ?? '',
        meta_keywords: initialData?.meta_keywords ?? '',
        icon: null as File | string | null,
        banner_image: null as File | string | null,
    }, {
        resourceId: initialData?.id,
        routes: {
            store: categoryRoutes.store.url(),
            update: initialData?.id ? categoryRoutes.update.url(initialData.id) : '',
        }
    });
}
