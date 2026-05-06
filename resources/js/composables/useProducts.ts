import { useResourceForm } from '@/composables/useResourceForm';
import * as productRoutes from '@/routes/admin/products/index';

type ProductData = Modules.Catalog.Data.ProductData;

export function useProducts(product?: ProductData) {
    return useResourceForm({
        name: product?.name ?? '',
        slug: product?.slug ?? '',
        description: product?.description ?? '',
        short_description: product?.short_description ?? '',
        sku: product?.sku ?? '',
        price: product?.price ?? 0,
        compare_at_price: product?.compare_at_price ?? 0,
        cost_price: product?.cost_price ?? 0,
        category_id: product?.category_id ?? null,
        brand_id: product?.brand_id ?? null,
        is_active: product?.is_active ?? true,
        is_featured: product?.is_featured ?? false,
        featured_image: null as File | string | null,
    }, {
        resourceId: product?.id,
        routes: {
            store: productRoutes.store.url(),
            update: product?.id ? productRoutes.update.url(product.id!) : '',
        }
    });
}
