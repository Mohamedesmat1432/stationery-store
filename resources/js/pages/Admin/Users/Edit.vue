<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Users, Save, ArrowLeft } from 'lucide-vue-next';
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
import { useRoles } from '@/composables/useRoles';
import { useUsers } from '@/composables/useUsers';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Users Management', href: '/admin/users' },
            { title: 'Edit User', href: '#' },
        ],
    },
});

const props = defineProps<{
    user: any;
    available_roles: string[];
}>();

const { form, submit } = useUsers(props.user);
const { formatRoleName, toggleRole } = useRoles(form);

const handleSubmit = () => {
    submit(props.user.id);
};
</script>

<template>
    <Head :title="$t('Edit User')" />

    <div
        class="mx-auto flex h-full w-full max-w-4xl flex-1 flex-col gap-4 overflow-x-auto p-4"
    >
        <form @submit.prevent="handleSubmit">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle
                            class="flex items-center gap-2 text-xl font-bold"
                        >
                            <Users class="h-6 w-6" /> {{ $t('Edit User') }}:
                            {{ user.name }}
                        </CardTitle>
                        <CardDescription>{{
                            $t('Update user profile and adjust assigned roles.')
                        }}</CardDescription>
                    </div>
                    <Button variant="outline" as-child type="button">
                        <Link
                            href="/admin/users"
                            class="flex items-center gap-2"
                        >
                            <ArrowLeft class="h-4 w-4" /> {{ $t('Back') }}
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name"
                                >{{ $t('Full Name') }}
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                id="name"
                                v-model="form.name"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="email"
                                >{{ $t('Email Address') }}
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <div class="max-w-md space-y-2">
                        <Label for="password"
                            >{{ $t('Password') }}
                            <span
                                class="text-xs font-normal text-muted-foreground"
                                >({{ $t('Leave blank to keep current') }})</span
                            ></Label
                        >
                        <Input
                            id="password"
                            type="password"
                            v-model="form.password"
                            placeholder="••••••••"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="space-y-4 border-t border-sidebar-border pt-4">
                        <Label class="text-lg font-semibold">{{
                            $t('Assign Roles')
                        }}</Label>
                        <InputError :message="form.errors.roles" />

                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <div
                                v-for="role in available_roles"
                                :key="role"
                                class="flex items-center space-x-2 rounded-lg border border-sidebar-border p-4 transition-colors hover:bg-sidebar-accent/50"
                            >
                                <Checkbox
                                    :id="'role-' + role"
                                    :model-value="form.roles.includes(role)"
                                    @update:model-value="toggleRole(role)"
                                />
                                <label
                                    :for="'role-' + role"
                                    class="cursor-pointer text-sm leading-none font-medium"
                                >
                                    {{ formatRoleName(role) }}
                                </label>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <CardFooter
                    class="flex justify-end border-t border-sidebar-border pt-6"
                >
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2"
                    >
                        <Save class="h-4 w-4" /> {{ $t('Update User') }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
