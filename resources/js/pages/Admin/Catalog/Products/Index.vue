<script setup lang="ts">
import { Deferred, Link } from '@inertiajs/vue3';
import { Package, Pencil, Trash2, RotateCcw, Trash, Download, Upload, Power } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import ResourceIndexLayout from '@/components/Admin/ResourceIndexLayout.vue';
import ResourceExportModal from '@/components/ResourceExportModal.vue';
import ResourceImportModal from '@/components/ResourceImportModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { useBulkActions } from '@/composables/useBulkActions';
import { useResourceFilters } from '@/composables/useResourceFilters';
import { formatCurrency } from '@/lib/format';
import * as productsRoutes from '@/routes/admin/products/index';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Products', href: productsRoutes.index.url() },
        ],
    },
});

const props = defineProps<{
    products?: {
        data: Modules.Catalog.Data.ProductData[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        filter?: {
            search?: string;
            trash?: string;
            category_id?: string;
            brand_id?: string;
        };
    };
    categories?: Modules.Catalog.Data.CategoryData[];
    brands?: Modules.Catalog.Data.BrandData[];
}>();

const { searchQuery, showTrashed, extraFilters, applyFilters, clearFilters } = useResourceFilters(
    () => props.filters?.filter,
    {
        baseUrl: productsRoutes.index.url(),
    },
);

const categoryFilter = computed({
    get: () => extraFilters.value.category_id || 'all',
    set: (val) => {
        extraFilters.value.category_id = val === 'all' ? undefined : val;
        applyFilters();
    },
});

const brandFilter = computed({
    get: () => extraFilters.value.brand_id || 'all',
    set: (val) => {
        extraFilters.value.brand_id = val === 'all' ? undefined : val;
        applyFilters();
    },
});

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
} = useBulkActions(() => (props.products?.data as any) ?? [], {
    entityName: 'products',
    // These will be available after Wayfinder generation, fallback to strings for now if needed
    // or use the wayfinder().url() pattern if preferred.
    // For now I'll use the pattern seen in Users/Index.vue
    bulkActionRoute: productsRoutes.bulkAction,
    destroyRoute: (id: string | number) => productsRoutes.destroy({ product: id as string }) as any,
    restoreRoute: (id: string | number) => productsRoutes.restore({ product: id as string }) as any,
    forceDeleteRoute: (id: string | number) => productsRoutes.forceDelete({ product: id as string }) as any,
});

const displayCurrency = (amount: number) => formatCurrency(amount);

// Export/Import state
const isExportModalOpen = ref(false);
const isImportModalOpen = ref(false);

const allColumns = [
    { id: 'name', label: 'Name' },
    { id: 'sku', label: 'SKU' },
    { id: 'price', label: 'Price' },
    { id: 'category_id', label: 'Category' },
    { id: 'brand_id', label: 'Brand' },
    { id: 'is_active', label: 'Status' },
    { id: 'created_at', label: 'Created At' },
];
</script>

