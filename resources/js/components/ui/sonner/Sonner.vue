<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Toaster as SonnerPrimitive } from 'vue-sonner';
import 'vue-sonner/style.css';

const { appearance } = useAppearance();
const page = usePage();
const dir = computed(() => (page.props.locale as string) === 'ar' ? 'rtl' : 'ltr');
const position = computed(() => dir.value === 'rtl' ? 'top-left' : 'top-right');
</script>

<template>
    <SonnerPrimitive
        :theme="appearance"
        class="toaster group"
        :position="position"
        :dir="dir"
        :rich-colors="true"
        :close-button="true"
        :duration="4000"
        :style="{
            '--normal-bg': 'var(--popover)',
            '--normal-text': 'var(--popover-foreground)',
            '--normal-border': 'var(--border)',
        }"
    />
</template>
