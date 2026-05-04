<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Package, Pencil, Trash2, RotateCcw, Trash } from 'lucide-vue-next';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ResourceFilterBar from '@/components/ResourceFilterBar.vue';
import ResourcePagination from '@/components/ResourcePagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { useBulkActions } from '@/composables/useBulkActions';
import { useResourceFilters } from '@/composables/useResourceFilters';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Products', href: '/admin/products' },
        ],
    },
});

const props = defineProps<{
    products: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        filter?: {
            search?: string;
            trash?: string;
        };
    };
}>();

const { searchQuery, showTrashed, applyFilters } = useResourceFilters(
    props.filters.filter,
    {
        baseUrl: '/admin/products',
    },
);

const {
    selectedIds,
    isAllSelected,
    isIndeterminate,
    toggleAll,
    toggleItem,
    can,
    bulkAction,
    deleteItem,
    restoreItem,
    forceDeleteItem,
    confirmState,
} = useBulkActions(() => props.products.data, {
    entityName: 'products',
    bulkActionRoute: (() => ({ url: '/admin/products/bulk-action' })) as any,
    destroyRoute: (id: string | number) => ({ url: `/admin/products/${id}` }),
    restoreRoute: (id: string | number) => ({ url: `/admin/products/${id}/restore` }),
    forceDeleteRoute: (id: string | number) => ({ url: `/admin/products/${id}/force-delete` }),
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <Head :title="$t('Products Management')" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <Card>
            <AdminPageHeader
                title="Products"
                description="Manage your store's products, pricing, and status."
                :icon="Package"
                :selected-count="selectedIds.length"
                :show-trashed="showTrashed"
                :can-create="can('create_products')"
                create-url="/admin/products/create"
                create-label="Add Product"
                :can-delete="can('delete_products')"
                :can-restore="can('restore_products')"
                :can-force-delete="can('force_delete_products')"
                @bulk-delete="bulkAction('delete')"
                @bulk-restore="bulkAction('restore')"
                @bulk-force-delete="bulkAction('forceDelete')"
            />

            <CardContent>
                <ResourceFilterBar
                    v-model:search="searchQuery"
                    v-model:trashed="showTrashed"
                    search-placeholder="Search by name, SKU..."
                    @search="applyFilters"
                    @update:trashed="applyFilters"
                />

                <div
                    class="overflow-x-auto rounded-md border border-sidebar-border"
                >
                    <table class="w-full text-start text-sm">
                        <thead
                            class="border-b border-sidebar-border bg-sidebar text-xs text-muted-foreground uppercase"
                        >
                            <tr>
                                <th class="w-10 px-6 py-3 font-medium">
                                    <Checkbox
                                        :model-value="
                                            isIndeterminate
                                                ? 'indeterminate'
                                                : isAllSelected
                                        "
                                        @update:model-value="toggleAll"
                                    />
                                </th>
                                <th class="px-6 py-3 font-medium">
                                    {{ $t('Product') }}
                                </th>
                                <th class="px-6 py-3 font-medium">SKU</th>
                                <th class="px-6 py-3 font-medium">
                                    {{ $t('Price') }}
                                </th>
                                <th class="px-6 py-3 text-center font-medium">
                                    {{ $t('Status') }}
                                </th>
                                <th class="px-6 py-3 text-end font-medium">
                                    {{ $t('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="product in products.data"
                                :key="product.id"
                                class="table-row-themed"
                            >
                                <td class="px-6 py-4">
                                    <Checkbox
                                        :model-value="
                                            selectedIds.includes(product.id)
                                        "
                                        @update:model-value="
                                            toggleItem(product.id)
                                        "
                                    />
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    {{ product.name }}
                                </td>
                                <td class="px-6 py-4 text-muted-foreground">
                                    {{ product.sku }}
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    {{ formatCurrency(product.price) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge
                                        :variant="
                                            product.is_active
                                                ? 'default'
                                                : 'secondary'
                                        "
                                    >
                                        {{
                                            product.is_active
                                                ? $t('Active')
                                                : $t('Draft')
                                        }}
                                    </Badge>
                                </td>
                                <td class="space-x-2 px-6 py-4 text-end">
                                    <template v-if="!product.deleted_at">
                                        <Button
                                            v-if="can('update_products')"
                                            variant="outline"
                                            size="icon"
                                            class="h-8 w-8"
                                            as-child
                                        >
                                            <Link
                                                :href="`/admin/products/${product.id}/edit`"
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            v-if="can('delete_products')"
                                            variant="destructive"
                                            size="icon"
                                            class="h-8 w-8"
                                            @click="deleteItem(product.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </template>
                                    <template v-else>
                                        <Button
                                            v-if="can('restore_products')"
                                            variant="outline"
                                            size="icon"
                                            class="h-8 w-8"
                                            title="Restore"
                                            @click="restoreItem(product.id)"
                                        >
                                            <RotateCcw class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            v-if="can('force_delete_products')"
                                            variant="destructive"
                                            size="icon"
                                            class="h-8 w-8"
                                            title="Force Delete"
                                            @click="forceDeleteItem(product.id)"
                                        >
                                            <Trash class="h-4 w-4" />
                                        </Button>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="products.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-8 text-center text-muted-foreground"
                                >
                                    {{ $t('No products found.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <ResourcePagination
                    :links="products.links"
                    :total="products.total"
                    :count="products.data.length"
                    resource-name="products"
                />
            </CardContent>
        </Card>
    </div>

    <ConfirmDialog
        v-model:open="confirmState.isOpen"
        :title="confirmState.title"
        :description="confirmState.description"
        :variant="confirmState.variant"
        :confirm-label="confirmState.confirmLabel"
        :loading="confirmState.loading"
        @confirm="confirmState.onConfirm"
    />
</template>
