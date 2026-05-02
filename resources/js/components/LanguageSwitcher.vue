<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Check, Languages, Loader2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import locale from '@/routes/locale';

const page = usePage();
const currentLocale = computed(() => page.props.locale as string);
const isProcessing = ref(false);

const languages = [
    { name: 'English', code: 'en' },
    { name: 'Arabic', code: 'ar' },
];

const switchLanguage = (code: string) => {
    if (code === currentLocale.value || isProcessing.value) {
        return;
    }

    isProcessing.value = true;

    router.post(
        locale.update.url(),
        { locale: code },
        {
            preserveScroll: true,
            preserveState: false,
            onFinish: () => {
                isProcessing.value = false;
            },
        },
    );
};
</script>

<template>
    <DropdownMenu :dir="currentLocale === 'ar' ? 'rtl' : 'ltr'">
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="sm"
                class="flex h-9 items-center gap-2 px-2 hover:bg-accent focus-visible:ring-1 focus-visible:ring-ring"
                :disabled="isProcessing"
            >
                <Loader2 v-if="isProcessing" class="h-4 w-4 animate-spin text-muted-foreground" />
                <Languages v-else class="h-4 w-4 opacity-70" />
                <span class="text-xs font-bold uppercase opacity-80">{{ currentLocale }}</span>
                <span class="sr-only">{{ $t('Language') }}</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            align="end"
            class="min-w-[120px]"
        >
            <DropdownMenuItem
                v-for="lang in languages"
                :key="lang.code"
                @click="switchLanguage(lang.code)"
                class="flex cursor-pointer items-center justify-between py-2 transition-colors hover:bg-accent"
                :class="{ 'bg-accent/50 font-semibold': currentLocale === lang.code }"
            >
                <span class="flex-1">{{ $t(lang.name) }}</span>
                <Check
                    v-if="currentLocale === lang.code"
                    class="ms-2 h-4 w-4 text-primary"
                />
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
