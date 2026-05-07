<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight, ShieldCheck } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
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
import { usePermissions } from '@/composables/usePermissions';
import * as rolesRoutes from '@/routes/admin/roles/index';

const form = defineModel<any>('form', { required: true });
const {
    available_permissions = {},
    isEdit,
    roleName,
    canUpdate = true,
} = defineProps<{
    available_permissions?: Record<string, string[]>;
    isEdit?: boolean;
    roleName?: string;
    canUpdate?: boolean;
}>();

defineEmits(['submit']);

const {
    groupedPermissions,
    formatName,
    formatPermissionLabel,
    togglePermission,
    toggleModule,
} = usePermissions(form.value, () => available_permissions ?? {});

const expandedModules = ref<Record<string, boolean>>({});

const isAllExpanded = computed(() => {
    const keys = Object.keys(groupedPermissions.value);

    if (keys.length === 0) {
return false;
}

    return keys.every(key => expandedModules.value[key]);
});

const isAllCollapsed = computed(() => {
    const keys = Object.keys(groupedPermissions.value);

    if (keys.length === 0) {
return false;
}

    return keys.every(key => !expandedModules.value[key]);
});

const toggleModuleExpansion = (moduleName: string) => {
    expandedModules.value[moduleName] = !expandedModules.value[moduleName];
};

const expandAll = () => {
    Object.keys(groupedPermissions.value).forEach(module => {
        expandedModules.value[module] = true;
    });
};

const collapseAll = () => {
    Object.keys(groupedPermissions.value).forEach(module => {
        expandedModules.value[module] = false;
    });
};

// Initialize all modules as expanded when they become available
watch(
    () => groupedPermissions.value,
    (newVal) => {
        Object.keys(newVal).forEach((module) => {
            if (expandedModules.value[module] === undefined) {
                expandedModules.value[module] = true;
            }
        });
    },
    { immediate: true },
);
</script>

<template>
    <form @submit.prevent="$emit('submit')">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="flex items-center gap-2 text-xl font-bold">
                        <ShieldCheck class="h-6 w-6" />
                        {{ isEdit ? $t('Edit Role: {name}', { name: roleName }) : $t('Create New Role') }}
                    </CardTitle>
                    <CardDescription>{{
                        isEdit
                            ? $t('Modify role details and update its access matrix.')
                            : $t('Define a new role and configure its access matrix.')
                    }}</CardDescription>
                </div>
                <slot name="header-actions"></slot>
            </CardHeader>
            <CardContent class="space-y-8">
                <!-- Role Name -->
                <div class="max-w-md space-y-2">
                    <Label for="name">
                        {{ $t('Role Name') }}
                        <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="name"
                        name="name"
                        v-model="form.name"
                        :placeholder="$t('e.g. Content Manager')"
                        :disabled="form.processing || !canUpdate"
                    />
                    <p v-if="isEdit && !canUpdate" class="mt-1 text-xs text-muted-foreground">
                        {{ $t('You do not have permission to rename roles.') }}
                    </p>
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Permissions Matrix -->
                <div class="space-y-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <Label class="text-xl font-bold">{{ $t('Permissions Matrix') }}</Label>
                            <p class="text-sm text-muted-foreground">{{ $t('RoleFormDescription') }}</p>
                        </div>
                        <div class="flex items-center gap-2 rounded-lg border border-sidebar-border bg-sidebar/50 p-1">
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="h-8 px-3 text-xs font-medium transition-all"
                                :class="{ 'bg-background shadow-sm text-primary': isAllExpanded, 'hover:bg-background/50': !isAllExpanded }"
                                @click="expandAll"
                            >
                                <ChevronDown class="mr-2 h-4 w-4" />
                                {{ $t('Expand All') }}
                            </Button>
                            <div class="h-4 w-px bg-sidebar-border"></div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="h-8 px-3 text-xs font-medium transition-all"
                                :class="{ 'bg-background shadow-sm text-primary': isAllCollapsed, 'hover:bg-background/50': !isAllCollapsed }"
                                @click="collapseAll"
                            >
                                <ChevronRight class="mr-2 h-4 w-4" />
                                {{ $t('Collapse All') }}
                            </Button>
                        </div>
                    </div>
                    <InputError :message="form.errors.permissions" />

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="(permissions, moduleName) in groupedPermissions"
                            :key="moduleName"
                            class="flex flex-col overflow-hidden rounded-xl border border-sidebar-border bg-sidebar/30 transition-all duration-200 hover:border-primary/30"
                            :class="{ 'ring-1 ring-primary/20 bg-sidebar/50': expandedModules[moduleName] }"
                        >
                            <div 
                                class="flex cursor-pointer items-center justify-between p-4 transition-colors hover:bg-sidebar-accent/30"
                                @click="toggleModuleExpansion(moduleName as string)"
                            >
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="flex h-6 w-6 items-center justify-center rounded-md bg-background border border-sidebar-border transition-transform duration-200"
                                        :class="{ 'rotate-180': expandedModules[moduleName] }"
                                    >
                                        <ChevronDown class="h-3.5 w-3.5 text-muted-foreground" />
                                    </div>
                                    <Label class="cursor-pointer text-base font-bold tracking-tight capitalize">
                                        {{ formatName(moduleName as string) }}
                                    </Label>
                                </div>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 px-3 text-[10px] font-bold uppercase tracking-wider transition-all"
                                    :class="{
                                        'bg-background shadow-sm text-primary': permissions.every(p => form.permissions.includes(p)),
                                        'hover:bg-background/50 border border-transparent hover:border-sidebar-border': !permissions.every(p => form.permissions.includes(p))
                                    }"
                                    @click.stop="toggleModule(permissions)"
                                    :disabled="form.processing"
                                >
                                    {{ $t('Toggle All') }}
                                </Button>
                            </div>
                            <div 
                                v-show="expandedModules[moduleName]" 
                                class="space-y-3 border-t border-sidebar-border bg-background/40 p-4"
                            >
                                <div
                                    v-for="permission in permissions"
                                    :key="permission"
                                    class="group flex items-center justify-between rounded-lg p-2 transition-colors hover:bg-sidebar-accent/50"
                                >
                                    <div class="flex items-center space-x-3 rtl:space-x-reverse">
                                        <Checkbox
                                            :id="permission"
                                            name="permissions[]"
                                            :model-value="form.permissions.includes(permission)"
                                            @update:model-value="togglePermission(permission)"
                                            :disabled="form.processing"
                                            class="h-4 w-4 border-muted-foreground/30 data-[state=checked]:border-primary data-[state=checked]:bg-primary"
                                        />
                                        <label
                                            :for="permission"
                                            class="cursor-pointer text-sm font-medium text-muted-foreground transition-colors group-hover:text-foreground"
                                        >
                                            {{ formatPermissionLabel(permission) }}
                                        </label>
                                    </div>
                                    <div 
                                        class="h-1.5 w-1.5 rounded-full transition-colors"
                                        :class="form.permissions.includes(permission) ? 'bg-primary shadow-[0_0_8px_rgba(var(--primary),0.5)]' : 'bg-muted-foreground/10'"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
            <CardFooter class="flex items-center justify-between border-t border-sidebar-border pt-6">
                <Button variant="ghost" as-child type="button">
                    <Link :href="rolesRoutes.index.url()">
                        {{ $t('Cancel') }}
                    </Link>
                </Button>
                <Button type="submit" :disabled="form.processing" class="flex items-center gap-2">
                    <slot name="submit-icon"></slot>
                    {{ isEdit ? $t('Update Role') : $t('Save Role') }}
                </Button>
            </CardFooter>
        </Card>
    </form>
</template>
