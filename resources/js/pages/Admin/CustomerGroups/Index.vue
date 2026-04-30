<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Users, Pencil, Trash2, RotateCcw, Trash } from 'lucide-vue-next';
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

const { searchQuery, showTrashed, applyFilters } = useResourceFilters(props.filters.filter, {
    baseUrl: '/admin/customer-groups',
});

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
            />

            <CardContent>
                <ResourceFilterBar
                    v-model:search="searchQuery"
                    v-model:trashed="showTrashed"
                    search-placeholder="Search groups..."
                    @search="applyFilters"
                    @update:trashed="applyFilters"
                />

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
                                <th class="px-4 py-3 font-medium">{{ $t('Name') }}</th>
                                <th class="px-4 py-3 font-medium">{{ $t('Slug') }}</th>
                                <th class="px-4 py-3 font-medium text-center">{{ $t('Discount') }}</th>
                                <th class="px-4 py-3 font-medium text-center">{{ $t('Customers') }}</th>
                                <th class="px-4 py-3 font-medium text-center">{{ $t('Status') }}</th>
                                <th class="px-4 py-3 font-medium text-end">{{ $t('Actions') }}</th>
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
                                        {{ group.is_active ? $t('Active') : $t('Inactive') }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="flex justify-end gap-2">
                                        <template v-if="!group.deleted_at">
                                            <Button :disabled="!can('update_customer_groups')" variant="ghost" size="icon" as-child>
                                                <Link v-if="can('update_customer_groups')" :href="`/admin/customer-groups/${group.id}/edit`">
                                                    <Pencil class="w-4 h-4" />
                                                </Link>
                                                <span v-else class="flex items-center justify-center opacity-50">
                                                    <Pencil class="w-4 h-4" />
                                                </span>
                                            </Button>
                                            <Button :disabled="!can('delete_customer_groups')" variant="ghost" size="icon" @click="deleteItem(group.id)" class="text-destructive">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </template>
                                        <template v-else>
                                            <Button :disabled="!can('restore_customer_groups')" variant="ghost" size="icon" title="Restore" @click="restoreItem(group.id)">
                                                <RotateCcw class="w-4 h-4" />
                                            </Button>
                                            <Button :disabled="!can('force_delete_customer_groups')" variant="ghost" size="icon" title="Force Delete" @click="forceDeleteItem(group.id)" class="text-destructive">
                                                <Trash class="w-4 h-4" />
                                            </Button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="groups.data.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
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
</template>
