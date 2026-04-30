<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
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
            { title: 'Users Management', href: '/admin/users' },
        ],
    },
});

const props = defineProps<{
    users: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        filter?: {
            search?: string;
            role?: string;
            trash?: string;
        };
    };
    available_roles: string[];
}>();

const { searchQuery, showTrashed, extraFilters, applyFilters } = useResourceFilters(props.filters.filter, {
    baseUrl: '/admin/users',
});

const roleFilter = computed({
    get: () => extraFilters.value.role || 'all',
    set: (val) => {
        extraFilters.value.role = val === 'all' ? undefined : val;
        applyFilters();
    }
});

const formatRoleName = (name: string) => {
    return name.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const page = usePage();
const selectableUsers = computed(() => {
    return props.users.data.filter((u: any) => u.id !== (page.props.auth as any).user.id);
});

const {
    selectedIds, isAllSelected, isIndeterminate, toggleAll, toggleItem,
    can, bulkAction, deleteItem, restoreItem, forceDeleteItem,
    confirmState
} = useBulkActions(() => selectableUsers.value, {
    entityName: 'users',
    bulkActionUrl: '/admin/users/bulk-action',
    resourceUrl: '/admin/users',
});
</script>

<template>
    <Head :title="$t('Users Management')" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <Card>
            <AdminPageHeader
                title="Users Management"
                description="Manage user accounts and their assigned roles."
                :icon="Users"
                :selected-count="selectedIds.length"
                :show-trashed="showTrashed"
                :can-create="can('create_users')"
                create-url="/admin/users/create"
                create-label="Create User"
                :can-delete="can('delete_users')"
                :can-restore="can('restore_users')"
                :can-force-delete="can('force_delete_users')"
                @bulk-delete="bulkAction('delete')"
                @bulk-restore="bulkAction('restore')"
                @bulk-force-delete="bulkAction('forceDelete')"
            />
            
            <CardContent>
                <ResourceFilterBar
                    v-model:search="searchQuery"
                    v-model:trashed="showTrashed"
                    search-placeholder="Search by name or email..."
                    @search="applyFilters"
                    @update:trashed="applyFilters"
                >
                    <template #filters>
                        <div class="flex items-center gap-2 min-w-[200px]">
                            <Select v-model="roleFilter">
                                <SelectTrigger class="h-9">
                                    <SelectValue :placeholder="$t('Filter by Role')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{{ $t('All Roles') }}</SelectItem>
                                    <SelectItem 
                                        v-for="role in available_roles" 
                                        :key="role" 
                                        :value="role"
                                    >
                                        {{ formatRoleName(role) }}
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
                                <th class="px-6 py-3 font-medium w-10">
                                    <Checkbox 
                                        :model-value="isIndeterminate ? 'indeterminate' : isAllSelected"
                                        @update:model-value="toggleAll"
                                    />
                                </th>
                                <th class="px-6 py-3 font-medium">{{ $t('Name') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('Email') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('Roles') }}</th>
                                <th class="px-6 py-3 font-medium text-end">{{ $t('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id" class="border-b border-sidebar-border last:border-0 hover:bg-sidebar-accent/50 transition-colors">
                                <td class="px-6 py-4">
                                    <Checkbox 
                                        v-if="user.id !== $page.props.auth.user.id"
                                        :model-value="selectedIds.includes(user.id)"
                                        @update:model-value="toggleItem(user.id)"
                                    />
                                </td>
                                <td class="px-6 py-4 font-medium">{{ user.name }}</td>
                                <td class="px-6 py-4 text-muted-foreground">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-1 flex-wrap">
                                        <Badge 
                                            v-for="role in user.roles" 
                                            :key="role"
                                            :variant="role === 'admin' ? 'destructive' : 'secondary'"
                                        >
                                            {{ formatRoleName(role) }}
                                        </Badge>
                                        <span v-if="!user.roles || user.roles.length === 0" class="text-xs text-muted-foreground italic">{{ $t('No Roles') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-end space-x-2">
                                    <template v-if="!user.deleted_at">
                                        <Button :disabled="!can('update_users')" variant="outline" size="icon" class="h-8 w-8" as-child>
                                            <Link v-if="can('update_users')" :href="`/admin/users/${user.id}/edit`">
                                                <Pencil class="w-4 h-4" />
                                            </Link>
                                            <span v-else class="flex items-center justify-center opacity-50">
                                                <Pencil class="w-4 h-4" />
                                            </span>
                                        </Button>
                                        <Button :disabled="!can('delete_users') || user.id === $page.props.auth.user.id" variant="destructive" size="icon" class="h-8 w-8" @click="deleteItem(user.id)">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </template>
                                    <template v-else>
                                        <Button :disabled="!can('restore_users')" variant="outline" size="icon" class="h-8 w-8" title="Restore" @click="restoreItem(user.id)">
                                            <RotateCcw class="w-4 h-4" />
                                        </Button>
                                        <Button :disabled="!can('force_delete_users') || user.id === $page.props.auth.user.id" variant="destructive" size="icon" class="h-8 w-8" title="Force Delete" @click="forceDeleteItem(user.id)">
                                            <Trash class="w-4 h-4" />
                                        </Button>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">
                                    {{ $t('No users found.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <ResourcePagination
                    :links="users.links"
                    :total="users.total"
                    :count="users.data.length"
                    resource-name="users"
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
