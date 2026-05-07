<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save, ArrowLeft } from 'lucide-vue-next';
import RoleForm from '@/components/forms/RoleForm.vue';
import { Button } from '@/components/ui/button';
import * as rolesRoutes from '@/routes/admin/roles/index';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Roles & Permissions', href: rolesRoutes.index.url() },
            { title: 'Create Role', href: rolesRoutes.create.url() },
        ],
    },
});

defineProps<{
    available_permissions?: Record<string, string[]>;
}>();

const form = useForm({
    name: '',
    permissions: [] as string[],
});

const submit = () => {
    form.post(rolesRoutes.store.url());
};
</script>

<template>
    <Head :title="$t('Create Role')" />

    <div
        class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-4 overflow-x-auto p-4"
    >
        <RoleForm
            v-model:form="form"
            :available_permissions="available_permissions"
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

            <template #submit-icon>
                <Save class="h-4 w-4" />
            </template>
        </RoleForm>
    </div>
</template>
