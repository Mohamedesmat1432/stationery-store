<script setup lang="ts">
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <div class="relative flex w-full max-w-md items-center rounded-xl bg-muted/50 p-1 backdrop-blur-sm">
        <!-- Sliding Indicator Background -->
        <div 
            class="absolute h-[calc(100%-8px)] w-[calc(33.33%-4px)] rounded-lg bg-card shadow-sm ring-1 ring-primary/20 transition-all duration-300 ease-in-out"
            :style="{
                insetInlineStart: appearance === 'light' ? '4px' : appearance === 'dark' ? '33.33%' : '66.66%'
            }"
        />

        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            class="relative z-10 flex flex-1 items-center justify-center gap-2 rounded-lg py-3 transition-colors duration-150"
            :class="[
                appearance === value
                    ? 'text-primary font-bold'
                    : 'text-muted-foreground hover:text-foreground hover:bg-muted/30'
            ]"
        >
            <component :is="Icon" class="size-4 shrink-0" />
            <span class="truncate text-xs sm:text-sm">{{ $t(label) }}</span>
        </button>
    </div>
</template>
