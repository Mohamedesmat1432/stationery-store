import { router } from '@inertiajs/vue3';
import { computed, toValue } from 'vue';
import type { MaybeRefOrGetter } from 'vue';
import { useBulkActions } from '@/composables/useBulkActions';
import * as categoriesRoutes from '@/routes/admin/categories/index';

type CategoryData = Modules.Catalog.Data.CategoryData;

export function useCategoryActions(categoriesSource: MaybeRefOrGetter<CategoryData[]>) {
    // Flatten the tree for bulk action selection logic
    const flattenedCategories = computed(() => {
        const items = toValue(categoriesSource) || [];
        const result: CategoryData[] = [];
        
        const flatten = (nodes: CategoryData[]) => {
            nodes.forEach(node => {
                result.push(node);

                if (node.children && node.children.length > 0) {
                    flatten(node.children);
                }
            });
        };
        
        flatten(items);

        return result;
    });

    const bulk = useBulkActions(flattenedCategories as any, {
        entityName: 'categories',
        bulkActionRoute: categoriesRoutes.bulkAction,
        destroyRoute: (id) => categoriesRoutes.destroy(String(id)),
        restoreRoute: (id) => categoriesRoutes.restore(String(id)),
        forceDeleteRoute: (id) => categoriesRoutes.forceDelete(String(id)),
    });

    const toggleActive = (category: CategoryData) => {
        // Optimistic update
        const originalStatus = category.is_active;
        category.is_active = !originalStatus;

        router.patch(categoriesRoutes.toggleActive(category.id!).url, {}, {
            preserveScroll: true,
            onError: () => {
                // Rollback on error
                category.is_active = originalStatus;
            }
        });
    };

    let reorderTimeout: any = null;
    const reorder = (tree: any[]) => {
        if (reorderTimeout) {
clearTimeout(reorderTimeout);
}
        
        reorderTimeout = setTimeout(() => {
            router.post(categoriesRoutes.reorder().url, { tree }, {
                preserveScroll: true,
            });
        }, 1000); // 1 second debounce
    };

    return {
        ...bulk,
        toggleActive,
        reorder,
    };
}
