<script setup lang="ts">
import { Deferred, Link, router } from '@inertiajs/vue3';
import {
    Users,
    Pencil,
    Trash2,
    RotateCcw,
    Trash,
    Download,
    Upload,
    Power,
} from 'lucide-vue-next';
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
import { formatLabel } from '@/lib/format';
import * as usersRoutes from '@/routes/admin/users/index';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Users Management', href: usersRoutes.index.url() },
        ],
    },
});

// Types are automatically generated via spatie/laravel-typescript-transformer
type User = Modules.Identity.Data.UserData & { id: string };

const props = withDefaults(
    defineProps<{
        users?: {
            data: User[];
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
        available_roles?: string[];
    }>(),
    {
        available_roles: () => [],
    },
);

const { searchQuery, showTrashed, extraFilters, applyFilters, clearFilters } =
    useResourceFilters(() => props.filters?.filter, {
        baseUrl: usersRoutes.index.url(),
    });

const roleFilter = computed({
    get: () => extraFilters.value.role || 'all',
    set: (val) => {
        extraFilters.value.role = val === 'all' ? undefined : val;
        applyFilters();
    },
});

const formatRoleName = (name: string) => formatLabel(name);

const selectableUsers = computed(() => {
    return props.users?.data?.filter((u) => !u.is_protected) ?? [];
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
} = useBulkActions(() => selectableUsers.value, {
    entityName: 'users',
    bulkActionRoute: usersRoutes.bulkAction,
    destroyRoute: (id) => usersRoutes.destroy(String(id)),
    restoreRoute: (id) => usersRoutes.restore(String(id)),
    forceDeleteRoute: (id) => usersRoutes.forceDelete(String(id)),
});

// Export/Import state
const isExportModalOpen = ref(false);
const isImportModalOpen = ref(false);

const allColumns = [
    { id: 'name', label: 'Name' },
    { id: 'email', label: 'Email' },
    { id: 'roles', label: 'Roles' },
    { id: 'created_at', label: 'Created At' },
];
</script>

<template>
    <ResourceIndexLayout
        title="Users Management"
        description="Manage user accounts and their assigned roles."
        :icon="Users"
        :selected-count="selectedIds?.length ?? 0"
        :can-create="can('create_users')"
        :create-url="usersRoutes.create.url()"
        create-label="Create User"
        :can-delete="can('delete_users')"
        :can-restore="can('restore_users')"
        :can-force-delete="can('force_delete_users')"
        :can-activate="can('update_users')"
        :can-deactivate="can('update_users')"
        v-model:search-query="searchQuery"
        v-model:show-trashed="showTrashed"
        search-placeholder="Search by name or email..."
        :pagination-links="users?.links"
        :pagination-total="users?.total"
        :pagination-count="users?.data?.length"
        resource-name="users"
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
                v-if="can('export_users')"
                variant="outline"
                class="flex items-center gap-2"
                @click="isExportModalOpen = true"
            >
                <Download class="h-4 w-4" /> {{ $t('Export') }}
            </Button>
            <Button
                v-if="can('import_users')"
                variant="outline"
                class="flex items-center gap-2"
                @click="isImportModalOpen = true"
            >
                <Upload class="h-4 w-4" /> {{ $t('Import') }}
            </Button>
        </template>

        <template #extra-filters>
            <div class="flex min-w-50 items-center gap-2">
                <Deferred data="available_roles">
                    <template #fallback>
                        <Skeleton class="h-9 w-full" />
                    </template>
                    <Select v-model="roleFilter">
                        <SelectTrigger class="h-9" :aria-label="$t('Filter by Role')">
                            <SelectValue :placeholder="$t('Filter by Role')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('All Roles') }}</SelectItem>
                            <SelectItem
                                v-for="role in available_roles ?? []"
                                :key="role"
                                :value="role"
                            >
                                {{ formatRoleName(role) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </Deferred>
            </div>
        </template>

        <Deferred data="users">
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
                            :model-value="
                                isIndeterminate ? 'indeterminate' : isAllSelected
                            "
                            @update:model-value="toggleAll"
                        />
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Name') }}
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Email') }}
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Roles') }}
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Status') }}
                    </th>
                    <th class="px-6 py-3 text-end font-medium">
                        {{ $t('Actions') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="user in users?.data ?? []"
                    :key="user.id"
                    class="table-row-themed"
                >
                    <td class="px-6 py-4">
                        <Checkbox
                            v-if="selectableUsers.some((u) => u.id === user.id)"
                            :model-value="selectedIds.includes(user.id)"
                            @update:model-value="toggleItem(user.id)"
                        />
                    </td>
                    <td class="px-6 py-4 font-medium">
                        {{ user.name }}
                    </td>
                    <td class="px-6 py-4 text-muted-foreground">
                        {{ user.email }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            <Badge
                                v-for="role in user.roles"
                                :key="role"
                                :variant="role === 'admin' ? 'destructive' : 'secondary'"
                            >
                                {{ formatRoleName(role) }}
                            </Badge>
                            <span
                                v-if="!user.roles || user.roles.length === 0"
                                class="text-xs text-muted-foreground italic"
                                >{{ $t('No Roles') }}</span
                            >
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <template v-if="!user.deleted_at">
                            <Badge v-if="user.is_active" variant="outline" class="bg-green-500/5 text-green-600 border-green-500/20 text-[10px] px-2 py-0">
                                {{ $t('Active') }}
                            </Badge>
                            <Badge v-else variant="outline" class="bg-destructive/5 text-destructive border-destructive/20 text-[10px] px-2 py-0">
                                {{ $t('Inactive') }}
                            </Badge>
                        </template>
                        <span v-else class="text-xs text-muted-foreground italic">-</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <template v-if="!user.deleted_at">
                                <Button
                                    v-if="can('update_users')"
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8"
                                    @click="router.patch(usersRoutes.toggleActive(user.id).url)"
                                    :title="user.is_active ? 'Deactivate' : 'Activate'"
                                >
                                    <Power :class="['h-4 w-4', user.is_active ? 'text-green-500' : 'text-muted-foreground']" />
                                </Button>
                                <Button
                                    v-if="can('update_users')"
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8"
                                    as-child
                                >
                                    <Link :href="usersRoutes.edit.url(user.id)">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    v-if="
                                        can('delete_users') &&
                                        selectableUsers.some((u) => u.id === user.id)
                                    "
                                    variant="destructive"
                                    size="icon"
                                    class="h-8 w-8"
                                    @click="deleteItem(user.id)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </template>
                            <template v-else>
                                <Button
                                    v-if="can('restore_users')"
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8"
                                    title="Restore"
                                    @click="restoreItem(user.id)"
                                >
                                    <RotateCcw class="h-4 w-4" />
                                </Button>
                                <Button
                                    v-if="
                                        can('force_delete_users') &&
                                        selectableUsers.some((u) => u.id === user.id)
                                    "
                                    variant="destructive"
                                    size="icon"
                                    class="h-8 w-8"
                                    title="Force Delete"
                                    @click="forceDeleteItem(user.id)"
                                >
                                    <Trash class="h-4 w-4" />
                                </Button>
                            </template>
                        </div>
                    </td>
                </tr>
                <tr v-if="(users?.data?.length ?? 0) === 0">
                    <td
                        colspan="6"
                        class="px-6 py-8 text-center text-muted-foreground"
                    >
                        {{ $t('No users found.') }}
                    </td>
                </tr>
            </tbody>
        </table>
        </Deferred>
    </ResourceIndexLayout>

    <ResourceExportModal
        v-model:open="isExportModalOpen"
        title="Export Users"
        description="Choose the columns you want to include in your user export."
        :columns="allColumns"
        :export-url="usersRoutes.exportMethod.url()"
    />

    <ResourceImportModal
        v-model:open="isImportModalOpen"
        title="Import Users"
        description="Select an Excel or CSV file to import users. The file should match the exported format."
        :import-url="usersRoutes.importMethod.url()"
    />
</template>
