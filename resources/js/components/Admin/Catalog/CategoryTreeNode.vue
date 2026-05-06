<script setup lang="ts">
import draggable from 'vuedraggable';
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    ChevronRight, 
    ChevronDown, 
    Pencil, 
    Trash2, 
    GripVertical, 
    Power,
    Layers,
    Tag,
    Star,
    RotateCcw,
    Trash
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import * as categoriesRoutes from '@/routes/admin/categories/index';
import type { Auth } from '@/types';

const permissions = computed<string[]>(() => (usePage().props.auth as Auth)?.permissions ?? []);
const can = (permission: string) => permissions.value.includes(permission);

type CategoryData = Modules.Catalog.Data.CategoryData;

const props = defineProps<{
    category: CategoryData;
    depth: number;
    selectedIds: (string | number)[];
    isDraggableDisabled?: boolean;
}>();

// Inject actions from root to avoid event bubbling through recursive layers
import { inject } from 'vue';
const actions = inject<any>('categoryActions');

const isExpanded = ref(true);

const toggleExpand = () => {
    isExpanded.value = !isExpanded.value;
};

const formatNumber = (value: number) => {
    return new Intl.NumberFormat().format(value);
};
</script>

<template>
    <div class="flex flex-col">
        <!-- Main Row -->
        <div 
            class="grid grid-cols-[48px_1fr_80px] sm:grid-cols-[48px_1fr_100px_200px] md:grid-cols-[48px_1fr_100px_120px_200px] lg:grid-cols-[48px_1fr_100px_120px_120px_200px] items-center table-row-themed group"
            :class="{ 'opacity-60 grayscale-[0.5]': category.deleted_at }"
        >
            <div class="px-4 sm:px-6 py-4 flex items-center">
                <Checkbox 
                    :model-value="selectedIds.includes(category.id!)" 
                    @update:model-value="actions.toggleSelect(category.id!)"
                />
            </div>
            
            <div class="px-4 sm:px-6 py-4">
                <div class="flex items-center gap-2" :style="{ marginLeft: `${depth * 1.5}rem` }">
                    <div class="w-6 flex items-center justify-center flex-shrink-0">
                        <button 
                            v-if="category.children && category.children.length > 0"
                            @click="toggleExpand"
                            class="p-1 hover:bg-accent rounded-sm transition-colors"
                        >
                            <ChevronDown v-if="isExpanded" class="w-3.5 h-3.5 text-muted-foreground" />
                            <ChevronRight v-else class="w-3.5 h-3.5 text-muted-foreground" />
                        </button>
                        <div v-else class="w-3.5 h-3.5 border-l-2 border-b-2 border-muted/30 ml-1 mb-1 rounded-bl-sm"></div>
                    </div>

                    <div class="flex items-center gap-3 min-w-0">
                        <GripVertical v-if="!category.deleted_at" class="w-3.5 h-3.5 text-muted-foreground/20 cursor-grab active:cursor-grabbing opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" />
                        
                        <!-- Media Icon -->
                        <div class="hidden sm:flex w-8 h-8 rounded border border-sidebar-border bg-sidebar/50 items-center justify-center overflow-hidden flex-shrink-0 group-hover:bg-sidebar transition-colors">
                            <img v-if="category.icon" :src="category.icon" class="w-full h-full object-contain p-0.5" />
                            <Tag v-else class="w-3.5 h-3.5 text-muted-foreground/30" />
                        </div>

                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-inherit text-sm truncate" :class="{ 'line-through decoration-muted-foreground/50': category.deleted_at }">
                                    {{ category.name }}
                                </span>
                                <Badge v-if="category.parent_name && isDraggableDisabled" variant="outline" class="text-[8px] px-1 py-0 h-3.5 flex-shrink-0 bg-sidebar border-sidebar-border">
                                    in {{ category.parent_name }}
                                </Badge>
                                <Star v-if="category.is_featured" class="w-2.5 h-2.5 text-amber-500 fill-amber-500 flex-shrink-0" />
                                <Badge v-if="category.deleted_at" variant="outline" class="bg-destructive/5 text-destructive border-destructive/20 text-[8px] px-1 py-0 h-3.5 flex-shrink-0">
                                    Trashed
                                </Badge>
                            </div>
                            <span class="text-[9px] text-muted-foreground font-mono uppercase tracking-tighter truncate">{{ category.slug }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden sm:flex px-4 sm:px-6 py-4">
                <template v-if="!category.deleted_at">
                    <Badge v-if="category.is_active" variant="outline" class="bg-green-500/5 text-green-600 border-green-500/20 text-[9px] px-1.5 py-0">
                        Active
                    </Badge>
                    <Badge v-else variant="outline" class="bg-destructive/5 text-destructive border-destructive/20 text-[9px] px-1.5 py-0">
                        Inactive
                    </Badge>
                </template>
                <span v-else class="text-xs text-muted-foreground italic">-</span>
            </div>

            <div class="hidden md:flex px-4 sm:px-6 py-4">
                <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground">
                    <Layers class="w-2.5 h-2.5" />
                    <span>{{ category.children?.length ?? 0 }} sub</span>
                </div>
            </div>

            <div class="hidden lg:flex px-4 sm:px-6 py-4 font-medium">
                <div class="flex items-center gap-1.5 text-xs">
                    <Tag class="w-3 h-3 text-muted-foreground/50" />
                    <span>{{ formatNumber(category.total_product_count) }}</span>
                </div>
            </div>

            <div class="px-4 sm:px-6 py-4 flex items-center">
                <div class="flex items-center gap-1 sm:gap-2">
                    <!-- Non-deleted actions -->
                    <template v-if="!category.deleted_at">
                        <Button 
                            v-if="can('update_categories')"
                            variant="outline" 
                            size="icon" 
                            class="h-7 w-7 sm:h-8 sm:w-8"
                            @click="actions.toggleActive(category)"
                            :title="category.is_active ? 'Deactivate' : 'Activate'"
                        >
                            <Power :class="['w-3.5 h-3.5', category.is_active ? 'text-green-500' : 'text-muted-foreground']" />
                        </Button>
                        
                        <Button 
                            v-if="can('update_categories')"
                            variant="outline" 
                            size="icon" 
                            class="h-7 w-7 sm:h-8 sm:w-8" 
                            as-child
                        >
                            <Link :href="categoriesRoutes.edit(category.id!).url">
                                <Pencil class="w-3.5 h-3.5" />
                            </Link>
                        </Button>
                        
                        <Button 
                            v-if="can('delete_categories')"
                            variant="destructive" 
                            size="icon" 
                            class="h-7 w-7 sm:h-8 sm:w-8"
                            @click="actions.delete(category.id!)"
                            title="Delete"
                        >
                            <Trash2 class="w-3.5 h-3.5" />
                        </Button>
                    </template>

                    <!-- Deleted actions -->
                    <template v-else>
                        <Button 
                            v-if="can('restore_categories')"
                            variant="outline" 
                            size="icon" 
                            class="h-7 w-7 sm:h-8 sm:w-8"
                            @click="actions.restore(category.id!)"
                            title="Restore"
                        >
                            <RotateCcw class="w-3.5 h-3.5 text-green-600" />
                        </Button>
                        
                        <Button 
                            v-if="can('force_delete_categories')"
                            variant="destructive" 
                            size="icon" 
                            class="h-7 w-7 sm:h-8 sm:w-8"
                            @click="actions.forceDelete(category.id!)"
                            title="Permanently Delete"
                        >
                            <Trash class="w-3.5 h-3.5" />
                        </Button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Recursive Children Rows -->
        <div v-if="isExpanded && category.children && category.children.length > 0">
            <draggable 
                v-model="category.children" 
                item-key="id" 
                handle=".cursor-grab"
                @end="actions.reorder"
                :animation="200"
                ghost-class="opacity-50"
                :disabled="isDraggableDisabled"
            >
                <template #item="{ element }">
                    <CategoryTreeNode 
                        :category="element"
                        :depth="depth + 1"
                        :selected-ids="selectedIds"
                        :is-draggable-disabled="isDraggableDisabled"
                    />
                </template>
            </draggable>
        </div>
    </div>
</template>
