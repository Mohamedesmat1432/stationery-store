<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { AlertTriangle, Info, RotateCcw } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import Spinner from '@/components/ui/spinner/Spinner.vue';

const page = usePage();

interface Props {
    open: boolean;
    title: string | { key: string; params?: any };
    description: string | { key: string; params?: any };
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'default' | 'destructive' | 'warning' | 'success';
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    variant: 'default',
    loading: false,
});

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();

const icon = computed(() => {
    switch (props.variant) {
        case 'destructive':
            return AlertTriangle;
        case 'warning':
            return AlertTriangle;
        case 'success':
            return RotateCcw;
        default:
            return Info;
    }
});

const iconClass = computed(() => {
    switch (props.variant) {
        case 'destructive':
            return 'text-destructive bg-destructive/10';
        case 'warning':
            return 'text-warning bg-warning/10';
        case 'success':
            return 'text-primary bg-primary/10';
        default:
            return 'text-primary bg-primary/10';
    }
});

const buttonVariant = computed(() => {
    if (props.variant === 'destructive') {
        return 'destructive';
    }

    return 'default';
});

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    emit('update:open', false);
    emit('cancel');
};

/**
 * Helper to translate string values within parameters
 */
const translateParams = (params: any) => {
    if (!params) {
        return {};
    }

    const translated = { ...params };
    Object.keys(translated).forEach((key) => {
        if (typeof translated[key] === 'string') {
            // We use the $t from the template scope which is available globally
            // or we can use useI18n() if we want to be more explicit.
        }
    });

    return translated;
};
</script>

<template>
    <Dialog
        :open="open"
        @update:open="(val) => emit('update:open', val)"
        :dir="page.props.locale === 'ar' ? 'rtl' : 'ltr'"
    >
        <DialogContent class="gap-0 overflow-hidden p-0 sm:max-w-[425px]">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div :class="['shrink-0 rounded-full p-2', iconClass]">
                        <component :is="icon" class="h-5 w-5" />
                    </div>
                    <div class="flex-1 pt-0.5">
                        <DialogHeader>
                            <DialogTitle
                                class="text-xl leading-none font-semibold tracking-tight"
                            >
                                <template v-if="typeof title === 'string'">
                                    {{ $t(title) }}
                                </template>
                                <template v-else>
                                    {{
                                        $t(
                                            title.key,
                                            Object.fromEntries(
                                                Object.entries(
                                                    title.params || {},
                                                ).map(([k, v]) => [
                                                    k,
                                                    typeof v === 'string'
                                                        ? $t(v)
                                                        : v,
                                                ]),
                                            ),
                                        )
                                    }}
                                </template>
                            </DialogTitle>
                            <DialogDescription
                                class="pt-2 text-sm leading-relaxed text-muted-foreground"
                            >
                                <template
                                    v-if="typeof description === 'string'"
                                >
                                    {{ $t(description) }}
                                </template>
                                <template v-else>
                                    {{
                                        $t(
                                            description.key,
                                            Object.fromEntries(
                                                Object.entries(
                                                    description.params || {},
                                                ).map(([k, v]) => [
                                                    k,
                                                    typeof v === 'string'
                                                        ? $t(v)
                                                        : v,
                                                ]),
                                            ),
                                        )
                                    }}
                                </template>
                            </DialogDescription>
                        </DialogHeader>
                    </div>
                </div>
            </div>

            <DialogFooter
                class="flex flex-col-reverse gap-2 bg-muted/50 p-4 sm:flex-row sm:gap-0"
            >
                <Button
                    variant="ghost"
                    @click="handleCancel"
                    :disabled="loading"
                    class="sm:me-auto"
                >
                    {{ $t(cancelLabel) }}
                </Button>
                <Button
                    :variant="buttonVariant"
                    @click="handleConfirm"
                    :disabled="loading"
                >
                    <Spinner v-if="loading" class="me-2" />
                    {{ $t(confirmLabel) }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
