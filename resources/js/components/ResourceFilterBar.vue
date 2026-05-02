<script setup lang="ts">
import { Filter } from 'lucide-vue-next';
import SearchInput from '@/components/SearchInput.vue';
import { Checkbox } from '@/components/ui/checkbox';

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
    <div class="mb-6 flex flex-col gap-4 md:flex-row">
        <SearchInput
            :model-value="modelSearch || ''"
            :placeholder="$t(searchPlaceholder)"
            @search="handleSearch"
        />

        <slot name="filters"></slot>

        <div class="ms-auto flex items-center gap-2 px-3">
            <Checkbox
                id="show-trashed"
                :model-value="modelTrashed"
                @update:model-value="(val) => (modelTrashed = !!val)"
            />
            <label
                for="show-trashed"
                class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
            >
                {{ $t('Show Trashed') }}
            </label>
        </div>
    </div>
</template>
