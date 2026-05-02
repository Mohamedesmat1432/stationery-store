<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Users, Pencil, Trash2, RotateCcw, Trash, Download, Upload } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ResourceFilterBar from '@/components/ResourceFilterBar.vue';
import ResourcePagination from '@/components/ResourcePagination.vue';
import ResourceExportModal from '@/components/ResourceExportModal.vue';
import ResourceImportModal from '@/components/ResourceImportModal.vue';
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
            { title: 'Customer Groups', href: '/admin/customer-groups' },
        ],
    },
});

const props = defineProps<{
    groups: {
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
        baseUrl: '/admin/customer-groups',
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
} = useBulkActions(() => props.groups.data, {
    entityName: 'customer groups',
    bulkActionUrl: '/admin/customer-groups/bulk-action',
    resourceUrl: '/admin/customer-groups',
});

// Export/Import state
const isExportModalOpen = ref(false);
const isImportModalOpen = ref(false);

const allColumns = [
    { id: 'name', label: 'Name' },
    { id: 'slug', label: 'Slug' },
    { id: 'discount_percentage', label: 'Discount Percentage' },
    { id: 'is_active', label: 'Status' },
    { id: 'created_at', label: 'Created At' },
];
</script>

<template>
    <Head :title="$t('Customer Groups')" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <Card>
            <AdminPageHeader
                title="Customer Groups"
                description="Manage customer segments and group-level discounts."
                :icon="Users"
                :selected-count="selectedIds.length"
                :show-trashed="showTrashed"
                :can-create="can('create_customer_groups')"
                create-url="/admin/customer-groups/create"
                create-label="Create Group"
                :can-delete="can('delete_customer_groups')"
                :can-restore="can('restore_customer_groups')"
                :can-force-delete="can('force_delete_customer_groups')"
                @bulk-delete="bulkAction('delete')"
                @bulk-restore="bulkAction('restore')"
                @bulk-force-delete="bulkAction('forceDelete')"
            >
                <template #actions>
                    <Button
                        v-if="can('export_customer_groups')"
                        variant="outline"
                        class="flex items-center gap-2"
                        @click="isExportModalOpen = true"
                    >
                        <Download class="h-4 w-4" /> {{ $t('Export') }}
                    </Button>
                    <Button
                        v-if="can('import_customer_groups')"
                        variant="outline"
                        class="flex items-center gap-2"
                        @click="isImportModalOpen = true"
                    >
                        <Upload class="h-4 w-4" /> {{ $t('Import') }}
                    </Button>
                </template>
            </AdminPageHeader>

            <CardContent>
                <ResourceFilterBar
                    v-model:search="searchQuery"
                    v-model:trashed="showTrashed"
                    search-placeholder="Search groups..."
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
                                <th class="px-6 py-3 text-start font-medium">
                                    {{ $t('Name') }}
                                </th>
                                <th class="px-6 py-3 text-start font-medium">
                                    {{ $t('Slug') }}
                                </th>
                                <th class="px-6 py-3 text-start font-medium">
                                    {{ $t('Discount') }}
                                </th>
                                <th class="px-6 py-3 text-start font-medium">
                                    {{ $t('Customers') }}
                                </th>
                                <th class="px-6 py-3 text-start font-medium">
                                    {{ $t('Status') }}
                                </th>
                                <th class="px-6 py-3 text-start font-medium">
                                    {{ $t('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="group in groups.data"
                                :key="group.id"
                                class="border-b border-sidebar-border transition-colors last:border-0 hover:bg-sidebar-accent/50"
                            >
                                <td class="px-6 py-4">
                                    <Checkbox
                                        :model-value="
                                            selectedIds.includes(group.id)
                                        "
                                        @update:model-value="
                                            toggleItem(group.id)
                                        "
                                    />
                                </td>
                                <td class="px-6 py-4 text-start font-medium">
                                    {{ group.name }}
                                </td>
                                <td
                                    class="px-6 py-4 text-start text-muted-foreground"
                                >
                                    {{ group.slug }}
                                </td>
                                <td class="px-6 py-4 text-start">
                                    <Badge variant="secondary"
                                        >{{ group.discount_percentage }}%</Badge
                                    >
                                </td>
                                <td
                                    class="px-6 py-4 text-start text-muted-foreground"
                                >
                                    {{ group.customers_count }}
                                </td>
                                <td class="px-6 py-4 text-start">
                                    <Badge
                                        :variant="
                                            group.is_active
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{
                                            group.is_active
                                                ? $t('Active')
                                                : $t('Inactive')
                                        }}
                                    </Badge>
                                </td>
                                <td class="space-x-2 px-6 py-4 text-start">
                                    <template v-if="!group.deleted_at">
                                        <Button
                                            v-if="can('update_customer_groups')"
                                            variant="outline"
                                            size="icon"
                                            class="h-8 w-8"
                                            as-child
                                        >
                                            <Link
                                                :href="`/admin/customer-groups/${group.id}/edit`"
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            v-if="can('delete_customer_groups')"
                                            variant="destructive"
                                            size="icon"
                                            class="h-8 w-8"
                                            @click="deleteItem(group.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </template>
                                    <template v-else>
                                        <Button
                                            v-if="
                                                can('restore_customer_groups')
                                            "
                                            variant="outline"
                                            size="icon"
                                            class="h-8 w-8"
                                            title="Restore"
                                            @click="restoreItem(group.id)"
                                        >
                                            <RotateCcw class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            v-if="
                                                can(
                                                    'force_delete_customer_groups',
                                                )
                                            "
                                            variant="destructive"
                                            size="icon"
                                            class="h-8 w-8"
                                            title="Force Delete"
                                            @click="forceDeleteItem(group.id)"
                                        >
                                            <Trash class="h-4 w-4" />
                                        </Button>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="groups.data.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-8 text-center text-muted-foreground"
                                >
                                    {{ $t('No customer groups found.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <ResourcePagination
                    :links="groups.links"
                    :total="groups.total"
                    :count="groups.data.length"
                    resource-name="customer groups"
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

    <ResourceExportModal
        v-model:open="isExportModalOpen"
        title="Export Customer Groups"
        description="Choose the columns you want to include in your customer group export."
        :columns="allColumns"
        export-url="/admin/customer-groups/export"
    />

    <ResourceImportModal
        v-model:open="isImportModalOpen"
        title="Import Customer Groups"
        description="Select an Excel or CSV file to import customer groups. The file should match the exported format."
        import-url="/admin/customer-groups/import"
    />
</template>
