<script setup lang="ts">
import { ref, computed, watch } from 'vue';
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
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
    open: boolean;
    title: string;
    description?: string;
    columns: { id: string; label: string }[];
    defaultColumns?: string[];
    exportUrl: string;
}>();

const emit = defineEmits(['update:open']);

const exportColumns = ref<string[]>(props.defaultColumns || props.columns.map(c => c.id));
const exportFormat = ref('xlsx');
const errorMessage = ref<string | null>(null);

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        exportColumns.value = props.defaultColumns || props.columns.map(c => c.id);
        errorMessage.value = null;
    }
});

const isAllSelected = computed(() => {
    return exportColumns.value.length === props.columns.length;
});

const isIndeterminate = computed(() => {
    return exportColumns.value.length > 0 && exportColumns.value.length < props.columns.length;
});

const toggleAll = (checked: boolean | 'indeterminate') => {
    if (checked === true || checked === 'indeterminate') {
        exportColumns.value = props.columns.map(c => c.id);
    } else {
        exportColumns.value = [];
    }
};

import { toast } from 'vue-sonner';

const submitExport = () => {
    if (exportColumns.value.length === 0) {
        errorMessage.value = 'Please select at least one column to export.';
        return;
    }

    errorMessage.value = null;
    toast.success('Export started. Your download will begin shortly.');
    
    // Get current query parameters (filters, search, etc.)
    const currentParams = new URLSearchParams(window.location.search);
    
    // Add export-specific parameters
    currentParams.set('format', exportFormat.value);
    
    // Clear any existing columns from URL and add selected ones
    currentParams.delete('columns[]');
    exportColumns.value.forEach(col => {
        currentParams.append('columns[]', col);
    });

    const url = `${props.exportUrl}?${currentParams.toString()}`;
    window.location.href = url;
    emit('update:open', false);
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
            <div class="grid gap-4 py-4">
                <div class="space-y-4">
                    <div class="flex items-center gap-2 pb-2 border-b">
                        <Checkbox 
                            id="select-all" 
                            :model-value="isIndeterminate ? 'indeterminate' : isAllSelected"
                            @update:model-value="toggleAll"
                        />
                        <Label for="select-all" class="font-bold cursor-pointer uppercase text-xs text-muted-foreground">{{ $t('Select All') }}</Label>
                    </div>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                        <div v-for="col in columns" :key="col.id" class="flex items-center gap-2">
                            <Checkbox 
                                :id="col.id" 
                                :model-value="exportColumns.includes(col.id)"
                                @update:model-value="(checked: boolean | 'indeterminate') => {
                                    if (checked === true) exportColumns.push(col.id);
                                    else exportColumns = exportColumns.filter(c => c !== col.id);
                                }"
                            />
                            <Label :for="col.id" class="cursor-pointer">{{ $t(col.label) }}</Label>
                        </div>
                    </div>

                    <div v-if="errorMessage" class="text-sm text-destructive font-medium animate-in fade-in slide-in-from-top-1">
                        {{ $t(errorMessage) }}
                    </div>
                </div>
                
                <div class="space-y-2 mt-2">
                    <Label>{{ $t('Export Format') }}</Label>
                    <Select v-model="exportFormat">
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="xlsx">{{ $t('Excel (.xlsx)') }}</SelectItem>
                            <SelectItem value="csv">{{ $t('CSV (.csv)') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="emit('update:open', false)">{{ $t('Cancel') }}</Button>
                <Button @click="submitExport">{{ $t('Export') }}</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
