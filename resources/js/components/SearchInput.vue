<script setup lang="ts">
import { useDebounceFn } from '@vueuse/core';
import { Search, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'search', value: string): void;
}>();

const internalValue = ref(props.modelValue);

watch(
    () => props.modelValue,
    (newVal) => {
        internalValue.value = newVal;
    },
);

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
    <div class="relative flex w-full max-w-sm items-center">
        <Search
            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
        />
        <Input
            v-model="internalValue"
            :placeholder="placeholder || 'Search...'"
            class="h-9 pr-9 pl-9"
        />
        <button
            v-if="internalValue"
            type="button"
            @click="clear"
            class="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
