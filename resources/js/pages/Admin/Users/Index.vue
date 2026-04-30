<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Users, Plus, Pencil, Trash2, Filter, RotateCcw, Trash } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, watch, computed } from 'vue';
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

const searchQuery = ref(props.filters.filter?.search || '');
const roleFilter = ref(props.filters.filter?.role || 'all');
const showTrashed = ref(props.filters.filter?.trash === 'only');

// Sync state when props change (e.g. navigation)
watch(() => props.filters.filter, (filter) => {
    searchQuery.value = filter?.search || '';
    roleFilter.value = filter?.role || 'all';
    showTrashed.value = filter?.trash === 'only';
}, { deep: true });

const applyFilters = () => {
    router.get('/admin/users', {
        filter: {
            search: searchQuery.value || undefined,
            role: roleFilter.value === 'all' ? undefined : roleFilter.value,
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

const handleRoleChange = (val: any) => {
    roleFilter.value = val;
    applyFilters();
};

const handleTrashToggle = (checked: boolean | 'indeterminate') => {
    showTrashed.value = checked === true;
    applyFilters();
};

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
    <Head title="Users Management" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="text-xl font-bold flex items-center gap-2">
                        <Users class="w-6 h-6" /> Users Management
                    </CardTitle>
                    <CardDescription>Manage user accounts and their assigned roles.</CardDescription>
                </div>
                <div class="flex items-center gap-2">
                    <template v-if="selectedIds.length > 0">
                        <template v-if="!showTrashed">
                            <Button 
                                v-if="can('delete_users')"
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
                                v-if="can('restore_users')"
                                variant="outline" 
                                size="sm" 
                                class="flex items-center gap-2"
                                @click="bulkAction('restore')"
                            >
                                <RotateCcw class="w-4 h-4" /> Restore Selected ({{ selectedIds.length }})
                            </Button>
                            <Button 
                                v-if="can('force_delete_users')"
                                variant="destructive" 
                                size="sm" 
                                class="flex items-center gap-2"
                                @click="bulkAction('forceDelete')"
                            >
                                <Trash class="w-4 h-4" /> Force Delete Selected ({{ selectedIds.length }})
                            </Button>
                        </template>
                    </template>
                    <Button v-if="can('create_users')" as-child class="flex items-center gap-2">
                        <Link href="/admin/users/create">
                            <Plus class="w-4 h-4" /> Create User
                        </Link>
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <SearchInput
                        v-model="searchQuery"
                        placeholder="Search by name or email..."
                        @search="handleSearch"
                    />
                    
                    <div class="flex items-center gap-2 min-w-[200px]">
                        <Filter class="w-4 h-4 text-muted-foreground" />
                        <Select :model-value="roleFilter" @update:model-value="handleRoleChange">
                            <SelectTrigger class="h-9">
                                <SelectValue placeholder="Filter by Role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Roles</SelectItem>
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
                                <th class="px-6 py-3 font-medium w-10">
                                    <Checkbox 
                                        :model-value="isIndeterminate ? 'indeterminate' : isAllSelected"
                                        @update:model-value="toggleAll"
                                    />
                                </th>
                                <th class="px-6 py-3 font-medium">Name</th>
                                <th class="px-6 py-3 font-medium">Email</th>
                                <th class="px-6 py-3 font-medium">Roles</th>
                                <th class="px-6 py-3 font-medium text-right">Actions</th>
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
                                        <span v-if="!user.roles || user.roles.length === 0" class="text-xs text-muted-foreground italic">No Roles</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <template v-if="!user.deleted_at">
                                        <Button v-if="can('update_users')" variant="outline" size="icon" class="h-8 w-8" as-child>
                                            <Link :href="`/admin/users/${user.id}/edit`">
                                                <Pencil class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button v-if="can('delete_users') && user.id !== $page.props.auth.user.id" variant="destructive" size="icon" class="h-8 w-8" @click="deleteItem(user.id)">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </template>
                                    <template v-else>
                                        <Button v-if="can('restore_users')" variant="outline" size="icon" class="h-8 w-8" title="Restore" @click="restoreItem(user.id)">
                                            <RotateCcw class="w-4 h-4" />
                                        </Button>
                                        <Button v-if="can('force_delete_users') && user.id !== $page.props.auth.user.id" variant="destructive" size="icon" class="h-8 w-8" title="Force Delete" @click="forceDeleteItem(user.id)">
                                            <Trash class="w-4 h-4" />
                                        </Button>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Simple Pagination -->
                <div class="mt-4 flex items-center justify-between" v-if="users.total > 0">
                    <span class="text-sm text-muted-foreground">
                        Showing {{ users.data.length }} of {{ users.total }} results
                    </span>
                    <div class="flex gap-2">
                        <Button 
                            v-for="(link, i) in users.links" 
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
