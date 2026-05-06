<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
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
const page = usePage();
const { t } = useI18n();

const exportColumns = ref<string[]>(
    props.defaultColumns || props.columns.map((c) => c.id),
);
const exportFormat = ref('xlsx');
const errorMessage = ref<string | null>(null);
const isProcessing = ref(false);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            exportColumns.value =
                props.defaultColumns || props.columns.map((c) => c.id);
            errorMessage.value = null;
            isProcessing.value = false;
        }
    },
);

const isAllSelected = computed(() => {
    return exportColumns.value.length === props.columns.length;
});

const toggleAll = () => {
    if (isAllSelected.value) {
        exportColumns.value = [];
    } else {
        exportColumns.value = props.columns.map((c) => c.id);
    }
};

const submitExport = () => {
    if (exportColumns.value.length === 0) {
        errorMessage.value = 'Please select at least one column to export.';
        toast.error(t('Please select at least one column to export.'));

        return;
    }

    errorMessage.value = null;
    isProcessing.value = true;
    toast.success(t('Export started. Your download will begin shortly.'));

    // Get current query parameters (filters, search, etc.)
    const currentParams = new URLSearchParams(window.location.search);

    // Add export-specific parameters
    currentParams.set('format', exportFormat.value);

    // Clear any existing columns from URL and add selected ones
    currentParams.delete('columns[]');
    exportColumns.value.forEach((col) => {
        currentParams.append('columns[]', col);
    });

    const url = `${props.exportUrl}?${currentParams.toString()}`;
    window.location.href = url;

    // Reset processing state after a short delay since we can't track download completion
    setTimeout(() => {
        isProcessing.value = false;
        emit('update:open', false);
    }, 1500);
};
</script>

<template>
    <Dialog
        :open="open"
        @update:open="emit('update:open', $event)"
        :dir="page.props.locale === 'ar' ? 'rtl' : 'ltr'"
    >
        <DialogContent class="sm:max-w-106.25">
            <DialogHeader class="text-start">
                <DialogTitle>{{ $t(title) }}</DialogTitle>
                <DialogDescription>
                    {{ description ? $t(description) : '' }}
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b pb-2">
                        <Label
                            class="text-xs font-bold text-muted-foreground uppercase"
                            >{{ $t('Select Columns to Export') }}</Label
                        >
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-8 px-2 text-xs"
                            @click="toggleAll"
                        >
                            {{
                                isAllSelected
                                    ? $t('Deselect All')
                                    : $t('Select All')
                            }}
                        </Button>
                    </div>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                        <div
                            v-for="col in columns"
                            :key="col.id"
                            class="flex items-center gap-2"
                        >
                            <Checkbox
                                :id="col.id"
                                :model-value="exportColumns.includes(col.id)"
                                @update:model-value="
                                    (checked: boolean | 'indeterminate') => {
                                        if (checked === true)
                                            exportColumns.push(col.id);
                                        else
                                            exportColumns =
                                                exportColumns.filter(
                                                    (c) => c !== col.id,
                                                );
                                    }
                                "
                            />
                            <Label :for="col.id" class="cursor-pointer">{{
                                $t(col.label)
                            }}</Label>
                        </div>
                    </div>

                    <div
                        v-if="errorMessage"
                        class="animate-in text-sm font-medium text-destructive fade-in slide-in-from-top-1"
                    >
                        {{ $t(errorMessage) }}
                    </div>
                </div>

                <div class="mt-2 space-y-2 text-start">
                    <Label>{{ $t('Export Format') }}</Label>
                    <Select v-model="exportFormat">
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="xlsx">{{
                                $t('Excel (.xlsx)')
                            }}</SelectItem>
                            <SelectItem value="csv">{{
                                $t('CSV (.csv)')
                            }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="emit('update:open', false)" :disabled="isProcessing">{{
                    $t('Cancel')
                }}</Button>
                <Button @click="submitExport" :disabled="isProcessing">
                    <span v-if="isProcessing">{{ $t('Processing...') }}</span>
                    <span v-else>{{ $t('Export') }}</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
