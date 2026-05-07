<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, FolderPlus } from 'lucide-vue-next';
import CategoryForm from '@/components/Admin/Catalog/CategoryForm.vue';
import { Button } from '@/components/ui/button';
import { useCategories } from '@/composables/useCategories';
import * as categoriesRoutes from '@/routes/admin/categories/index';

type CategoryData = Modules.Catalog.Data.CategoryData;

defineProps<{
    available_categories?: CategoryData[];
}>();


defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Categories', href: categoriesRoutes.index().url },
            { title: 'Create Category', href: categoriesRoutes.create().url },
        ],
    },
});

const { form, submit } = useCategories();
const handleSubmit = () => submit();
</script>

<template>
    <Head title="Create Category" />

    <div class="max-w-6xl mx-auto p-6 space-y-6">
        <CategoryForm 
            v-model:form="form"
            :available_categories="available_categories"
            @submit="handleSubmit" 
        >
            <template #header-actions>
                <Button variant="outline" as-child type="button">
                    <Link :href="categoriesRoutes.index().url" class="flex items-center gap-2">
                        <ChevronLeft class="h-4 w-4" /> {{ $t('Back') }}
                    </Link>
                </Button>
            </template>
            <template #submit-icon>
                <FolderPlus class="h-4 w-4" />
            </template>
        </CategoryForm>
    </div>
</template>
