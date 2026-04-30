<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ShieldCheck, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
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
            { title: 'Roles & Permissions', href: '/admin/roles' },
        ],
    },
});

const props = defineProps<{
    roles: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        filter?: {
            search?: string;
        };
    };
}>();

const { searchQuery, applyFilters } = useResourceFilters(props.filters.filter, {
    baseUrl: '/admin/roles',
});

const formatRoleName = (name: string) => {
    return name.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const {
    selectedIds, isAllSelected, isIndeterminate, toggleAll, toggleItem,
    can, bulkAction, deleteItem, restoreItem, forceDeleteItem,
    confirmState
} = useBulkActions(() => props.roles.data, {
    entityName: 'roles',
    bulkActionUrl: '/admin/roles/bulk-action',
    resourceUrl: '/admin/roles',
});
</script>

<template>
    <Head :title="$t('Roles Management')" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <Card>
            <AdminPageHeader
                title="Roles & Permissions"
                description="Manage system roles and their associated permissions."
                :icon="ShieldCheck"
                :selected-count="selectedIds.length"
                :can-create="can('create_roles')"
                create-url="/admin/roles/create"
                create-label="Create Role"
                :can-delete="can('delete_roles')"
                @bulk-delete="bulkAction('delete')"
            />

            <CardContent>
                <ResourceFilterBar
                    v-model:search="searchQuery"
                    search-placeholder="Search roles..."
                    @search="applyFilters"
                />

                <div class="rounded-md border border-sidebar-border">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-muted-foreground uppercase bg-sidebar border-b border-sidebar-border">
                            <tr>
                                <th class="px-6 py-3 font-medium w-10">
                                    <Checkbox 
                                        :model-value="isIndeterminate ? 'indeterminate' : isAllSelected"
                                        @update:model-value="toggleAll"
                                    />
                                </th>
                                <th class="px-6 py-3 font-medium">{{ $t('Role Name') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('Permissions Count') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ $t('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="role in roles.data" :key="role.id" class="border-b border-sidebar-border last:border-0 hover:bg-sidebar-accent/50 transition-colors">
                                <td class="px-6 py-4">
                                    <Checkbox 
                                        :model-value="selectedIds.includes(role.id)"
                                        @update:model-value="toggleItem(role.id)"
                                    />
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    <Badge :variant="role.name === 'admin' ? 'destructive' : 'default'">
                                        {{ formatRoleName(role.name) }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-muted-foreground">
                                    {{ role.permissions ? role.permissions.length : 0 }} {{ $t('Permissions') }}
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <Button :disabled="!can('update_roles')" variant="outline" size="icon" class="h-8 w-8" as-child>
                                        <Link v-if="can('update_roles')" :href="`/admin/roles/${role.id}/edit`">
                                            <Pencil class="w-4 h-4" />
                                        </Link>
                                        <span v-else class="flex items-center justify-center opacity-50">
                                            <Pencil class="w-4 h-4" />
                                        </span>
                                    </Button>
                                    <Button :disabled="!can('delete_roles')" variant="destructive" size="icon" class="h-8 w-8" @click="deleteItem(role.id)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="roles.data.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-muted-foreground">
                                    {{ $t('No roles found.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <ResourcePagination
                    :links="roles.links"
                    :total="roles.total"
                    :count="roles.data.length"
                    resource-name="roles"
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
