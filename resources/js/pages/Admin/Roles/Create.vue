<script setup lang="ts">
import { Head, Link, useForm, Deferred } from '@inertiajs/vue3';
import { ShieldCheck, Save, ArrowLeft } from 'lucide-vue-next';
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

const {
    groupedPermissions,
    formatName,
    formatPermissionLabel,
    togglePermission,
    toggleModule,
} = usePermissions(form, () => props.available_permissions);

const submit = () => {
    form.post('/admin/roles');
};
</script>

<template>
    <Head :title="$t('Create Role')" />

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
                            {{ $t('Create New Role') }}
                        </CardTitle>
                        <CardDescription>{{
                            $t(
                                'Define a new role and configure its access matrix.',
                            )
                        }}</CardDescription>
                    </div>
                    <Button variant="outline" as-child type="button">
                        <Link
                            href="/admin/roles"
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
                            :disabled="form.processing"
                        />
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
                            <Deferred data="available_permissions">
                                <template #fallback>
                                    <div
                                        v-for="i in 6"
                                        :key="i"
                                        class="space-y-4 rounded-lg border border-sidebar-border bg-sidebar p-4"
                                    >
                                        <div
                                            class="flex items-center justify-between border-b border-sidebar-border pb-2"
                                        >
                                            <Skeleton class="h-5 w-24" />
                                            <Skeleton class="h-6 w-16" />
                                        </div>
                                        <div class="space-y-3">
                                            <div
                                                v-for="j in 4"
                                                :key="j"
                                                class="flex items-center space-x-2"
                                            >
                                                <Skeleton class="h-4 w-4" />
                                                <Skeleton class="h-4 w-32" />
                                            </div>
                                        </div>
                                    </div>
                                </template>

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
                                            >{{ formatName(moduleName) }}</Label
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
                            </Deferred>
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
                        <Save class="h-4 w-4" /> {{ $t('Save Role') }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
