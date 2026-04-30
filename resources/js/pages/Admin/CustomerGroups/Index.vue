<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Users, Plus, Pencil, Trash2, RotateCcw, Trash } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, watch } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { useBulkActions } from '@/composables/useBulkActions';
import SearchInput from '@/components/SearchInput.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

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

const searchQuery = ref(props.filters.filter?.search || '');
const showTrashed = ref(props.filters.filter?.trash === 'only');

// Sync state when props change (e.g. navigation)
watch(() => props.filters.filter, (filter) => {
    searchQuery.value = filter?.search || '';
    showTrashed.value = filter?.trash === 'only';
}, { deep: true });

const applyFilters = () => {
    router.get('/admin/customer-groups', {
        filter: {
            search: searchQuery.value || undefined,
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

const handleTrashToggle = (checked: boolean | 'indeterminate') => {
    showTrashed.value = checked === true;
    applyFilters();
};

const {
    selectedIds, isAllSelected, isIndeterminate, toggleAll, toggleItem,
    can, bulkAction, deleteItem, restoreItem, forceDeleteItem,
    confirmState
} = useBulkActions(() => props.groups.data, {
    entityName: 'customer groups',
    bulkActionUrl: '/admin/customer-groups/bulk-action',
    resourceUrl: '/admin/customer-groups',
});
</script>

<template>
    <Head title="Customer Groups" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="text-xl font-bold flex items-center gap-2">
                        <Users class="w-6 h-6" /> Customer Groups
                    </CardTitle>
                    <CardDescription>Manage customer segments and group-level discounts.</CardDescription>
                </div>
                <div class="flex items-center gap-2">
                    <template v-if="selectedIds.length > 0">
                        <template v-if="!showTrashed">
                            <Button 
                                v-if="can('delete_customer_groups')"
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
                                v-if="can('restore_customer_groups')"
                                variant="outline" 
                                size="sm" 
                                class="flex items-center gap-2"
                                @click="bulkAction('restore')"
                            >
                                <RotateCcw class="w-4 h-4" /> Restore Selected ({{ selectedIds.length }})
                            </Button>
                            <Button 
                                v-if="can('force_delete_customer_groups')"
                                variant="destructive" 
                                size="sm" 
                                class="flex items-center gap-2"
                                @click="bulkAction('forceDelete')"
                            >
                                <Trash class="w-4 h-4" /> Force Delete Selected ({{ selectedIds.length }})
                            </Button>
                        </template>
                    </template>
                    <Button v-if="can('create_customer_groups')" as-child class="flex items-center gap-2">
                        <Link href="/admin/customer-groups/create">
                            <Plus class="w-4 h-4" /> Create Group
                        </Link>
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <SearchInput
                        v-model="searchQuery"
                        placeholder="Search groups..."
                        @search="handleSearch"
                    />

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

                <div class="rounded-md border border-sidebar-border">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-muted-foreground uppercase bg-sidebar border-b border-sidebar-border">
                            <tr>
                                <th class="px-4 py-3 font-medium w-10">
                                    <Checkbox 
                                        :model-value="isIndeterminate ? 'indeterminate' : isAllSelected"
                                        @update:model-value="toggleAll"
                                    />
                                </th>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Slug</th>
                                <th class="px-4 py-3 font-medium text-center">Discount</th>
                                <th class="px-4 py-3 font-medium text-center">Customers</th>
                                <th class="px-4 py-3 font-medium text-center">Status</th>
                                <th class="px-4 py-3 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-for="group in groups.data" :key="group.id" class="hover:bg-sidebar/50 transition-colors">
                                <td class="px-4 py-3">
                                    <Checkbox 
                                        :model-value="selectedIds.includes(group.id)"
                                        @update:model-value="toggleItem(group.id)"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-foreground">{{ group.name }}</span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ group.slug }}</td>
                                <td class="px-4 py-3 text-center">
                                    <Badge variant="secondary">{{ group.discount_percentage }}%</Badge>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-muted-foreground">{{ group.customers_count }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Badge :variant="group.is_active ? 'default' : 'destructive'">
                                        {{ group.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <template v-if="!group.deleted_at">
                                            <Button v-if="can('update_customer_groups')" variant="ghost" size="icon" as-child>
                                                <Link :href="`/admin/customer-groups/${group.id}/edit`">
                                                    <Pencil class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <Button v-if="can('delete_customer_groups')" variant="ghost" size="icon" @click="deleteItem(group.id)" class="text-destructive">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </template>
                                        <template v-else>
                                            <Button v-if="can('restore_customer_groups')" variant="ghost" size="icon" title="Restore" @click="restoreItem(group.id)">
                                                <RotateCcw class="w-4 h-4" />
                                            </Button>
                                            <Button v-if="can('force_delete_customer_groups')" variant="ghost" size="icon" title="Force Delete" @click="forceDeleteItem(group.id)" class="text-destructive">
                                                <Trash class="w-4 h-4" />
                                            </Button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="groups.data.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                    No customer groups found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex items-center justify-between" v-if="groups.total > 0">
                    <div class="text-sm text-muted-foreground">
                        Showing {{ groups.data.length }} of {{ groups.total }} groups
                    </div>
                    <div class="flex gap-2">
                        <Button 
                            variant="outline" 
                            size="sm" 
                            :disabled="groups.current_page === 1"
                            @click="router.visit(groups.links[0].url)"
                        >
                            Previous
                        </Button>
                        <Button 
                            variant="outline" 
                            size="sm" 
                            :disabled="groups.current_page === groups.last_page"
                            @click="router.visit(groups.links[groups.links.length - 1].url)"
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
