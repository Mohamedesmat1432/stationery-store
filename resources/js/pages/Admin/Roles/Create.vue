<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ShieldCheck, Save, ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import { usePermissions } from '@/composables/usePermissions';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Roles & Permissions', href: '/admin/roles' },
            { title: 'Create Role', href: '/admin/roles/create' },
        ],
    },
});

const props = defineProps<{
    available_permissions: string[];
}>();

const form = useForm({
    name: '',
    permissions: [] as string[],
});

const { groupedPermissions, formatName, formatPermissionLabel, togglePermission, toggleModule } = usePermissions(form, props.available_permissions);

const submit = () => {
    form.post('/admin/roles');
};
</script>

<template>
    <Head :title="$t('Create Role')" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4 max-w-5xl mx-auto w-full">
        <form @submit.prevent="submit">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-xl font-bold flex items-center gap-2">
                            <ShieldCheck class="w-6 h-6" /> {{ $t('Create New Role') }}
                        </CardTitle>
                        <CardDescription>{{ $t('Define a new role and configure its access matrix.') }}</CardDescription>
                    </div>
                    <Button variant="outline" as-child type="button">
                        <Link href="/admin/roles" class="flex items-center gap-2">
                            <ArrowLeft class="w-4 h-4" /> {{ $t('Back') }}
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-8">
                    <!-- Role Name -->
                    <div class="space-y-2 max-w-md">
                        <Label for="name">{{ $t('Role Name') }} <span class="text-destructive">*</span></Label>
                        <Input 
                            id="name" 
                            v-model="form.name" 
                            :placeholder="$t('e.g. Content Manager')" 
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Permissions Matrix -->
                    <div class="space-y-4">
                        <Label class="text-lg font-semibold">{{ $t('Permissions Matrix') }}</Label>
                        <InputError :message="form.errors.permissions" />
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div 
                                v-for="(permissions, moduleName) in groupedPermissions" 
                                :key="moduleName"
                                class="rounded-lg border border-sidebar-border bg-sidebar p-4 space-y-4"
                            >
                                <div class="flex items-center justify-between border-b border-sidebar-border pb-2">
                                    <Label class="font-semibold capitalize text-base">{{ formatName(moduleName) }}</Label>
                                    <Button 
                                        type="button" 
                                        variant="ghost" 
                                        size="sm" 
                                        class="h-6 text-xs px-2"
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
                                            :model-value="form.permissions.includes(permission)"
                                            @update:model-value="togglePermission(permission)"
                                            :disabled="form.processing"
                                        />
                                        <label 
                                            :for="permission"
                                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer text-muted-foreground hover:text-foreground transition-colors"
                                        >
                                            {{ formatPermissionLabel(permission) }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <CardFooter class="border-t border-sidebar-border pt-6 flex justify-end">
                    <Button type="submit" :disabled="form.processing" class="flex items-center gap-2">
                        <Save class="w-4 h-4" /> {{ $t('Save Role') }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
