<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Plus, Trash2, RotateCcw, Trash } from 'lucide-vue-next';
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
}

const props = withDefaults(defineProps<Props>(), {
    canCreate: false,
    canDelete: false,
    canRestore: false,
    canForceDelete: false,
    selectedCount: 0,
    showTrashed: false,
});

defineEmits(['bulk-delete', 'bulk-restore', 'bulk-force-delete']);
</script>

<template>
    <CardHeader class="flex flex-row items-center justify-between">
        <div>
            <CardTitle class="flex items-center gap-2 text-xl font-bold">
                <component :is="icon" v-if="icon" class="h-6 w-6" />
                {{ $t(title) }}
            </CardTitle>
            <CardDescription v-if="description">{{
                $t(description)
            }}</CardDescription>
        </div>
        <div class="flex items-center gap-2">
            <template v-if="selectedCount > 0">
                <template v-if="!showTrashed">
                    <Button
                        :disabled="!canDelete"
                        variant="destructive"
                        size="sm"
                        class="flex items-center gap-2"
                        @click="$emit('bulk-delete')"
                    >
                        <Trash2 class="h-4 w-4" />
                        {{
                            $t('Delete Selected ({count})', {
                                count: selectedCount,
                            })
                        }}
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
                        <RotateCcw class="h-4 w-4" />
                        {{
                            $t('Restore Selected ({count})', {
                                count: selectedCount,
                            })
                        }}
                    </Button>
                    <Button
                        :disabled="!canForceDelete"
                        variant="destructive"
                        size="sm"
                        class="flex items-center gap-2"
                        @click="$emit('bulk-force-delete')"
                    >
                        <Trash class="h-4 w-4" />
                        {{
                            $t('Force Delete Selected ({count})', {
                                count: selectedCount,
                            })
                        }}
                    </Button>
                </template>
            </template>

            <slot name="actions"></slot>

            <Button
                v-if="createUrl && canCreate"
                as-child
                class="flex items-center gap-2"
            >
                <Link :href="createUrl">
                    <Plus class="h-4 w-4" />
                    {{
                        createLabel
                            ? $t(createLabel)
                            : $t('Create {title}', { title: $t(title) })
                    }}
                </Link>
            </Button>
        </div>
    </CardHeader>
</template>
