<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, PackagePlus } from 'lucide-vue-next';
import ProductForm from '@/components/Admin/Catalog/ProductForm.vue';
import { Button } from '@/components/ui/button';
import { useProducts } from '@/composables/useProducts';
import * as productRoutes from '@/routes/admin/products/index';

type CategoryData = Modules.Catalog.Data.CategoryData;

defineProps<{
    categories?: CategoryData[];
    brands?: { id: string, name: string }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Products', href: productRoutes.index.url() },
            { title: 'Create Product', href: productRoutes.create.url() },
        ],
    },
});

const { form, submit } = useProducts();
const handleSubmit = () => submit();
</script>

<template>
    <Head title="Create Product" />

    <div class="max-w-6xl mx-auto p-6 space-y-6">
        <div class="flex items-center justify-between">
            <Button variant="ghost" as-child type="button">
                <Link :href="productRoutes.index.url()" class="flex items-center gap-2">
                    <ChevronLeft class="h-4 w-4" /> {{ $t('Back to List') }}
                </Link>
            </Button>
        </div>

        <Deferred :data="['categories', 'brands']">
            <template #fallback>
                <div class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-6">
                            <div class="h-[600px] bg-sidebar rounded-xl animate-pulse"></div>
                        </div>
                        <div class="space-y-6">
                            <div class="h-[300px] bg-sidebar rounded-xl animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </template>
            <ProductForm 
                v-model:form="form"
                :categories="categories"
                :brands="brands"
                @submit="handleSubmit" 
            >
                <template #submit-icon>
                    <PackagePlus class="h-5 w-5 mr-2" />
                </template>
            </ProductForm>
        </Deferred>
    </div>
</template>
