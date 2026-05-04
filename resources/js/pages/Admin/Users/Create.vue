<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Save, ArrowLeft } from 'lucide-vue-next';
import { index, create } from '@/actions/Modules/Identity/Http/Controllers/UserController';
import UserForm from '@/components/forms/UserForm.vue';
import { Button } from '@/components/ui/button';
import { useUsers } from '@/composables/useUsers';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Users Management', href: index.url() },
            { title: 'Create User', href: create.url() },
        ],
    },
});

withDefaults(
    defineProps<{
        available_roles?: string[];
    }>(),
    {
        available_roles: () => [],
    },
);

const { form, submit } = useUsers();
const handleSubmit = () => submit();
</script>

<template>
    <Head :title="$t('Create User')" />

    <div class="mx-auto flex h-full w-full max-w-4xl flex-1 flex-col gap-4 overflow-x-auto p-4">
        <UserForm
            :form="form"
            :available_roles="available_roles"
            @submit="handleSubmit"
        >
            <template #header-actions>
                <Button variant="outline" as-child type="button">
                    <Link :href="index.url()" class="flex items-center gap-2">
                        <ArrowLeft class="h-4 w-4" /> {{ $t('Back') }}
                    </Link>
                </Button>
            </template>
            <template #submit-icon>
                <Save class="h-4 w-4" />
            </template>
        </UserForm>
    </div>
</template>
