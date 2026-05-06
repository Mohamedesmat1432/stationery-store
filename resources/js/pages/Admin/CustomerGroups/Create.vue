<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Save, ArrowLeft } from 'lucide-vue-next';
import * as customerGroupsRoutes from '@/routes/admin/customer-groups/index';
import CustomerGroupForm from '@/components/forms/CustomerGroupForm.vue';
import { Button } from '@/components/ui/button';
import { useCustomerGroups } from '@/composables/useCustomerGroups';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Customer Groups', href: customerGroupsRoutes.index.url() },
            { title: 'Create Group', href: customerGroupsRoutes.create.url() },
        ],
    },
});

const { form, submit } = useCustomerGroups();
const handleSubmit = () => submit();

const onUpdateIsActive = (val: boolean) => {
    form.is_active = val;
};
</script>

<template>
    <Head :title="$t('Create Customer Group')" />

    <div class="mx-auto flex h-full w-full max-w-4xl flex-1 flex-col gap-4 overflow-x-auto p-4">
        <CustomerGroupForm :form="form" @submit="handleSubmit" @update:is_active="onUpdateIsActive">
            <template #header-actions>
                <Button variant="outline" as-child type="button">
                    <Link :href="customerGroupsRoutes.index.url()" class="flex items-center gap-2">
                        <ArrowLeft class="h-4 w-4" /> {{ $t('Back') }}
                    </Link>
                </Button>
            </template>
            <template #submit-icon>
                <Save class="h-4 w-4" />
            </template>
        </CustomerGroupForm>
    </div>
</template>

