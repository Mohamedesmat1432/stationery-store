<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { UserCircle, Pencil, Trash2, RotateCcw, Trash, Building2, Mail, Phone } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, computed } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { useBulkActions } from '@/composables/useBulkActions';
import { useResourceFilters } from '@/composables/useResourceFilters';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import ResourceFilterBar from '@/components/ResourceFilterBar.vue';
import ResourcePagination from '@/components/ResourcePagination.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Customers', href: '/admin/customers' },
        ],
    },
});

const props = defineProps<{
    customers: {
        data: any[];
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
    available_groups: any[];
}>();

const { searchQuery, showTrashed, extraFilters, applyFilters } = useResourceFilters(props.filters.filter, {
    baseUrl: '/admin/customers',
});

const groupFilter = computed({
    get: () => extraFilters.value.group || 'all',
    set: (val) => {
        extraFilters.value.group = val === 'all' ? undefined : val;
        applyFilters();
    }
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

const {
    selectedIds, isAllSelected, isIndeterminate, toggleAll, toggleItem,
    can, bulkAction, deleteItem, restoreItem, forceDeleteItem,
    confirmState
} = useBulkActions(() => props.customers.data, {
    entityName: 'customers',
    bulkActionUrl: '/admin/customers/bulk-action',
    resourceUrl: '/admin/customers',
});
</script>

<template>
    <Head :title="$t('Customers')" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <Card>
            <AdminPageHeader
                title="Customers"
                description="View and manage your customer base."
                :icon="UserCircle"
                :selected-count="selectedIds.length"
                :show-trashed="showTrashed"
                :can-create="can('create_customers')"
                create-url="/admin/customers/create"
                create-label="Create Customer"
                :can-delete="can('delete_customers')"
                :can-restore="can('restore_customers')"
                :can-force-delete="can('force_delete_customers')"
                @bulk-delete="bulkAction('delete')"
                @bulk-restore="bulkAction('restore')"
                @bulk-force-delete="bulkAction('forceDelete')"
            />

            <CardContent>
                <ResourceFilterBar
                    v-model:search="searchQuery"
                    v-model:trashed="showTrashed"
                    search-placeholder="Search by name, email, phone..."
                    @search="applyFilters"
                    @update:trashed="applyFilters"
                >
                    <template #filters>
                        <div class="flex items-center gap-2 min-w-[200px]">
                            <Select v-model="groupFilter">
                                <SelectTrigger class="h-9">
                                    <SelectValue :placeholder="$t('Filter by Group')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{{ $t('All Groups') }}</SelectItem>
                                    <SelectItem 
                                        v-for="group in available_groups" 
                                        :key="group.id" 
                                        :value="group.id"
                                    >
                                        {{ group.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </template>
                </ResourceFilterBar>

                <div class="rounded-md border border-sidebar-border overflow-x-auto">
                    <table class="w-full text-sm text-start">
                        <thead class="text-xs text-muted-foreground uppercase bg-sidebar border-b border-sidebar-border">
                            <tr>
                                <th class="px-4 py-3 font-medium w-10">
                                    <Checkbox 
                                        :model-value="isIndeterminate ? 'indeterminate' : isAllSelected"
                                        @update:model-value="toggleAll"
                                    />
                                </th>
                                <th class="px-4 py-3 font-medium">{{ $t('Customer') }}</th>
                                <th class="px-4 py-3 font-medium">{{ $t('Contact') }}</th>
                                <th class="px-4 py-3 font-medium">{{ $t('Group') }}</th>
                                <th class="px-4 py-3 font-medium text-end">{{ $t('Orders') }}</th>
                                <th class="px-4 py-3 font-medium text-end">{{ $t('Spent') }}</th>
                                <th class="px-4 py-3 font-medium text-end">{{ $t('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-sidebar/50 transition-colors">
                                <td class="px-4 py-3">
                                    <Checkbox 
                                        :model-value="selectedIds.includes(customer.id)"
                                        @update:model-value="toggleItem(customer.id)"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-foreground">{{ customer.name || $t('No User Linked') }}</span>
                                        <span v-if="customer.company_name" class="text-xs text-muted-foreground flex items-center gap-1">
                                            <Building2 class="w-3 h-3" /> {{ customer.company_name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1">
                                        <span v-if="customer.email" class="text-xs flex items-center gap-1">
                                            <Mail class="w-3 h-3" /> {{ customer.email }}
                                        </span>
                                        <span v-if="customer.phone" class="text-xs flex items-center gap-1">
                                            <Phone class="w-3 h-3" /> {{ customer.phone }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge variant="outline" v-if="customer.group_name">
                                        {{ customer.group_name }}
                                    </Badge>
                                    <span v-else class="text-xs text-muted-foreground italic">{{ $t('No Group') }}</span>
                                </td>
                                <td class="px-4 py-3 text-end">{{ customer.orders_count }}</td>
                                <td class="px-4 py-3 text-end font-medium">{{ formatCurrency(customer.total_spent) }}</td>
                                <td class="px-4 py-3 text-end">
                                    <div class="flex justify-end gap-2">
                                        <template v-if="!customer.deleted_at">
                                            <Button :disabled="!can('update_customers')" variant="ghost" size="icon" as-child>
                                                <Link v-if="can('update_customers')" :href="`/admin/customers/${customer.id}/edit`">
                                                    <Pencil class="w-4 h-4" />
                                                </Link>
                                                <span v-else class="flex items-center justify-center opacity-50">
                                                    <Pencil class="w-4 h-4" />
                                                </span>
                                            </Button>
                                            <Button :disabled="!can('delete_customers')" variant="ghost" size="icon" @click="deleteItem(customer.id)" class="text-destructive">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </template>
                                        <template v-else>
                                            <Button :disabled="!can('restore_customers')" variant="ghost" size="icon" title="Restore" @click="restoreItem(customer.id)">
                                                <RotateCcw class="w-4 h-4" />
                                            </Button>
                                            <Button :disabled="!can('force_delete_customers')" variant="ghost" size="icon" title="Force Delete" @click="forceDeleteItem(customer.id)" class="text-destructive">
                                                <Trash class="w-4 h-4" />
                                            </Button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="customers.data.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                    {{ $t('No customers found.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <ResourcePagination
                    :links="customers.links"
                    :total="customers.total"
                    :count="customers.data.length"
                    resource-name="customers"
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
