import { useResourceForm } from '@/composables/useResourceForm';
import * as brandRoutes from '@/routes/admin/brands/index';

type BrandData = Modules.Catalog.Data.BrandData;

export function useBrands(brand?: BrandData) {
    return useResourceForm({
        name: brand?.name ?? '',
        slug: brand?.slug ?? '',
        description: brand?.description ?? '',
        website: brand?.website ?? '',
        is_active: brand?.is_active ?? true,
        sort_order: brand?.sort_order ?? 0,
        logo: null as File | string | null,
    }, {
        resourceId: brand?.id,
        routes: {
            store: brandRoutes.store.url(),
            update: brand?.id ? brandRoutes.update.url(brand.id!) : '',
        }
    });
}
