import { ref, computed, watch, toValue, type MaybeRefOrGetter } from 'vue';

export function useSelection<T extends { id: string | number }>(itemsSource: MaybeRefOrGetter<T[]>) {
    const selectedIds = ref<(string | number)[]>([]);

    const items = computed(() => toValue(itemsSource));

    // Reset selection when source items change (e.g., pagination, search)
    watch(items, () => selectedIds.value = [], { deep: false });

    const isAllSelected = computed(() => {
        const currentItems = items.value;
        return currentItems.length > 0 && currentItems.every(item => selectedIds.value.includes(item.id));
    });

    const isIndeterminate = computed(() => {
        const currentItems = items.value;
        const selectedCount = currentItems.filter(item => selectedIds.value.includes(item.id)).length;
        return selectedCount > 0 && selectedCount < currentItems.length;
    });

    const toggleAll = () => {
        if (isAllSelected.value) {
            // Remove current items from selection
            const currentIds = items.value.map(i => i.id);
            selectedIds.value = selectedIds.value.filter(id => !currentIds.includes(id));
        } else {
            // Add all current items to selection (preventing duplicates)
            const currentIds = items.value.map(i => i.id);
            selectedIds.value = Array.from(new Set([...selectedIds.value, ...currentIds]));
        }
    };

    const toggleItem = (id: string | number) => {
        const index = selectedIds.value.indexOf(id);
        
        if (index > -1) {
            selectedIds.value = selectedIds.value.filter(selectedId => selectedId !== id);
        } else {
            selectedIds.value = [...selectedIds.value, id];
        }
    };

    const clearSelection = () => {
        selectedIds.value = [];
    };

    return {
        selectedIds,
        isAllSelected,
        isIndeterminate,
        toggleAll,
        toggleItem,
        clearSelection,
    };
}
