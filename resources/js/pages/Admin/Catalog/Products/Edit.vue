<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, Save } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import ProductForm from '@/components/Admin/Catalog/ProductForm.vue';
import { useProducts } from '@/composables/useProducts';
import * as productRoutes from '@/routes/admin/products/index';

type ProductData = Modules.Catalog.Data.ProductData;
type CategoryData = Modules.Catalog.Data.CategoryData;

const props = defineProps<{
    product: ProductData;
    categories?: CategoryData[];
    brands?: { id: string, name: string }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Products', href: productRoutes.index.url() },
            { title: 'Edit Product', href: '#' },
        ],
    },
});

const { form, submit } = useProducts(props.product);
const handleSubmit = () => submit();
</script>

<template>
    <Head :title="`Edit Product: ${product.name}`" />

    <div class="max-w-6xl mx-auto p-6 space-y-6">
        <div class="flex items-center justify-between">
            <Button variant="ghost" as-child type="button">
                <Link :href="productRoutes.index.url()" class="flex items-center gap-2">
                    <ChevronLeft class="h-4 w-4" /> {{ $t('Back to List') }}
                </Link>
            </Button>
        </div>

        <ProductForm 
            :form="form"
            :product="product"
            :categories="categories"
            :brands="brands"
            :is-edit="true"
            :product-name="product.name"
            @submit="handleSubmit" 
        >
            <template #submit-icon>
                <Save class="h-5 w-5 mr-2" />
            </template>
        </ProductForm>
    </div>
</template>
