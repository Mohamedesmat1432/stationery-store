<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import Spinner from '@/components/ui/spinner/Spinner.vue';
import { AlertTriangle, Info, RotateCcw } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    open: boolean;
    title: string;
    description: string;
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
    if (props.variant === 'destructive') return 'destructive';
    return 'default';
});

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    emit('update:open', false);
    emit('cancel');
};
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-[425px] overflow-hidden p-0 gap-0">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div :class="['p-2 rounded-full shrink-0', iconClass]">
                        <component :is="icon" class="w-5 h-5" />
                    </div>
                    <div class="flex-1 pt-0.5">
                        <DialogHeader>
                            <DialogTitle class="text-xl font-semibold leading-none tracking-tight">
                                {{ title }}
                            </DialogTitle>
                            <DialogDescription class="pt-2 text-sm text-muted-foreground leading-relaxed">
                                {{ description }}
                            </DialogDescription>
                        </DialogHeader>
                    </div>
                </div>
            </div>
            
            <DialogFooter class="bg-muted/50 p-4 flex flex-col-reverse sm:flex-row gap-2 sm:gap-0">
                <Button variant="ghost" @click="handleCancel" :disabled="loading" class="sm:mr-auto">
                    {{ cancelLabel }}
                </Button>
                <Button :variant="buttonVariant" @click="handleConfirm" :disabled="loading">
                    <Spinner v-if="loading" class="mr-2" />
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
