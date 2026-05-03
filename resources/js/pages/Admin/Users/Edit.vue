<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Save, ArrowLeft } from 'lucide-vue-next';
import { index } from '@/actions/Modules/Identity/Http/Controllers/UserController';
import { Button } from '@/components/ui/button';
import UserForm from '@/components/forms/UserForm.vue';
import { useUsers } from '@/composables/useUsers';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Users Management', href: index.url() },
            { title: 'Edit User', href: '#' },
        ],
    },
});

const props = defineProps<{
    user: Modules.Identity.Data.UserData;
    available_roles: string[];
}>();

const { form, submit } = useUsers(props.user);
const handleSubmit = () => submit(props.user.id!);
</script>

<template>
    <Head :title="$t('Edit User')" />

    <div class="mx-auto flex h-full w-full max-w-4xl flex-1 flex-col gap-4 overflow-x-auto p-4">
        <UserForm
            is-edit
            :user-name="user.name"
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
