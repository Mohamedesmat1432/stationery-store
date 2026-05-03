<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import AccentColorPicker from '@/components/AccentColorPicker.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { LayoutGrid, CheckCircle2, Monitor, Moon, Sun, Palette } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="$t('Appearance settings')" />

    <h1 class="sr-only">{{ $t('Appearance settings') }}</h1>

    <div class="flex flex-col gap-8">
        <Heading
            title="Appearance settings"
            description="Customize how the application looks on your device"
        />

        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:items-start">
            <!-- Settings Side -->
            <div class="space-y-12">
                <!-- Theme Mode -->
                <section class="space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-xl bg-primary/8 text-primary shadow-sm ring-1 ring-primary/10">
                            <Monitor class="size-5" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold leading-none">{{ $t('Interface Theme') }}</h3>
                            <p class="text-xs text-muted-foreground">{{ $t('Choose how the application feels on your eyes.') }}</p>
                        </div>
                    </div>
                    <AppearanceTabs />
                </section>

                <!-- Accent Color -->
                <section class="space-y-6 pt-6 border-t border-dashed">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-xl bg-primary/8 text-primary shadow-sm ring-1 ring-primary/10">
                            <Palette class="size-5" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold leading-none">{{ $t('Accent Color') }}</h3>
                            <p class="text-xs text-muted-foreground">{{ $t('Personalize the primary color of the interface.') }}</p>
                        </div>
                    </div>
                    <AccentColorPicker />
                </section>
            </div>

            <!-- Preview Side -->
            <div class="sticky top-8 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <h3 class="text-sm font-semibold leading-none">{{ $t('Live Preview') }}</h3>
                        <p class="text-xs text-muted-foreground">{{ $t('Real-time visualization of your preferences.') }}</p>
                    </div>
                </div>

                <Card class="overflow-hidden border-2 border-primary/10 shadow-xl transition-all duration-700">
                    <CardContent class="p-0">
                        <!-- Mock Toolbar -->
                        <div class="flex h-10 items-center gap-2 border-b bg-muted/40 px-4">
                            <div class="flex gap-1.5">
                                <div class="size-2.5 rounded-full bg-red-400/80" />
                                <div class="size-2.5 rounded-full bg-amber-400/80" />
                                <div class="size-2.5 rounded-full bg-emerald-400/80" />
                            </div>
                            <div class="ms-4 h-3 w-32 rounded-full bg-muted animate-pulse" />
                        </div>
                        
                        <!-- Mock Layout -->
                        <div class="grid grid-cols-[80px_1fr] h-[280px]">
                            <!-- Sidebar Mock -->
                            <div class="flex flex-col border-e bg-sidebar p-2 gap-3">
                                <div class="h-8 rounded-lg bg-primary flex items-center justify-center shadow-lg shadow-primary/20">
                                    <LayoutGrid class="size-4 text-primary-foreground" />
                                </div>
                                <div class="space-y-2 px-1">
                                    <div class="h-1.5 w-full rounded bg-muted/40" />
                                    <div class="h-1.5 w-4/5 rounded bg-muted/20" />
                                    <div class="h-1.5 w-full rounded bg-muted/20" />
                                </div>
                                <div class="mt-auto h-8 rounded-full bg-muted/30 border border-dashed border-muted" />
                            </div>

                            <!-- Content Mock -->
                            <div class="p-8 space-y-8 bg-card transition-colors duration-500">
                                <div class="space-y-3">
                                    <div class="h-5 w-2/5 rounded bg-muted animate-pulse" />
                                    <div class="space-y-1.5">
                                        <div class="h-2 w-full rounded bg-muted/40 animate-pulse" />
                                        <div class="h-2 w-3/4 rounded bg-muted/40 animate-pulse" />
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <Button size="sm" class="h-9 px-5 rounded-full shadow-lg shadow-primary/20">
                                        <CheckCircle2 class="me-2 size-4" />
                                        {{ $t('Apply') }}
                                    </Button>
                                    <div class="h-9 w-24 rounded-full border bg-muted/10" />
                                </div>

                                <div class="grid grid-cols-2 gap-4 pt-4">
                                    <div class="h-16 rounded-2xl border-2 border-primary/10 bg-primary/5 flex items-center justify-center transition-colors">
                                        <div class="size-2 rounded-full bg-primary" />
                                        <div class="ms-3 h-1.5 w-8 rounded-full bg-primary/20" />
                                    </div>
                                    <div class="h-16 rounded-2xl border border-muted/50 bg-muted/5 flex items-center justify-center opacity-40">
                                        <div class="size-2 rounded-full bg-muted" />
                                        <div class="ms-3 h-1.5 w-8 rounded-full bg-muted/20" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
