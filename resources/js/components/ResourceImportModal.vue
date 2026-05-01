<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    open: boolean;
    title: string;
    description?: string;
    importUrl: string;
}>();

const emit = defineEmits(['update:open']);

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
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ $t(title) }}</DialogTitle>
                <DialogDescription v-if="description">
                    {{ $t(description) }}
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitImport">
                <div class="grid gap-4 py-4">
                    <div class="space-y-2">
                        <Label for="file">{{ $t('Select File') }}</Label>
                        <Input 
                            id="file" 
                            type="file" 
                            accept=".xlsx,.csv" 
                            @change="(e: Event) => importForm.file = (e.target as HTMLInputElement).files?.[0] || null"
                        />
                        <div v-if="importForm.errors.file" class="text-sm text-destructive mt-1">{{ importForm.errors.file }}</div>
                        <div v-if="Object.keys(importForm.errors).length > 0 && !importForm.errors.file" class="text-sm text-destructive mt-1">
                            <ul>
                                <li v-for="(error, key) in importForm.errors" :key="key">{{ error }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('update:open', false)" :disabled="importForm.processing">{{ $t('Cancel') }}</Button>
                    <Button type="submit" :disabled="importForm.processing || !importForm.file">
                        <span v-if="importForm.processing">{{ $t('Uploading...') }}</span>
                        <span v-else>{{ $t('Import') }}</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
