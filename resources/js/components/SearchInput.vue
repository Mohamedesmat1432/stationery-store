<script setup lang="ts">
import { ref, watch } from 'vue';
import { Search, X } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { useDebounceFn } from '@vueuse/core';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'search', value: string): void;
}>();

const internalValue = ref(props.modelValue);

watch(() => props.modelValue, (newVal) => {
    internalValue.value = newVal;
});

const debouncedSearch = useDebounceFn((value: string) => {
    emit('search', value);
}, 500);

watch(internalValue, (newVal) => {
    emit('update:modelValue', newVal);
    debouncedSearch(newVal);
});

const clear = () => {
    internalValue.value = '';
};
</script>

<template>
    <div class="relative w-full max-w-sm flex items-center">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
            v-model="internalValue"
            :placeholder="placeholder || 'Search...'"
            class="pl-9 pr-9 h-9"
        />
        <button
            v-if="internalValue"
            type="button"
            @click="clear"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
