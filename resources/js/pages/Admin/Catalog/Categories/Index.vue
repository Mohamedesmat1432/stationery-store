<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Plus, Search, Trash2, FolderTree, Layers, Download, Upload } from 'lucide-vue-next';
import ResourceIndexLayout from '@/components/Admin/ResourceIndexLayout.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import CategoryTreeNode from '@/components/Admin/Catalog/CategoryTreeNode.vue';
import { useCategoryActions } from '@/composables/useCategoryActions';
import ResourceExportModal from '@/components/ResourceExportModal.vue';
import ResourceImportModal from '@/components/ResourceImportModal.vue';
import { useResourceFilters } from '@/composables/useResourceFilters';
import * as categoriesRoutes from '@/routes/admin/categories/index';
import draggable from 'vuedraggable';

type CategoryData = Modules.Catalog.Data.CategoryData;

const props = defineProps<{
    categories: {
        data: CategoryData[];
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

const localCategories = ref([...(props.categories?.data ?? [])]);

watch(
    () => props.categories?.data,
    (newVal) => {
        if (Array.isArray(newVal)) {
            localCategories.value = [...newVal];
        }
    },
    { deep: true }
);

const {
    selectedIds,
    isAllSelected,
    isIndeterminate,
    toggleAll,
    toggleItem,
    bulkAction,
    deleteItem,
    restoreItem,
    forceDeleteItem,
    toggleActive,
    confirmState,
    reorder,
    can,
} = useCategoryActions(localCategories);

// Provide actions to recursive tree nodes to avoid event bubbling overhead
import { provide } from 'vue';
provide('categoryActions', {
    toggleSelect: toggleItem,
    toggleActive,
    delete: deleteItem,
    restore: restoreItem,
    forceDelete: forceDeleteItem,
    reorder: () => reorder(localCategories.value),
});

const { searchQuery, showTrashed, applyFilters, clearFilters } = useResourceFilters(
    () => props.filters?.filter,
    { baseUrl: categoriesRoutes.index().url }
);

const isExportModalOpen = ref(false);
const isImportModalOpen = ref(false);

const exportColumns = [
    { id: 'id', label: 'ID' },
    { id: 'name', label: 'Name' },
    { id: 'slug', label: 'Slug' },
    { id: 'parent_id', label: 'Parent ID' },
    { id: 'is_active', label: 'Status' },
    { id: 'is_featured', label: 'Featured' },
    { id: 'created_at', label: 'Created At' },
];
</script>

<template>
    <ResourceIndexLayout
        title="Categories"
        description="View and manage your product hierarchy and catalog structure."
        :icon="FolderTree"
        :selected-count="selectedIds?.length ?? 0"
        :can-create="can('create_categories')"
        :create-url="categoriesRoutes.create().url"
        create-label="Create Category"
        :can-delete="can('delete_categories')"
        :can-restore="can('restore_categories')"
        :can-force-delete="can('force_delete_categories')"
        v-model:search-query="searchQuery"
        v-model:show-trashed="showTrashed"
        search-placeholder="Search by category name..."
        :pagination-links="categories?.links"
        :pagination-total="categories?.total"
        :pagination-count="categories?.data?.length"
        resource-name="categories"
        :confirm-state="confirmState"
        @search="applyFilters"
        @clear-filters="clearFilters"
        @bulk-delete="bulkAction('delete')"
        @bulk-restore="bulkAction('restore')"
        @bulk-force-delete="bulkAction('forceDelete')"
    >
        <template #header-actions>
            <Button
                v-if="can('export_categories')"
                variant="outline"
                class="flex items-center gap-2"
                @click="isExportModalOpen = true"
            >
                <Download class="h-4 w-4" /> {{ $t('Export') }}
            </Button>
            <Button
                v-if="can('import_categories')"
                variant="outline"
                class="flex items-center gap-2"
                @click="isImportModalOpen = true"
            >
                <Upload class="h-4 w-4" /> {{ $t('Import') }}
            </Button>
            <Button
                v-if="!searchQuery && !showTrashed"
                variant="outline"
                class="flex items-center gap-2"
                @click="reorder(localCategories)"
            >
                <Layers class="h-4 w-4" /> {{ $t('Reorder Tree') }}
            </Button>
        </template>

        <div class="rounded-md border-sidebar-border overflow-x-auto">
            <!-- Table Header -->
            <div class="grid grid-cols-[48px_1fr_80px] sm:grid-cols-[48px_1fr_100px_200px] md:grid-cols-[48px_1fr_100px_120px_200px] lg:grid-cols-[48px_1fr_100px_120px_120px_200px] border-b border-sidebar-border bg-sidebar text-[10px] sm:text-xs text-muted-foreground uppercase font-medium">
                <div class="px-4 sm:px-6 py-3 flex items-center">
                    <Checkbox
                        :model-value="isIndeterminate ? 'indeterminate' : isAllSelected"
                        @update:model-value="toggleAll"
                    />
                </div>
                <div class="px-4 sm:px-6 py-3 flex items-center">{{ $t('Category Name & Slug') }}</div>
                <div class="hidden sm:flex px-4 sm:px-6 py-3 items-center">{{ $t('Status') }}</div>
                <div class="hidden md:flex px-4 sm:px-6 py-3 items-center">{{ $t('Subcategories') }}</div>
                <div class="hidden lg:flex px-4 sm:px-6 py-3 items-center">{{ $t('Product Count') }}</div>
                <div class="px-4 sm:px-6 py-3 flex items-center justify-end">{{ $t('Actions') }}</div>
            </div>

            <!-- Draggable Tree Body -->
            <div class="divide-y divide-sidebar-border">
                <draggable
                    v-model="localCategories"
                    item-key="id"
                    handle=".cursor-grab"
                    @end="reorder(localCategories)"
                    :animation="200"
                    ghost-class="opacity-50"
                    :disabled="!!searchQuery || showTrashed"
                >
                    <template #item="{ element }">
                        <CategoryTreeNode
                            :category="element"
                            :depth="0"
                            :selected-ids="selectedIds"
                            :is-draggable-disabled="!!searchQuery || showTrashed"
                        />
                    </template>
                </draggable>

                <div v-if="(categories?.data?.length ?? 0) === 0" class="px-6 py-8 text-center text-muted-foreground">
                    {{ $t('No categories found.') }}
                </div>
            </div>
        </div>
    </ResourceIndexLayout>

    <ResourceExportModal
        v-model:open="isExportModalOpen"
        :export-url="categoriesRoutes.exportMethod.url()"
        :columns="exportColumns"
        title="Export Categories"
        description="Choose the columns you want to include in your category export."
    />

    <ResourceImportModal
        v-model:open="isImportModalOpen"
        :import-url="categoriesRoutes.importMethod.url()"
        title="Import Categories"
        description="Select an Excel or CSV file to import categories. The file should match the exported format."
    />
</template>
