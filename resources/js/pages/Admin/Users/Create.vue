<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Users, Save, ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import { useRoles } from '@/composables/useRoles';
import { useUsers } from '@/composables/useUsers';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Users Management', href: '/admin/users' },
            { title: 'Create User', href: '/admin/users/create' },
        ],
    },
});

const props = defineProps<{
    available_roles: string[];
}>();

const { form, submit } = useUsers();
const { formatRoleName, toggleRole } = useRoles(form);

const handleSubmit = () => {
    submit();
};
</script>

<template>
    <Head :title="$t('Create User')" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4 max-w-4xl mx-auto w-full">
        <form @submit.prevent="handleSubmit">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-xl font-bold flex items-center gap-2">
                            <Users class="w-6 h-6" /> {{ $t('Create New User') }}
                        </CardTitle>
                        <CardDescription>{{ $t('Add a new user and assign them roles.') }}</CardDescription>
                    </div>
                    <Button variant="outline" as-child type="button">
                        <Link href="/admin/users" class="flex items-center gap-2">
                            <ArrowLeft class="w-4 h-4" /> {{ $t('Back') }}
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <Label for="name">{{ $t('Full Name') }} <span class="text-destructive">*</span></Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
                                :placeholder="$t('John Doe')" 
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="email">{{ $t('Email Address') }} <span class="text-destructive">*</span></Label>
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

                    <div class="space-y-2 max-w-md">
                        <Label for="password">{{ $t('Password') }} <span class="text-destructive">*</span></Label>
                        <Input 
                            id="password" 
                            type="password"
                            v-model="form.password" 
                            placeholder="••••••••" 
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="space-y-4 pt-4 border-t border-sidebar-border">
                        <Label class="text-lg font-semibold">{{ $t('Assign Roles') }}</Label>
                        <InputError :message="form.errors.roles" />
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div 
                                v-for="role in available_roles" 
                                :key="role"
                                class="flex items-center space-x-2 border border-sidebar-border rounded-lg p-4 hover:bg-sidebar-accent/50 transition-colors"
                            >
                                <Checkbox 
                                    :id="'role-' + role" 
                                    :model-value="form.roles.includes(role)"
                                    @update:model-value="toggleRole(role)"
                                />
                                <label 
                                    :for="'role-' + role"
                                    class="text-sm font-medium leading-none cursor-pointer"
                                >
                                    {{ formatRoleName(role) }}
                                </label>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <CardFooter class="border-t border-sidebar-border pt-6 flex justify-end">
                    <Button type="submit" :disabled="form.processing" class="flex items-center gap-2">
                        <Save class="w-4 h-4" /> {{ $t('Save User') }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
