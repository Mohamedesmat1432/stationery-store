<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { UserCircle, Plus, Pencil, Trash2, Filter, Mail, Phone, Building2, RotateCcw, Trash } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, watch } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { useBulkActions } from '@/composables/useBulkActions';
import SearchInput from '@/components/SearchInput.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
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

const searchQuery = ref(props.filters.filter?.search || '');
const groupFilter = ref(props.filters.filter?.group || 'all');
const showTrashed = ref(props.filters.filter?.trash === 'only');

// Sync state when props change (e.g. navigation)
watch(() => props.filters.filter, (filter) => {
    searchQuery.value = filter?.search || '';
    groupFilter.value = filter?.group || 'all';
    showTrashed.value = filter?.trash === 'only';
}, { deep: true });

const applyFilters = () => {
    router.get('/admin/customers', {
        filter: {
            search: searchQuery.value || undefined,
            group: groupFilter.value === 'all' ? undefined : groupFilter.value,
            trash: showTrashed.value ? 'only' : undefined
        },
    }, {
        preserveState: true,
        replace: true,
    });
};

const handleSearch = (val: string) => {
    searchQuery.value = val;
    applyFilters();
};

const handleGroupChange = (val: any) => {
    groupFilter.value = val;
    applyFilters();
};

const handleTrashToggle = (checked: boolean | 'indeterminate') => {
    showTrashed.value = checked === true;
    applyFilters();
};

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
    <Head title="Customers" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="text-xl font-bold flex items-center gap-2">
                        <UserCircle class="w-6 h-6" /> Customers
                    </CardTitle>
                    <CardDescription>View and manage your customer base.</CardDescription>
                </div>
                <div class="flex items-center gap-2">
                    <template v-if="selectedIds.length > 0">
                        <template v-if="!showTrashed">
                            <Button 
                                v-if="can('delete_customers')"
                                variant="destructive" 
                                size="sm" 
                                class="flex items-center gap-2"
                                @click="bulkAction('delete')"
                            >
                                <Trash2 class="w-4 h-4" /> Delete Selected ({{ selectedIds.length }})
                            </Button>
                        </template>
                        <template v-else>
                            <Button 
                                v-if="can('restore_customers')"
                                variant="outline" 
                                size="sm" 
                                class="flex items-center gap-2"
                                @click="bulkAction('restore')"
                            >
                                <RotateCcw class="w-4 h-4" /> Restore Selected ({{ selectedIds.length }})
                            </Button>
                            <Button 
                                v-if="can('force_delete_customers')"
                                variant="destructive" 
                                size="sm" 
                                class="flex items-center gap-2"
                                @click="bulkAction('forceDelete')"
                            >
                                <Trash class="w-4 h-4" /> Force Delete Selected ({{ selectedIds.length }})
                            </Button>
                        </template>
                    </template>
                    <Button v-if="can('create_customers')" as-child class="flex items-center gap-2">
                        <Link href="/admin/customers/create">
                            <Plus class="w-4 h-4" /> Create Customer
                        </Link>
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <SearchInput
                        v-model="searchQuery"
                        placeholder="Search by name, email, phone..."
                        @search="handleSearch"
                    />
                    
                    <div class="flex items-center gap-2 min-w-[200px]">
                        <Filter class="w-4 h-4 text-muted-foreground" />
                        <Select :model-value="groupFilter" @update:model-value="handleGroupChange">
                            <SelectTrigger class="h-9">
                                <SelectValue placeholder="Filter by Group" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Groups</SelectItem>
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

                    <div class="flex items-center gap-2 px-3 ml-auto">
                        <Checkbox 
                            id="show-trashed" 
                            :model-value="showTrashed" 
                            @update:model-value="handleTrashToggle"
                        />
                        <label for="show-trashed" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Show Trashed
                        </label>
                    </div>
                </div>

                <div class="rounded-md border border-sidebar-border overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-muted-foreground uppercase bg-sidebar border-b border-sidebar-border">
                            <tr>
                                <th class="px-4 py-3 font-medium w-10">
                                    <Checkbox 
                                        :model-value="isIndeterminate ? 'indeterminate' : isAllSelected"
                                        @update:model-value="toggleAll"
                                    />
                                </th>
                                <th class="px-4 py-3 font-medium">Customer</th>
                                <th class="px-4 py-3 font-medium">Contact</th>
                                <th class="px-4 py-3 font-medium">Group</th>
                                <th class="px-4 py-3 font-medium text-right">Orders</th>
                                <th class="px-4 py-3 font-medium text-right">Spent</th>
                                <th class="px-4 py-3 font-medium text-right">Actions</th>
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
                                        <span class="font-medium text-foreground">{{ customer.name || 'No User Linked' }}</span>
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
                                    <span v-else class="text-xs text-muted-foreground italic">No Group</span>
                                </td>
                                <td class="px-4 py-3 text-right">{{ customer.orders_count }}</td>
                                <td class="px-4 py-3 text-right font-medium">{{ formatCurrency(customer.total_spent) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <template v-if="!customer.deleted_at">
                                            <Button v-if="can('update_customers')" variant="ghost" size="icon" as-child>
                                                <Link :href="`/admin/customers/${customer.id}/edit`">
                                                    <Pencil class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <Button v-if="can('delete_customers')" variant="ghost" size="icon" @click="deleteItem(customer.id)" class="text-destructive">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </template>
                                        <template v-else>
                                            <Button v-if="can('restore_customers')" variant="ghost" size="icon" title="Restore" @click="restoreItem(customer.id)">
                                                <RotateCcw class="w-4 h-4" />
                                            </Button>
                                            <Button v-if="can('force_delete_customers')" variant="ghost" size="icon" title="Force Delete" @click="forceDeleteItem(customer.id)" class="text-destructive">
                                                <Trash class="w-4 h-4" />
                                            </Button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="customers.data.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                    No customers found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex items-center justify-between" v-if="customers.total > 0">
                    <div class="text-sm text-muted-foreground">
                        Showing {{ customers.data.length }} of {{ customers.total }} customers
                    </div>
                    <div class="flex gap-2">
                        <Button 
                            variant="outline" 
                            size="sm" 
                            :disabled="customers.current_page === 1"
                            @click="router.visit(customers.links[0].url)"
                        >
                            Previous
                        </Button>
                        <Button 
                            variant="outline" 
                            size="sm" 
                            :disabled="customers.current_page === customers.last_page"
                            @click="router.visit(customers.links[customers.links.length - 1].url)"
                        >
                            Next
                        </Button>
                    </div>
                </div>
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
