import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface FilterState {
    search?: string;
    trash?: string;
    [key: string]: any;
}

interface Options {
    baseUrl: string;
    onFilter?: (filters: any) => void;
}

export function useResourceFilters(initialFilters: any, options: Options) {
    const searchQuery = ref(initialFilters?.search || '');
    const showTrashed = ref(initialFilters?.trash === 'only');
    
    // Additional dynamic filters
    const extraFilters = ref<Record<string, any>>({});
    
    // Initialize extra filters from initial state, excluding search and trash
    Object.keys(initialFilters || {}).forEach(key => {
        if (key !== 'search' && key !== 'trash') {
            extraFilters.value[key] = initialFilters[key];
        }
    });

    const applyFilters = () => {
        const filterData: Record<string, any> = {
            search: searchQuery.value || undefined,
            trash: showTrashed.value ? 'only' : undefined,
            ...extraFilters.value
        };

        // Clean undefined values
        const cleanFilters = Object.fromEntries(
            Object.entries(filterData).filter(([_, v]) => v !== undefined && v !== 'all')
        );

        router.get(options.baseUrl, {
            filter: cleanFilters,
        }, {
            preserveState: true,
            replace: true,
        });

        if (options.onFilter) {
            options.onFilter(cleanFilters);
        }
    };

    watch(() => initialFilters, (newFilters) => {
        searchQuery.value = newFilters?.search || '';
        showTrashed.value = newFilters?.trash === 'only';
        
        Object.keys(newFilters || {}).forEach(key => {
            if (key !== 'search' && key !== 'trash') {
                extraFilters.value[key] = newFilters[key];
            }
        });
    }, { deep: true });

    return {
        searchQuery,
        showTrashed,
        extraFilters,
        applyFilters,
    };
}
