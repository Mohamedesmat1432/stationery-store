<script setup lang="ts">
import { ACCENT_COLORS, useAppearance } from '@/composables/useAppearance';
import { Check } from 'lucide-vue-next';

const { resolvedAppearance, accentColor, updateAccentColor } = useAppearance();

const colors = Object.keys(ACCENT_COLORS) as Array<keyof typeof ACCENT_COLORS>;
</script>

<template>
    <div class="flex flex-wrap gap-4">
        <button
            v-for="color in colors"
            :key="color"
            @click="updateAccentColor(color)"
            :class="[
                'group relative flex h-10 w-10 items-center justify-center rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-4 dark:focus:ring-offset-neutral-950',
                accentColor === color
                    ? 'scale-110 shadow-lg ring-2 ring-ring ring-offset-2 dark:ring-offset-neutral-950'
                    : 'hover:scale-125 hover:shadow-md hover:z-10',
            ]"
            :style="{
                backgroundColor:
                    resolvedAppearance === 'dark'
                        ? ACCENT_COLORS[color].dark
                        : ACCENT_COLORS[color].light,
                boxShadow: accentColor === color ? `0 0 20px -5px ${resolvedAppearance === 'dark' ? ACCENT_COLORS[color].dark : ACCENT_COLORS[color].light}` : 'none'
            }"
            :title="color.charAt(0).toUpperCase() + color.slice(1)"
        >
            <Check
                v-if="accentColor === color"
                :class="[
                    'h-4 w-4 transition-all duration-500 scale-110',
                    color === 'yellow' || (color === 'zinc' && resolvedAppearance === 'dark')
                        ? 'text-black'
                        : 'text-white',
                ]"
            />
            
            <!-- Tooltip / Label on hover (Expert touch) -->
            <span class="absolute -bottom-8 scale-0 rounded bg-foreground px-2 py-1 text-[10px] text-background transition-all group-hover:scale-100 font-bold uppercase tracking-wider">
                {{ $t(color) }}
            </span>
            
            <span class="sr-only">{{ color }}</span>
        </button>
    </div>
</template>
