<script setup lang="ts">
import { Filter } from 'lucide-vue-next';
import { Checkbox } from '@/components/ui/checkbox';
import SearchInput from '@/components/SearchInput.vue';

interface Props {
    searchPlaceholder?: string;
    showTrashed?: boolean;
}

const modelSearch = defineModel<string>('search');
const modelTrashed = defineModel<boolean>('trashed');

withDefaults(defineProps<Props>(), {
    searchPlaceholder: 'Search...',
    showTrashed: false,
});

const emit = defineEmits(['search']);

const handleSearch = (val: string) => {
    modelSearch.value = val;
    emit('search', val);
};
</script>

<template>
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <SearchInput
            :model-value="modelSearch || ''"
            :placeholder="$t(searchPlaceholder)"
            @search="handleSearch"
        />
        
        <slot name="filters"></slot>

        <div class="flex items-center gap-2 px-3 ms-auto">
            <Checkbox 
                id="show-trashed" 
                :model-value="modelTrashed" 
                @update:model-value="(val) => modelTrashed = !!val"
            />
            <label for="show-trashed" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                {{ $t('Show Trashed') }}
            </label>
        </div>
    </div>
</template>
