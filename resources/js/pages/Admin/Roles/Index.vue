<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShieldCheck, Pencil, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import ResourceIndexLayout from '@/components/Admin/ResourceIndexLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { useBulkActions } from '@/composables/useBulkActions';
import { useResourceFilters } from '@/composables/useResourceFilters';
import { formatRoleName } from '@/composables/useRoles';
import * as rolesRoutes from '@/routes/admin/roles/index';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Roles & Permissions', href: rolesRoutes.index.url() },
        ],
    },
});

// Types are automatically generated via spatie/laravel-typescript-transformer
type Role = Modules.Identity.Data.RoleData & { id: string };

const props = withDefaults(
    defineProps<{
        roles: {
            data: Role[];
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
        available_roles?: string[];
    }>(),
    {
        available_roles: () => [],
    },
);

const { searchQuery, applyFilters, clearFilters } = useResourceFilters(
    () => props.filters?.filter,
    { baseUrl: rolesRoutes.index.url() },
);

// formatRoleName is a standalone utility, no form needed

const selectableRoles = computed(() => {
    return props.roles?.data?.filter((r) => !r.is_protected) ?? [];
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
    confirmState,
} = useBulkActions(() => selectableRoles.value, {
    entityName: 'roles',
    bulkActionRoute: rolesRoutes.bulkAction,
    destroyRoute: (id: string | number) => rolesRoutes.destroy(String(id)),
});
</script>

<template>
    <ResourceIndexLayout
        title="Roles & Permissions"
        description="Manage system roles and their associated permissions."
        :icon="ShieldCheck"
        :selected-count="selectedIds?.length ?? 0"
        :can-create="can('create_roles')"
        :create-url="rolesRoutes.create.url()"
        create-label="Create Role"
        :can-delete="can('delete_roles')"
        v-model:search-query="searchQuery"
        search-placeholder="Search roles..."
        :can-show-trashed="false"
        :pagination-links="roles?.links"
        :pagination-total="roles?.total"
        :pagination-count="roles?.data?.length"
        resource-name="roles"
        v-model:confirm-state="confirmState"
        @search="applyFilters"
        @clear-filters="clearFilters"
        @bulk-delete="bulkAction('delete')"
    >
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
                        {{ $t('Role Name') }}
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Permissions Count') }}
                    </th>
                    <th class="px-6 py-3 text-start font-medium">
                        {{ $t('Actions') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="role in roles?.data ?? []"
                    :key="role.id"
                    class="table-row-themed"
                >
                    <td class="px-6 py-4">
                        <Checkbox
                            v-if="selectableRoles.some((r) => r.id === role.id)"
                            :model-value="selectedIds.includes(role.id)"
                            @update:model-value="toggleItem(role.id)"
                        />
                    </td>
                    <td class="px-6 py-4 font-medium">
                        <Badge
                            :variant="role.is_protected ? 'destructive' : 'default'"
                        >
                            {{ formatRoleName(role.name) }}
                        </Badge>
                    </td>
                    <td class="px-6 py-4 text-muted-foreground">
                        {{ role.permissions ? role.permissions.length : 0 }}
                        {{ $t('Permissions') }}
                    </td>
                    <td class="flex items-center gap-2 px-6 py-4 text-start">
                        <Button
                            v-if="can('update_roles')"
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            as-child
                        >
                            <Link :href="rolesRoutes.edit.url(role.id)">
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button
                            v-if="can('delete_roles') && !role.is_protected"
                            variant="destructive"
                            size="icon"
                            class="h-8 w-8"
                            @click="deleteItem(role.id)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </td>
                </tr>
                <tr v-if="(roles?.data?.length ?? 0) === 0">
                    <td
                        colspan="4"
                        class="px-6 py-8 text-center text-muted-foreground"
                    >
                        {{ $t('No roles found.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </ResourceIndexLayout>
</template>