<template>
    <ResourceIndexLayout
        title="Products Management"
        description="Manage your store's products, pricing, and status."
        :icon="Package"
        :selected-count="selectedIds?.length ?? 0"
        :can-create="can('create_products')"
        :create-url="productsRoutes.create.url()"
        create-label="Add Product"
        :can-delete="can('delete_products')"
        :can-restore="can('restore_products')"
        :can-force-delete="can('force_delete_products')"
        :can-activate="can('update_products')"
        :can-deactivate="can('update_products')"
        v-model:search-query="searchQuery"
        v-model:show-trashed="showTrashed"
        search-placeholder="Search by name, SKU..."
        :pagination-links="products?.links"
        :pagination-total="products?.total"
        :pagination-count="products?.data?.length"
        resource-name="products"
        v-model:confirm-state="confirmState"
        @search="applyFilters"
        @clear-filters="clearFilters"
        @bulk-delete="bulkAction('delete')"
        @bulk-restore="bulkAction('restore')"
        @bulk-force-delete="bulkAction('forceDelete')"
        @bulk-activate="bulkAction('activate')"
        @bulk-deactivate="bulkAction('deactivate')"
    >
        <template #header-actions>
            <Button
                v-if="can('export_products')"
                variant="outline"
                class="flex items-center gap-2"
                @click="isExportModalOpen = true"
            >
                <Download class="h-4 w-4" /> {{ $t('Export') }}
            </Button>
            <Button
                v-if="can('import_products')"
                variant="outline"
                class="flex items-center gap-2"
                @click="isImportModalOpen = true"
            >
                <Upload class="h-4 w-4" /> {{ $t('Import') }}
            </Button>
        </template>

        <template #extra-filters>
            <div class="flex flex-wrap items-center gap-2">
                <Deferred data="categories">
                    <template #fallback>
                        <Skeleton class="h-9 w-40" />
                    </template>
                    <Select v-model="categoryFilter">
                        <SelectTrigger class="h-9 w-40">
                            <SelectValue :placeholder="$t('All Categories')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('All Categories') }}</SelectItem>
                            <SelectItem
                                v-for="category in categories ?? []"
                                :key="category.id!"
                                :value="category.id!"
                            >
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </Deferred>

                <Deferred data="brands">
                    <template #fallback>
                        <Skeleton class="h-9 w-40" />
                    </template>
                    <Select v-model="brandFilter">
                        <SelectTrigger class="h-9 w-40">
                            <SelectValue :placeholder="$t('All Brands')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('All Brands') }}</SelectItem>
                            <SelectItem
                                v-for="brand in brands ?? []"
                                :key="brand.id!"
                                :value="brand.id!"
                            >
                                {{ brand.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </Deferred>
            </div>
        </template>

        <Deferred data="products">
            <template #fallback>
                <div class="space-y-4 p-6">
                    <Skeleton class="h-10 w-full" v-for="i in 8" :key="i" />
                </div>
            </template>
            <table class="w-full text-start text-sm">
            <thead
                class="border-b border-sidebar-border bg-sidebar text-xs text-muted-foreground uppercase"
            >
                <tr>
                    <th class="w-10 px-6 py-3 font-medium">
                        <Checkbox
                            :model-value="isIndeterminate ? 'indeterminate' : isAllSelected"
                            @update:model-value="toggleAll"
                        />
                    </th>
                    <th class="px-6 py-3 text-start font-medium">{{ $t('Product') }}</th>
                    <th class="px-6 py-3 text-start font-medium">{{ $t('SKU') }}</th>
                    <th class="px-6 py-3 text-start font-medium">{{ $t('Price') }}</th>
                    <th class="px-6 py-3 text-center font-medium">{{ $t('Status') }}</th>
                    <th class="px-6 py-3 text-end font-medium">{{ $t('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="product in products?.data ?? []"
                    :key="product.id!"
                    class="table-row-themed"
                >
                    <td class="px-6 py-4">
                        <Checkbox
                            :model-value="selectedIds.includes(product.id!)"
                            @update:model-value="toggleItem(product.id!)"
                        />
                    </td>
                    <td class="px-6 py-4 font-medium">
                        <div class="flex flex-col">
                            <span>{{ product.name }}</span>
                            <span v-if="product.category" class="text-xs text-muted-foreground">
                                {{ product.category.name }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-muted-foreground">{{ product.sku }}</td>
                    <td class="px-6 py-4 font-medium">{{ displayCurrency(product.price) }}</td>
                    <td class="px-6 py-4 text-center">
                        <Badge :variant="product.is_active ? 'default' : 'secondary'">
                            {{ product.is_active ? $t('Active') : $t('Draft') }}
                        </Badge>
                    </td>
                    <td class="flex items-center justify-end gap-2 px-6 py-4">
                        <template v-if="!product.deleted_at">
                            <Button
                                v-if="can('update_products')"
                                variant="outline"
                                size="icon"
                                class="h-8 w-8"
                                as-child
                            >
                                <Link :href="productsRoutes.edit.url({ product: product.id! })">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button
                                v-if="can('update_products')"
                                variant="outline"
                                size="icon"
                                class="h-8 w-8"
                                :class="product.is_active ? 'text-amber-500 hover:text-amber-600' : 'text-emerald-500 hover:text-emerald-600'"
                                as-child
                            >
                                <Link 
                                    :href="productsRoutes.toggleActive({ product: product.id! }).url" 
                                    method="patch" 
                                    as="button" 
                                    preserve-scroll
                                >
                                    <Power class="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button
                                v-if="can('delete_products')"
                                variant="destructive"
                                size="icon"
                                class="h-8 w-8"
                                @click="deleteItem(product.id!)"
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
                                @click="restoreItem(product.id!)"
                            >
                                <RotateCcw class="h-4 w-4" />
                            </Button>
                            <Button
                                v-if="can('force_delete_products')"
                                variant="destructive"
                                size="icon"
                                class="h-8 w-8"
                                title="Force Delete"
                                @click="forceDeleteItem(product.id!)"
                            >
                                <Trash class="h-4 w-4" />
                            </Button>
                        </template>
                    </td>
                </tr>
                <tr v-if="(products?.data?.length ?? 0) === 0">
                    <td colspan="6" class="px-6 py-8 text-center text-muted-foreground">
                        {{ $t('No products found.') }}
                    </td>
                </tr>
            </tbody>
        </table>
        </Deferred>
    </ResourceIndexLayout>

    <ResourceExportModal
        v-model:open="isExportModalOpen"
        title="Export Products"
        description="Choose the columns you want to include in your product export."
        :columns="allColumns"
        :export-url="productsRoutes.exportMethod.url()"
    />

    <ResourceImportModal
        v-model:open="isImportModalOpen"
        title="Import Products"
        description="Select an Excel or CSV file to import products."
        :import-url="productsRoutes.importMethod.url()"
    />
</template>
