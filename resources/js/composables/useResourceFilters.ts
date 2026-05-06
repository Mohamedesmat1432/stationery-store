import { router } from '@inertiajs/vue3';
import { ref, watch, toValue } from 'vue';
import type { MaybeRefOrGetter } from 'vue';

interface Options {
    baseUrl: string;
    only?: string[];
    onFilter?: (filters: any) => void;
}

export function useResourceFilters(initialFiltersSource: MaybeRefOrGetter<any>, options: Options) {
    const searchQuery = ref('');
    const showTrashed = ref(false);
    const extraFilters = ref<Record<string, any>>({});

    const syncFilters = (source: any) => {
        // Laravel returns [] for empty only(['filter']) which breaks object access
        const normalizedSource = (source && !Array.isArray(source)) ? source : {};
        
        searchQuery.value = normalizedSource?.search || '';
        showTrashed.value = normalizedSource?.trash === 'only';

        const newExtra: Record<string, any> = {};
        Object.keys(normalizedSource).forEach((key) => {
            if (key !== 'search' && key !== 'trash') {
                newExtra[key] = normalizedSource[key];
            }
        });
        extraFilters.value = newExtra;
    };

    // Initial sync
    syncFilters(toValue(initialFiltersSource));

    // Watch for external changes (e.g. browser back button, direct prop updates)
    watch(() => toValue(initialFiltersSource), (newFilters) => {
        syncFilters(newFilters);
    }, { deep: true });

    const applyFilters = () => {
        const filterData: Record<string, any> = {
            search: searchQuery.value || undefined,
            trash: showTrashed.value ? 'only' : undefined,
            ...extraFilters.value,
        };

        const cleanFilters = Object.fromEntries(
            Object.entries(filterData).filter(
                ([, v]) => v !== undefined && v !== null && v !== '' && v !== 'all',
            ),
        );

        const routerOptions: any = {
            preserveState: true,
            replace: true,
        };

        if (options.only && Array.isArray(options.only) && options.only.length > 0) {
            routerOptions.only = options.only;
        }

        router.get(
            options.baseUrl || window.location.pathname,
            {
                filter: cleanFilters,
                page: 1,
            },
            routerOptions,
        );

        if (options.onFilter) {
            options.onFilter(cleanFilters);
        }
    };

    const clearFilters = () => {
        searchQuery.value = '';
        showTrashed.value = false;
        extraFilters.value = {};
        applyFilters();
    };

    return {
        searchQuery,
        showTrashed,
        extraFilters,
        applyFilters,
        clearFilters,
    };
}
