import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Options {
    baseUrl: string;
    only?: string[];
    onFilter?: (filters: any) => void;
}

export function useResourceFilters(initialFilters: any, options: Options) {
    const filtersRef = ref(initialFilters);
    const searchQuery = ref(initialFilters?.search || '');
    const showTrashed = ref(initialFilters?.trash === 'only');

    const extraFilters = ref<Record<string, any>>({});

    const syncFilters = (source: any) => {
        searchQuery.value = source?.search || '';
        showTrashed.value = source?.trash === 'only';

        const newExtra: Record<string, any> = {};
        Object.keys(source || {}).forEach(key => {
            if (key !== 'search' && key !== 'trash') {
                newExtra[key] = source[key];
            }
        });
        extraFilters.value = newExtra;
    };

    syncFilters(initialFilters);

    const applyFilters = () => {
        const filterData: Record<string, any> = {
            search: searchQuery.value || undefined,
            trash: showTrashed.value ? 'only' : undefined,
            ...extraFilters.value
        };

        const cleanFilters = Object.fromEntries(
            Object.entries(filterData).filter(([, v]) => v !== undefined && v !== null && v !== '' && v !== 'all')
        );

        router.get(options.baseUrl, {
            filter: cleanFilters,
            page: 1,
        }, {
            preserveState: true,
            replace: true,
            only: options.only,
        });

        if (options.onFilter) {
            options.onFilter(cleanFilters);
        }
    };

    watch(filtersRef, (newFilters) => {
        syncFilters(newFilters);
    }, { deep: true });

    return {
        searchQuery,
        showTrashed,
        extraFilters,
        applyFilters,
    };
}
