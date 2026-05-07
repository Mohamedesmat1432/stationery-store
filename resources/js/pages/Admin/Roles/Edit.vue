<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Save, ArrowLeft, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import RoleForm from '@/components/forms/RoleForm.vue';
import { Button } from '@/components/ui/button';
import { useBulkActions } from '@/composables/useBulkActions';
import * as rolesRoutes from '@/routes/admin/roles/index';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Roles & Permissions', href: rolesRoutes.index.url() },
            { title: 'Edit Role', href: '#' },
        ],
    },
});

const props = withDefaults(
    defineProps<{
        role: {
            id: string;
            name: string;
            permissions: string[];
        };
        available_permissions?: Record<string, string[]>;
    }>(),
    {
        available_permissions: () => ({}),
    },
);

const form = useForm({
    id: props.role.id,
    name: props.role.name,
    permissions: props.role.permissions || ([] as string[]),
});

const submit = () => {
    form.put(rolesRoutes.update.url(props.role.id));
};

const { can } = useBulkActions(() => [], {
    entityName: 'roles',
    bulkActionRoute: rolesRoutes.bulkAction,
    destroyRoute: (id: string | number) => rolesRoutes.destroy(String(id)),
});

// Delete confirmation via ConfirmDialog
const showDeleteConfirm = ref(false);
const deleteLoading = ref(false);

const confirmDelete = () => {
    showDeleteConfirm.value = true;
};

const handleDeleteConfirm = () => {
    deleteLoading.value = true;
    router.delete(rolesRoutes.destroy.url(props.role.id), {
        onFinish: () => {
            deleteLoading.value = false;
            showDeleteConfirm.value = false;
        },
    });
};
</script>

<template>
    <Head :title="$t('Edit Role')" />

    <div
        class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-4 overflow-x-auto p-4"
    >
        <RoleForm
            v-model:form="form"
            :available_permissions="available_permissions"
            is-edit
            :role-name="role.name"
            :can-update="can('update_roles')"
            @submit="submit"
        >
            <template #header-actions>
                <Button variant="outline" as-child type="button">
                    <Link
                        :href="rolesRoutes.index.url()"
                        class="flex items-center gap-2"
                    >
                        <ArrowLeft class="h-4 w-4" /> {{ $t('Back') }}
                    </Link>
                </Button>
            </template>

            <template #footer-left>
                <Button
                    v-if="can('delete_roles')"
                    type="button"
                    variant="destructive"
                    @click="confirmDelete"
                    class="flex items-center gap-2"
                >
                    <Trash2 class="h-4 w-4" /> {{ $t('Delete Role') }}
                </Button>
            </template>

            <template #submit-icon>
                <Save class="h-4 w-4" />
            </template>
        </RoleForm>
    </div>

    <ConfirmDialog
        v-model:open="showDeleteConfirm"
        title="Delete Role"
        description="Are you sure you want to delete this role? This action cannot be undone. All users assigned to this role will lose its permissions."
        variant="destructive"
        confirm-label="Delete Role"
        :loading="deleteLoading"
        @confirm="handleDeleteConfirm"
    />
</template>
