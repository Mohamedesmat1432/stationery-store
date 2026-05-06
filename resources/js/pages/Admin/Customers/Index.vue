<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
import {
    Building2,
    Download,
    Mail,
    Pencil,
    Phone,
    RotateCcw,
    Trash,
    Trash2,
    Upload,
    UserCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import ResourceIndexLayout from '@/components/Admin/ResourceIndexLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ResourceExportModal from '@/components/ResourceExportModal.vue';
import ResourceFilterBar from '@/components/ResourceFilterBar.vue';
import ResourceImportModal from '@/components/ResourceImportModal.vue';
import ResourcePagination from '@/components/ResourcePagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
import * as customersRoutes from '@/routes/admin/customers/index';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Customers', href: customersRoutes.index.url() },
        ],
    },
});

// Types are automatically generated via spatie/laravel-typescript-transformer
type Customer = Modules.CRM.Data.CustomerData & { id: string };

const props = withDefaults(
    defineProps<{
        customers: {
            data: Customer[];
            links: any[];
            current_page: number;
            last_page: number;
            total: number;
        };
        filters: {
            filter?: {
                search?: string;
                group?: string;
                trash?: string;
            };
        };
        available_groups?: Modules.CRM.Data.CustomerGroupData[];
    }>(),
    {
        available_groups: () => [],
    },
);

const { searchQuery, showTrashed, extraFilters, applyFilters, clearFilters } =
    useResourceFilters(() => props.filters?.filter, {
        baseUrl: customersRoutes.index.url(),
    });

