<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, Save } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import BrandForm from '@/components/Admin/Catalog/BrandForm.vue';
import { useBrands } from '@/composables/useBrands';
import * as brandRoutes from '@/routes/admin/brands/index';

type BrandData = Modules.Catalog.Data.BrandData;

const props = defineProps<{
    brand: BrandData;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Brands', href: brandRoutes.index.url() },
            { title: 'Edit Brand', href: '#' },
        ],
    },
});

const { form, submit } = useBrands(props.brand);
const handleSubmit = () => submit();
</script>

<template>
    <Head :title="`Edit Brand: ${brand.name}`" />

    <div class="max-w-6xl mx-auto p-6 space-y-6">
        <div class="flex items-center justify-between">
            <Button variant="ghost" as-child type="button">
                <Link :href="brandRoutes.index.url()" class="flex items-center gap-2">
                    <ChevronLeft class="h-4 w-4" /> {{ $t('Back to List') }}
                </Link>
            </Button>
        </div>

        <BrandForm 
            :form="form"
            :brand="brand"
            :is-edit="true"
            :brand-name="brand.name"
            @submit="handleSubmit" 
        />
    </div>
</template>
