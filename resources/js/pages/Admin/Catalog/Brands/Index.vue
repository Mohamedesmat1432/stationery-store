<script setup lang="ts">
import { Head, Link, Deferred } from '@inertiajs/vue3';
import { Tag, Pencil, Trash2, RotateCcw, Trash, Globe, ExternalLink, Download, Upload, Power } from 'lucide-vue-next';
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
import * as brandRoutes from '@/routes/admin/brands/index';

type BrandData = Modules.Catalog.Data.BrandData;

const props = defineProps<{
    brands?: {
        data: BrandData[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        filter?: {
            search?: string;
            trash?: string;
            is_active?: string;
        };
    };
}>();

const { searchQuery, showTrashed, extraFilters, applyFilters, clearFilters } = useResourceFilters(
    () => props.filters?.filter,
    {
        baseUrl: brandRoutes.index.url(),
    },
);

const statusFilter = computed({
    get: () => extraFilters.value.is_active || 'all',
    set: (val) => {
        extraFilters.value.is_active = val === 'all' ? undefined : val;
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
} = useBulkActions(() => (props.brands?.data ?? []) as any[], {
    entityName: 'brands',
    bulkActionRoute: brandRoutes.bulkAction,
    destroyRoute: (id) => brandRoutes.destroy({ brand: id as string }) as any,
    restoreRoute: (id) => brandRoutes.restore({ brand: id as string }) as any,
    forceDeleteRoute: (id) => brandRoutes.forceDelete({ brand: id as string }) as any,
});

// Export/Import state
const isExportModalOpen = ref(false);
const isImportModalOpen = ref(false);

const allColumns = [
    { id: 'name', label: 'Name' },
    { id: 'slug', label: 'Slug' },
    { id: 'website', label: 'Website' },
    { id: 'is_active', label: 'Status' },
    { id: 'created_at', label: 'Created At' },
];
</script>

<template>
    <Head title="Brands Management" />

    <ResourceIndexLayout
        title="Brands Management"
        description="Organize your catalog by brands and manufacturers."
        :icon="Tag"
        :selected-count="selectedIds?.length ?? 0"
        :can-create="can('create_brands')"
        :create-url="brandRoutes.create.url()"
        create-label="Add Brand"
        :can-delete="can('delete_brands')"
        :can-restore="can('restore_brands')"
        :can-force-delete="can('force_delete_brands')"
        :can-activate="can('update_brands')"
        :can-deactivate="can('update_brands')"
        v-model:search-query="searchQuery"
        v-model:show-trashed="showTrashed"
        search-placeholder="Search brands by name..."
        :pagination-links="brands?.links"
        :pagination-total="brands?.total"
        :pagination-count="brands?.data?.length"
        resource-name="brands"
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
                v-if="can('export_brands')"
                variant="outline"
                class="flex items-center gap-2"
                @click="isExportModalOpen = true"
            >
                <Download class="h-4 w-4" /> {{ $t('Export') }}
            </Button>
            <Button
                v-if="can('import_brands')"
                variant="outline"
                class="flex items-center gap-2"
                @click="isImportModalOpen = true"
            >
                <Upload class="h-4 w-4" /> {{ $t('Import') }}
            </Button>
        </template>

        <template #extra-filters>
            <div class="flex min-w-40 items-center gap-2">
                <Select v-model="statusFilter">
                    <SelectTrigger class="h-9 w-40">
                        <SelectValue :placeholder="$t('All Status')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ $t('All Status') }}</SelectItem>
                        <SelectItem value="1">{{ $t('Active') }}</SelectItem>
                        <SelectItem value="0">{{ $t('Disabled') }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </template>

        <Deferred data="brands">
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
                        <th class="px-6 py-3 text-start font-medium">{{ $t('Brand') }}</th>
                        <th class="px-6 py-3 text-start font-medium">{{ $t('Website') }}</th>
                        <th class="px-6 py-3 text-center font-medium">{{ $t('Products') }}</th>
                        <th class="px-6 py-3 text-center font-medium">{{ $t('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ $t('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="brand in brands?.data ?? []"
                        :key="brand.id!"
                        class="table-row-themed"
                    >
                        <td class="px-6 py-4">
                            <Checkbox
                                :model-value="selectedIds.includes(brand.id!)"
                                @update:model-value="toggleItem(brand.id!)"
                            />
                        </td>
                        <td class="px-6 py-4 font-medium">
                            <div class="flex items-center gap-3">
                                <div v-if="brand.logo" class="h-8 w-8 rounded bg-white p-1 shadow-sm flex items-center justify-center border overflow-hidden">
                                    <img :src="brand.logo" class="max-h-full max-w-full object-contain" />
                                </div>
                                <div v-else class="h-8 w-8 rounded bg-muted flex items-center justify-center text-muted-foreground">
                                    <Tag class="h-4 w-4" />
                                </div>
                                <span>{{ brand.name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <a 
                                v-if="brand.website" 
                                :href="brand.website" 
                                target="_blank" 
                                class="text-primary hover:underline flex items-center gap-1"
                            >
                                <Globe class="h-3 w-3" />
                                {{ brand.website.replace(/^https?:\/\/(www\.)?/, '').split('/')[0] }}
                                <ExternalLink class="h-2.5 w-2.5 opacity-50" />
                            </a>
                            <span v-else class="text-muted-foreground text-xs">—</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <Badge variant="outline">{{ brand.products_count ?? 0 }}</Badge>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <Badge :variant="brand.is_active ? 'default' : 'secondary'">
                                {{ brand.is_active ? $t('Active') : $t('Disabled') }}
                            </Badge>
                        </td>
                        <td class="flex items-center justify-end gap-2 px-6 py-4">
                            <template v-if="!brand.deleted_at">
                                <Button
                                    v-if="can('update_brands')"
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8"
                                    as-child
                                >
                                    <Link :href="brandRoutes.edit.url(brand.id!)">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    v-if="can('update_brands')"
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8"
                                    :class="brand.is_active ? 'text-amber-500 hover:text-amber-600' : 'text-emerald-500 hover:text-emerald-600'"
                                    as-child
                                >
                                    <Link 
                                        :href="brandRoutes.toggleActive({ brand: brand.id! }).url" 
                                        method="patch" 
                                        as="button" 
                                        preserve-scroll
                                    >
                                        <Power class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    v-if="can('delete_brands')"
                                    variant="destructive"
                                    size="icon"
                                    class="h-8 w-8"
                                    @click="deleteItem(brand.id!)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </template>
                            <template v-else>
                                <Button
                                    v-if="can('restore_brands')"
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8"
                                    title="Restore"
                                    @click="restoreItem(brand.id!)"
                                >
                                    <RotateCcw class="h-4 w-4" />
                                </Button>
                                <Button
                                    v-if="can('force_delete_brands')"
                                    variant="destructive"
                                    size="icon"
                                    class="h-8 w-8"
                                    title="Force Delete"
                                    @click="forceDeleteItem(brand.id!)"
                                >
                                    <Trash class="h-4 w-4" />
                                </Button>
                            </template>
                        </td>
                    </tr>
                    <tr v-if="(brands?.data?.length ?? 0) === 0">
                        <td colspan="6" class="px-6 py-8 text-center text-muted-foreground">
                            {{ $t('No brands found.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </Deferred>
    </ResourceIndexLayout>

    <ResourceExportModal
        v-model:open="isExportModalOpen"
        title="Export Brands"
        description="Choose the columns you want to include in your brand export."
        :columns="allColumns"
        :export-url="brandRoutes.exportMethod.url()"
    />

    <ResourceImportModal
        v-model:open="isImportModalOpen"
        title="Import Brands"
        description="Select an Excel or CSV file to import brands."
        :import-url="brandRoutes.importMethod.url()"
    />
</template>
