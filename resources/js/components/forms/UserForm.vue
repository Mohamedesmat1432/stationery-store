<script setup lang="ts">
import { Deferred, Link } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import { useRoles } from '@/composables/useRoles';
import * as usersRoutes from '@/routes/admin/users/index';

const props = withDefaults(
    defineProps<{
        form: any;
        available_roles?: string[];
        isEdit?: boolean;
        userName?: string;
    }>(),
    {
        available_roles: () => [],
    },
);

defineEmits(['submit']);

const { formatRoleName, toggleRole } = useRoles(props.form);
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl font-bold">
                            <Users class="h-6 w-6 text-primary" />
                            {{ isEdit ? $t('Edit User') + ': ' + userName : $t('Create New User') }}
                        </CardTitle>
                        <CardDescription>{{
                            isEdit
                                ? $t('Update user profile and adjust assigned roles.')
                                : $t('Add a new user and assign them roles.')
                        }}</CardDescription>
                    </div>
                    <slot name="header-actions"></slot>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">
                                {{ $t('Full Name') }}
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="name"
                                name="name"
                                autocomplete="name"
                                v-model="form.name!"
                                :placeholder="$t('John Doe')"
                                :disabled="form.processing"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="email">
                                {{ $t('Email Address') }}
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="email"
                                name="email"
                                autocomplete="email"
                                type="email"
                                v-model="form.email!"
                                placeholder="john@example.com"
                                :disabled="form.processing"
                                required
                            />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <div class="max-w-md space-y-2">
                        <Label for="password">
                            {{ $t('Password') }}
                            <span v-if="!isEdit" class="text-destructive">*</span>
                            <span v-else class="text-xs font-normal text-muted-foreground">
                                ({{ $t('Leave blank to keep current') }})
                            </span>
                        </Label>
                        <Input
                            id="password"
                            name="password"
                            :autocomplete="isEdit ? 'new-password' : 'password'"
                            type="password"
                            v-model="form.password!"
                            placeholder="••••••••"
                            :disabled="form.processing"
                            :required="!isEdit"
                        />
                        <InputError :message="form.errors.password" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">{{ $t('Security & Roles') }}</CardTitle>
                    <CardDescription>{{ $t('Assign permissions via roles to this user account.') }}</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <InputError :message="form.errors.roles" />

                    <div class="space-y-3">
                        <Deferred data="available_roles">
                            <template #fallback>
                                <div
                                    v-for="i in 3"
                                    :key="i"
                                    class="flex items-center space-x-2 rounded-lg border border-sidebar-border p-3"
                                >
                                    <Skeleton class="h-4 w-4" />
                                    <Skeleton class="h-4 w-24" />
                                </div>
                            </template>
                            <div
                                v-for="role in available_roles ?? []"
                                :key="role"
                                class="flex items-center space-x-3 rounded-lg border border-sidebar-border p-3 transition-colors hover:bg-sidebar-accent/50"
                            >
                                <Checkbox
                                    :id="'role-' + role"
                                    name="roles[]"
                                    :model-value="form.roles.includes(role)"
                                    @update:model-value="toggleRole(role)"
                                />
                                <label
                                    :for="'role-' + role"
                                    class="cursor-pointer text-sm leading-none font-medium flex-1"
                                >
                                    {{ formatRoleName(role) }}
                                </label>
                            </div>
                        </Deferred>
                    </div>
                </CardContent>
            </Card>

            <div class="flex flex-col gap-3">
                <Button type="submit" :disabled="form.processing" class="w-full h-12 text-lg shadow-xl font-bold">
                    <slot name="submit-icon"></slot>
                    {{ isEdit ? $t('Update User') : $t('Save User') }}
                </Button>
                
                <Button variant="ghost" as-child class="w-full" type="button">
                    <Link :href="usersRoutes.index.url()">
                        {{ $t('Cancel') }}
                    </Link>
                </Button>
            </div>
        </div>
    </form>
</template>
