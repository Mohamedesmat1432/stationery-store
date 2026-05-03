<script setup lang="ts">
import { Deferred } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
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
import { Skeleton } from '@/components/ui/skeleton';
import { useRoles } from '@/composables/useRoles';

const props = defineProps<{
    form: any;
    available_roles: string[];
    isEdit?: boolean;
    userName?: string;
}>();

defineEmits(['submit']);

const { formatRoleName, toggleRole } = useRoles(props.form);
</script>

<template>
    <form @submit.prevent="$emit('submit')">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="flex items-center gap-2 text-xl font-bold">
                        <Users class="h-6 w-6" />
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
                            v-model="form.name"
                            :placeholder="$t('John Doe')"
                            :disabled="form.processing"
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
                            type="email"
                            v-model="form.email"
                            placeholder="john@example.com"
                            :disabled="form.processing"
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
                        type="password"
                        v-model="form.password"
                        placeholder="••••••••"
                        :disabled="form.processing"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="space-y-4 border-t border-sidebar-border pt-4">
                    <Label class="text-lg font-semibold">{{ $t('Assign Roles') }}</Label>
                    <InputError :message="form.errors.roles" />

                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <Deferred data="available_roles">
                            <template #fallback>
                                <div
                                    v-for="i in 4"
                                    :key="i"
                                    class="flex items-center space-x-2 rounded-lg border border-sidebar-border p-4"
                                >
                                    <Skeleton class="h-4 w-4" />
                                    <Skeleton class="h-4 w-24" />
                                </div>
                            </template>
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
                        </Deferred>
                    </div>
                </div>
            </CardContent>
            <CardFooter class="flex justify-end border-t border-sidebar-border pt-6">
                <Button type="submit" :disabled="form.processing" class="flex items-center gap-2">
                    <slot name="submit-icon"></slot>
                    {{ isEdit ? $t('Update User') : $t('Save User') }}
                </Button>
            </CardFooter>
        </Card>
    </form>
</template>
