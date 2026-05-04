<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ShieldCheck, Save, ArrowLeft, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
    CardFooter,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useBulkActions } from '@/composables/useBulkActions';
import { usePermissions } from '@/composables/usePermissions';
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

const {
    groupedPermissions,
    formatName,
    formatPermissionLabel,
    togglePermission,
    toggleModule,
} = usePermissions(form, () => props.available_permissions ?? {});

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
        <form @submit.prevent="submit">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle
                            class="flex items-center gap-2 text-xl font-bold"
                        >
                            <ShieldCheck class="h-6 w-6" />
                            {{ $t('Edit Role: {name}', { name: role.name }) }}
                        </CardTitle>
                        <CardDescription>{{
                            $t(
                                'Modify role details and update its access matrix.',
                            )
                        }}</CardDescription>
                    </div>
                    <Button variant="outline" as-child type="button">
                        <Link
                            :href="rolesRoutes.index.url()"
                            class="flex items-center gap-2"
                        >
                            <ArrowLeft class="h-4 w-4" /> {{ $t('Back') }}
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-8">
                    <!-- Role Name -->
                    <div class="max-w-md space-y-2">
                        <Label for="name"
                            >{{ $t('Role Name') }}
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="name"
                            v-model="form.name"
                            :placeholder="$t('e.g. Content Manager')"
                            :disabled="form.processing || !can('update_roles')"
                        />
                        <p
                            v-if="!can('update_roles')"
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            {{
                                $t(
                                    'You do not have permission to rename roles.',
                                )
                            }}
                        </p>
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Permissions Matrix -->
                    <div class="space-y-4">
                        <Label class="text-lg font-semibold">{{
                            $t('Permissions Matrix')
                        }}</Label>
                        <InputError :message="form.errors.permissions" />

                        <div
                            class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                v-for="(
                                    permissions, moduleName
                                ) in groupedPermissions"
                                :key="moduleName"
                                class="space-y-4 rounded-lg border border-sidebar-border bg-sidebar p-4"
                            >
                                <div
                                    class="flex items-center justify-between border-b border-sidebar-border pb-2"
                                >
                                    <Label
                                        class="text-base font-semibold capitalize"
                                        >{{ formatName(moduleName as string) }}</Label
                                    >
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-6 px-2 text-xs"
                                        @click="toggleModule(permissions)"
                                        :disabled="form.processing"
                                    >
                                        {{ $t('Toggle All') }}
                                    </Button>
                                </div>
                                <div class="space-y-3">
                                    <div
                                        v-for="permission in permissions"
                                        :key="permission"
                                        class="flex items-center space-x-2"
                                    >
                                        <Checkbox
                                            :id="permission"
                                            :model-value="
                                                form.permissions.includes(
                                                    permission,
                                                )
                                            "
                                            @update:model-value="
                                                togglePermission(permission)
                                            "
                                            :disabled="form.processing"
                                        />
                                        <label
                                            :for="permission"
                                            class="cursor-pointer text-sm leading-none font-medium text-muted-foreground transition-colors peer-disabled:cursor-not-allowed peer-disabled:opacity-70 hover:text-foreground"
                                        >
                                            {{
                                                formatPermissionLabel(
                                                    permission,
                                                )
                                            }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <CardFooter
                    class="flex items-center justify-between border-t border-sidebar-border pt-6"
                >
                    <div>
                        <Button
                            v-if="can('delete_roles')"
                            type="button"
                            variant="destructive"
                            @click="confirmDelete"
                            class="flex items-center gap-2"
                        >
                            <Trash2 class="h-4 w-4" /> {{ $t('Delete Role') }}
                        </Button>
                    </div>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2"
                    >
                        <Save class="h-4 w-4" /> {{ $t('Update Role') }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
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
