<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { Component } from 'vue';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ResourceFilterBar from '@/components/ResourceFilterBar.vue';
import ResourcePagination from '@/components/ResourcePagination.vue';
import { Card, CardContent } from '@/components/ui/card';


interface Props {
    title: string;
    description?: string;
    icon?: Component;
    selectedCount?: number;
    showTrashed?: boolean;
    canCreate?: boolean;
    createUrl?: string;
    createLabel?: string;
    canDelete?: boolean;
    canRestore?: boolean;
    canForceDelete?: boolean;
    canActivate?: boolean;
    canDeactivate?: boolean;
    searchQuery: string;
    searchPlaceholder?: string;
    canShowTrashed?: boolean;
    paginationLinks?: any[];
    paginationTotal?: number;
    paginationCount?: number;
    resourceName: string;
    confirmState: {
        isOpen: boolean;
        title: any;
        description: any;
        variant: any;
        confirmLabel: string;
        loading: boolean;
        onConfirm: () => void;
    };
}

const confirmState = defineModel<any>('confirmState', { required: true });
const {
    title,
    description,
    icon,
    selectedCount = 0,
    showTrashed = false,
    canCreate = false,
    createUrl,
    createLabel,
    canDelete = false,
    canRestore = false,
    canForceDelete = false,
    searchQuery,
    searchPlaceholder = 'Search...',
    canShowTrashed = true,
    paginationLinks,
    paginationTotal,
    paginationCount,
    resourceName,
} = defineProps<Omit<Props, 'confirmState'>>();

const emit = defineEmits<{
    (e: 'update:searchQuery', value: string | undefined): void;
    (e: 'update:showTrashed', value: boolean | undefined): void;
    (e: 'search'): void;
    (e: 'bulk-delete'): void;
    (e: 'bulk-restore'): void;
    (e: 'bulk-force-delete'): void;
    (e: 'bulk-activate'): void;
    (e: 'bulk-deactivate'): void;
    (e: 'clear-filters'): void;
}>();
</script>

<template>
    <Head :title="$t(title)" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <Card>
            <AdminPageHeader
                :title="title"
                :description="description"
                :icon="icon"
                :selected-count="selectedCount"
                :show-trashed="showTrashed"
                :can-create="canCreate"
                :create-url="createUrl"
                :create-label="createLabel"
                :can-delete="canDelete"
                :can-restore="canRestore"
                :can-force-delete="canForceDelete"
                :can-activate="canActivate"
                :can-deactivate="canDeactivate"
                @bulk-delete="emit('bulk-delete')"
                @bulk-restore="emit('bulk-restore')"
                @bulk-force-delete="emit('bulk-force-delete')"
                @bulk-activate="emit('bulk-activate')"
                @bulk-deactivate="emit('bulk-deactivate')"
            >
                <template #actions>
                    <slot name="header-actions" />
                </template>
            </AdminPageHeader>

            <CardContent>
                <ResourceFilterBar
                    :search="searchQuery"
                    :trashed="showTrashed"
                    :can-show-trashed="canShowTrashed"
                    :search-placeholder="searchPlaceholder"
                    @update:search="emit('update:searchQuery', $event)"
                    @update:trashed="emit('update:showTrashed', $event); emit('search')"
                    @search="emit('search')"
                >
                    <template #extra-filters>
                        <slot name="extra-filters" />
                    </template>
                </ResourceFilterBar>

                <div class="overflow-x-auto rounded-md border border-sidebar-border">
                    <slot />
                </div>

                <div v-if="(paginationLinks?.length ?? 0) > 3" class="mt-6">
                    <ResourcePagination
                        :links="paginationLinks ?? []"
                        :total="paginationTotal ?? 0"
                        :count="paginationCount ?? 0"
                        :resource-name="resourceName"
                    />
                </div>
            </CardContent>
        </Card>
    </div>

    <ConfirmDialog
        v-model:open="confirmState.isOpen"
        :title="confirmState.title"
        :description="confirmState.description"
        :variant="confirmState.variant"
        :confirm-label="confirmState.confirmLabel"
        :loading="confirmState.loading"
        @confirm="confirmState.onConfirm"
    />
</template>
