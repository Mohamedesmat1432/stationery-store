<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    open: boolean;
    title: string;
    description?: string;
    importUrl: string;
}>();

const emit = defineEmits(['update:open']);
const page = usePage();

const importForm = useForm({
    file: null as File | null,
});

const submitImport = () => {
    importForm.post(props.importUrl, {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:open', false);
            importForm.reset();
        },
    });
};
</script>

<template>
    <Dialog
        :open="open"
        @update:open="emit('update:open', $event)"
        :dir="page.props.locale === 'ar' ? 'rtl' : 'ltr'"
    >
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader class="text-start">
                <DialogTitle>{{ $t(title) }}</DialogTitle>
                <DialogDescription v-if="description">
                    {{ $t(description) }}
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitImport">
                <div class="grid gap-4 py-4 text-start">
                    <div class="space-y-2">
                        <Label for="file">{{ $t('Select File') }}</Label>
                        <Input
                            id="file"
                            type="file"
                            accept=".xlsx,.csv"
                            @change="
                                (e: Event) =>
                                    (importForm.file =
                                        (e.target as HTMLInputElement)
                                            .files?.[0] || null)
                            "
                        />
                        <div
                            v-if="importForm.errors.file"
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ importForm.errors.file }}
                        </div>
                        <div
                            v-if="
                                Object.keys(importForm.errors).length > 0 &&
                                !importForm.errors.file
                            "
                            class="mt-1 text-sm text-destructive"
                        >
                            <ul>
                                <li
                                    v-for="(error, key) in importForm.errors"
                                    :key="key"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                        </div>

                        <!-- Progress Bar -->
                        <div v-if="importForm.progress" class="mt-4 space-y-1">
                            <div class="flex justify-between text-xs">
                                <span>{{ $t('Uploading...') }}</span>
                                <span>{{ importForm.progress.percentage }}%</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-secondary">
                                <div
                                    class="h-full bg-primary transition-all duration-300"
                                    :style="{ width: `${importForm.progress.percentage}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('update:open', false)"
                        :disabled="importForm.processing"
                        >{{ $t('Cancel') }}</Button
                    >
                    <Button
                        type="submit"
                        :disabled="importForm.processing || !importForm.file"
                    >
                        <span v-if="importForm.processing">{{
                            $t('Uploading...')
                        }}</span>
                        <span v-else>{{ $t('Import') }}</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
