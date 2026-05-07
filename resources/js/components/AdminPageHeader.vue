<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Plus, Trash2, RotateCcw, Trash, CheckCircle, XCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
    title: string;
    description?: string;
    icon?: any;
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
}

withDefaults(defineProps<Props>(), {
    canDelete: false,
    canRestore: false,
    canForceDelete: false,
    canActivate: false,
    canDeactivate: false,
    selectedCount: 0,
    showTrashed: false,
});

defineEmits(['bulk-delete', 'bulk-restore', 'bulk-force-delete', 'bulk-activate', 'bulk-deactivate']);
</script>

<template>
    <CardHeader class="flex flex-col gap-4 space-y-0 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <CardTitle class="flex items-center gap-2 text-xl font-bold">
                <component :is="icon" v-if="icon" class="h-6 w-6 shrink-0 text-primary" />
                <span class="truncate">{{ $t(title) }}</span>
            </CardTitle>
            <CardDescription v-if="description" class="line-clamp-2 sm:line-clamp-none">
                {{ $t(description) }}
            </CardDescription>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <template v-if="selectedCount > 0">
                <template v-if="!showTrashed">
                    <Button
                        :disabled="!canDelete"
                        variant="destructive"
                        size="sm"
                        class="flex items-center gap-2"
                        @click="$emit('bulk-delete')"
                    >
                        <Trash2 class="h-4 w-4 shrink-0" />
                        <span class="whitespace-nowrap">
                            {{
                                $t('Delete Selected ({count})', {
                                    count: selectedCount,
                                })
                            }}
                        </span>
                    </Button>

                    <Button
                        v-if="canActivate"
                        variant="outline"
                        size="sm"
                        class="flex items-center gap-2 border-success text-success hover:bg-success hover:text-white"
                        @click="$emit('bulk-activate')"
                    >
                        <CheckCircle class="h-4 w-4 shrink-0" />
                        <span class="whitespace-nowrap">
                            {{
                                $t('Activate Selected ({count})', {
                                    count: selectedCount,
                                })
                            }}
                        </span>
                    </Button>

                    <Button
                        v-if="canDeactivate"
                        variant="outline"
                        size="sm"
                        class="flex items-center gap-2 border-warning text-warning hover:bg-warning hover:text-white"
                        @click="$emit('bulk-deactivate')"
                    >
                        <XCircle class="h-4 w-4 shrink-0" />
                        <span class="whitespace-nowrap">
                            {{
                                $t('Deactivate Selected ({count})', {
                                    count: selectedCount,
                                })
                            }}
                        </span>
                    </Button>
                </template>
                <template v-else>
                    <Button
                        :disabled="!canRestore"
                        variant="outline"
                        size="sm"
                        class="flex items-center gap-2"
                        @click="$emit('bulk-restore')"
                    >
                        <RotateCcw class="h-4 w-4 shrink-0" />
                        <span class="whitespace-nowrap">
                            {{
                                $t('Restore Selected ({count})', {
                                    count: selectedCount,
                                })
                            }}
                        </span>
                    </Button>
                    <Button
                        :disabled="!canForceDelete"
                        variant="destructive"
                        size="sm"
                        class="flex items-center gap-2"
                        @click="$emit('bulk-force-delete')"
                    >
                        <Trash class="h-4 w-4 shrink-0" />
                        <span class="whitespace-nowrap">
                            {{
                                $t('Force Delete Selected ({count})', {
                                    count: selectedCount,
                                })
                            }}
                        </span>
                    </Button>
                </template>
            </template>

            <div class="flex flex-wrap items-center gap-2">
                <slot name="actions"></slot>

                <Button
                    v-if="createUrl && canCreate"
                    as-child
                    class="flex items-center gap-2"
                >
                    <Link :href="createUrl">
                        <Plus class="h-4 w-4 shrink-0" />
                        <span class="whitespace-nowrap">
                            {{
                                createLabel
                                    ? $t(createLabel)
                                    : $t('Create {title}', { title: $t(title) })
                            }}
                        </span>
                    </Link>
                </Button>
            </div>
        </div>
    </CardHeader>
</template>
