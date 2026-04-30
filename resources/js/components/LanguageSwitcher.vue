<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Languages } from 'lucide-vue-next';
import { computed } from 'vue';
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

const languages = [
    { name: 'English', code: 'en' },
    { name: 'Arabic', code: 'ar' },
];

const switchLanguage = (code: string) => {
    if (code === currentLocale.value) return;

    router.post(locale.update.url(), { locale: code }, {
        preserveScroll: true,
        preserveState: false, // Force re-render of components that use page.props
    });
};
</script>

<template>
    <DropdownMenu :dir="page.props.locale === 'ar' ? 'rtl' : 'ltr'">
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="h-9 w-9">
                <Languages class="h-5 w-5 opacity-80 group-hover:opacity-100" />
                <span class="sr-only">{{ $t('Language') }}</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent :align="page.props.locale === 'ar' ? 'start' : 'end'">
            <DropdownMenuItem
                v-for="lang in languages"
                :key="lang.code"
                @click="switchLanguage(lang.code)"
                :class="{ 'bg-accent': currentLocale === lang.code }"
                class="cursor-pointer font-medium"
            >
                {{ $t(lang.name) }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
