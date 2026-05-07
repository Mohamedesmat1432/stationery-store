<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, PencilLine } from 'lucide-vue-next';
import CategoryForm from '@/components/Admin/Catalog/CategoryForm.vue';
import { Button } from '@/components/ui/button';
import { useCategories } from '@/composables/useCategories';
import * as categoriesRoutes from '@/routes/admin/categories/index';

type CategoryData = Modules.Catalog.Data.CategoryData;


const props = defineProps<{
    category: CategoryData;
    available_categories?: CategoryData[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Categories', href: categoriesRoutes.index().url },
            { title: 'Edit Category', href: '#' },
        ],
    },
});

const { form, submit } = useCategories(props.category);
const handleSubmit = () => submit();
</script>

<template>
    <Head :title="`Edit Category: ${category.name}`" />

    <div class="max-w-6xl mx-auto p-6 space-y-6">
        <Deferred data="available_categories">
            <template #fallback>
                <div class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-6">
                            <div class="h-[400px] bg-sidebar rounded-xl animate-pulse"></div>
                        </div>
                        <div class="space-y-6">
                            <div class="h-[200px] bg-sidebar rounded-xl animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </template>
            <CategoryForm 
                v-model:form="form"
                :category="category"
                :available_categories="available_categories"
                :is-edit="true"
                :category-name="category.name"
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
                    <PencilLine class="h-4 w-4" />
                </template>
            </CategoryForm>
        </Deferred>
    </div>
</template>