const groupFilter = computed({
    get: () => extraFilters.value.group || 'all',
    set: (val) => {
        extraFilters.value.group = val === 'all' ? undefined : val;
        applyFilters();
    },
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const selectableCustomers = computed(() => {
    return props.customers?.data?.filter((c) => !c.is_protected) ?? [];
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
} = useBulkActions(() => selectableCustomers.value, {
    entityName: 'customers',
    bulkActionRoute: customersRoutes.bulkAction,
    destroyRoute: (id) => customersRoutes.destroy(String(id)),
    restoreRoute: (id) => customersRoutes.restore(String(id)),
    forceDeleteRoute: (id) => customersRoutes.forceDelete(String(id)),
});

// Export/Import state
const isExportModalOpen = ref(false);
const isImportModalOpen = ref(false);

const allColumns = [
    { id: 'name', label: 'Name' },
    { id: 'email', label: 'Email' },
    { id: 'age', label: 'Age' },
    { id: 'group', label: 'Group' },
    { id: 'total_spent', label: 'Total Spent' },
    { id: 'orders_count', label: 'Orders Count' },
    { id: 'created_at', label: 'Created At' },
];
</script>

<template>
    <ResourceIndexLayout
        title="Customers"
        description="View and manage your customer base."
        :icon="UserCircle"
        :selected-count="selectedIds?.length ?? 0"
        :can-create="can('create_customers')"
        :create-url="customersRoutes.create.url()"
        create-label="Create Customer"
        :can-delete="can('delete_customers')"
        :can-restore="can('restore_customers')"
        :can-force-delete="can('force_delete_customers')"
        v-model:search-query="searchQuery"
        v-model:show-trashed="showTrashed"
        search-placeholder="Search by name, email, phone..."
        :pagination-links="customers?.links"
        :pagination-total="customers?.total"
        :pagination-count="customers?.data?.length"
        resource-name="customers"
        :confirm-state="confirmState"
        @search="applyFilters"
        @clear-filters="clearFilters"
        @bulk-delete="bulkAction('delete')"
        @bulk-restore="bulkAction('restore')"
        @bulk-force-delete="bulkAction('forceDelete')"
    >
        <template #header-actions>
            <Button
                v-if="can('export_customers')"
                variant="outline"
                class="flex items-center gap-2"
                @click="isExportModalOpen = true"
            >
                <Download class="h-4 w-4" /> {{ $t('Export') }}
            </Button>
            <Button
                v-if="can('import_customers')"
                variant="outline"
                class="flex items-center gap-2"
                @click="isImportModalOpen = true"
            >
                <Upload class="h-4 w-4" /> {{ $t('Import') }}
            </Button>
        </template>

        <template #extra-filters>
            <div class="flex min-w-50 items-center gap-2">
                <Deferred data="available_groups">
                    <template #fallback>
                        <Skeleton class="h-9 w-full" />
                    </template>
                    <Select v-model="groupFilter">
                        <SelectTrigger class="h-9" :aria-label="$t('Filter by Group')">
                            <SelectValue :placeholder="$t('Filter by Group')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('All Groups') }}</SelectItem>
                            <SelectItem
                                v-for="group in available_groups ?? []"
                                :key="group.id!"
                                :value="group.id"
                            >
                                {{ group.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </Deferred>
            </div>
        </template>

        <table class="w-full text-start text-sm">
            <thead
                class="border-b border-sidebar-border bg-sidebar text-xs text-muted-foreground uppercase"
            >
                <tr>
                    <th class="w-10 px-6 py-3 font-medium">
                        <Checkbox
                            :model-value="
                                isIndeterminate ? 'indeterminate' : isAllSelected
                            "
                            @update:model-value="toggleAll"
                        />
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Customer') }}
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Contact') }}
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Age') }}
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Group') }}
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Orders') }}
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Spent') }}
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Actions') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="customer in customers?.data ?? []"
                    :key="customer.id"
                    class="table-row-themed"
                >
                    <td class="px-6 py-4">
                        <Checkbox
                            v-if="selectableCustomers.some((c) => c.id === customer.id)"
                            :model-value="selectedIds.includes(customer.id)"
                            @update:model-value="toggleItem(customer.id)"
                        />
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span class="font-medium text-foreground">{{
                                customer.name || $t('No User Linked')
                            }}</span>
                            <span
                                v-if="customer.company_name"
                                class="flex items-center gap-1 text-xs text-muted-foreground"
                            >
                                <Building2 class="h-3 w-3" />
                                {{ customer.company_name }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-1">
                            <span
                                v-if="customer.email"
                                class="flex items-center gap-1 text-xs"
                            >
                                <Mail class="h-3 w-3" />
                                {{ customer.email }}
                            </span>
                            <span
                                v-if="customer.phone"
                                class="flex items-center gap-1 text-xs"
                            >
                                <Phone class="h-3 w-3" />
                                {{ customer.phone }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        {{ customer.age ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <Badge
                            variant="secondary"
                            class="text-foreground transition-all duration-150 [a&]:hover:border-primary/40 [a&]:hover:bg-primary/5 [a&]:hover:text-primary"
                            v-if="customer.group_name"
                        >
                            {{ customer.group_name }}
                        </Badge>
                        <span v-else class="text-xs text-muted-foreground italic">{{
                            $t('No Group')
                        }}</span>
                    </td>
                    <td class="px-6 py-4 text-start">
                        {{ customer.orders_count }}
                    </td>
                    <td class="px-6 py-4 text-start font-medium">
                        {{ formatCurrency(customer.total_spent) }}
                    </td>
                    <td class="flex items-center gap-2 px-6 py-4 text-start">
                        <template v-if="!customer.deleted_at">
                            <Button
                                v-if="can('update_customers')"
                                variant="outline"
                                size="icon"
                                class="h-8 w-8"
                                as-child
                            >
                                <Link :href="customersRoutes.edit.url(customer.id)">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button
                                v-if="can('delete_customers')"
                                variant="destructive"
                                size="icon"
                                class="h-8 w-8"
                                @click="deleteItem(customer.id)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </template>
                        <template v-else>
                            <Button
                                v-if="can('restore_customers')"
                                variant="outline"
                                size="icon"
                                class="h-8 w-8"
                                title="Restore"
                                @click="restoreItem(customer.id)"
                            >
                                <RotateCcw class="h-4 w-4" />
                            </Button>
                            <Button
                                v-if="can('force_delete_customers')"
                                variant="destructive"
                                size="icon"
                                class="h-8 w-8"
                                title="Force Delete"
                                @click="forceDeleteItem(customer.id)"
                            >
                                <Trash class="h-4 w-4" />
                            </Button>
                        </template>
                    </td>
                </tr>
                <tr v-if="(customers?.data?.length ?? 0) === 0">
                    <td
                        colspan="8"
                        class="px-6 py-8 text-center text-muted-foreground"
                    >
                        {{ $t('No customers found.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </ResourceIndexLayout>

    <ResourceExportModal
        v-model:open="isExportModalOpen"
        title="Export Customers"
        description="Choose the columns you want to include in your customer export."
        :columns="allColumns"
        :export-url="customersRoutes.exportMethod.url()"
    />

    <ResourceImportModal
        v-model:open="isImportModalOpen"
        title="Import Customers"
        description="Select an Excel or CSV file to import customers. The file should match the exported format."
        :import-url="customersRoutes.importMethod.url()"
    />
</template>
