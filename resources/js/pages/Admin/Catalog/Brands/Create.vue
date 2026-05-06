<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, Tag } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import BrandForm from '@/components/Admin/Catalog/BrandForm.vue';
import { useBrands } from '@/composables/useBrands';
import * as brandRoutes from '@/routes/admin/brands/index';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Brands', href: brandRoutes.index.url() },
            { title: 'Create Brand', href: brandRoutes.create.url() },
        ],
    },
});

const { form, submit } = useBrands();
const handleSubmit = () => submit();
</script>

<template>
    <Head title="Create Brand" />

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
            @submit="handleSubmit" 
        />
    </div>
</template>
