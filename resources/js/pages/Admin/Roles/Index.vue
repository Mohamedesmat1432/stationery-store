<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ShieldCheck, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, computed } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { useBulkActions } from '@/composables/useBulkActions';
import SearchInput from '@/components/SearchInput.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

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

const searchQuery = ref(props.filters.filter?.search || '');

const handleSearch = () => {
    router.get('/admin/roles', {
        filter: {
            search: searchQuery.value || undefined,
        }
    }, {
        preserveState: true,
        replace: true,
    });
};

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
    <Head title="Roles Management" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="text-xl font-bold flex items-center gap-2">
                        <ShieldCheck class="w-6 h-6" /> Roles & Permissions
                    </CardTitle>
                    <CardDescription>Manage system roles and their associated permissions.</CardDescription>
                </div>
                <div class="flex items-center gap-2">
                    <Button 
                        v-if="selectedIds.length > 0 && can('delete_roles')"
                        variant="destructive" 
                        size="sm" 
                        class="flex items-center gap-2"
                        @click="bulkAction('delete')"
                    >
                        <Trash2 class="w-4 h-4" /> Delete Selected ({{ selectedIds.length }})
                    </Button>
                    <Button v-if="can('create_roles')" as-child class="flex items-center gap-2">
                        <Link href="/admin/roles/create">
                            <Plus class="w-4 h-4" /> Create Role
                        </Link>
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <SearchInput
                        v-model="searchQuery"
                        placeholder="Search roles..."
                        @search="handleSearch"
                    />
                </div>

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
                                <th class="px-6 py-3 font-medium">Role Name</th>
                                <th class="px-6 py-3 font-medium">Permissions Count</th>
                                <th class="px-6 py-3 font-medium text-right">Actions</th>
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
                                    {{ role.permissions ? role.permissions.length : 0 }} Permissions
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <Button v-if="can('update_roles')" variant="outline" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="`/admin/roles/${role.id}/edit`">
                                            <Pencil class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                    <Button v-if="can('delete_roles')" variant="destructive" size="icon" class="h-8 w-8" @click="deleteItem(role.id)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="roles.data.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-muted-foreground">
                                    No roles found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Simple Pagination -->
                <div class="mt-4 flex items-center justify-between" v-if="roles.total > 0">
                    <span class="text-sm text-muted-foreground">
                        Showing {{ roles.data.length }} of {{ roles.total }} results
                    </span>
                    <div class="flex gap-2">
                        <Button 
                            v-for="(link, i) in roles.links" 
                            :key="i"
                            :variant="link.active ? 'default' : 'outline'"
                            :disabled="!link.url"
                            size="sm"
                            as-child
                        >
                            <Link v-if="link.url" :href="link.url" v-html="link.label"></Link>
                            <span v-else v-html="link.label"></span>
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
