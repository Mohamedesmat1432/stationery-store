<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Save, ArrowLeft } from 'lucide-vue-next';
import { index } from '@/actions/Modules/CRM/Http/Controllers/CustomerController';
import CustomerForm from '@/components/forms/CustomerForm.vue';
import { Button } from '@/components/ui/button';
import { useCustomers } from '@/composables/useCustomers';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Customers', href: index.url() },
            { title: 'Edit Customer', href: '#' },
        ],
    },
});

const props = withDefaults(
    defineProps<{
        customer: Modules.CRM.Data.CustomerData;
        available_groups?: Modules.CRM.Data.CustomerGroupData[];
        available_users?: Modules.Identity.Data.UserData[];
    }>(),
    {
        available_groups: () => [],
        available_users: () => [],
    },
);

const { form, submit } = useCustomers(props.customer);
const handleSubmit = () => submit(props.customer.id!);
</script>

<template>
    <Head :title="$t('Edit Customer')" />

    <div class="mx-auto flex h-full w-full max-w-4xl flex-1 flex-col gap-4 overflow-x-auto p-4">
        <CustomerForm
            is-edit
            :form="form"
            :available_groups="available_groups"
            :available_users="available_users"
            @submit="handleSubmit"
        >
            <template #header-actions>
                <Button variant="outline" as-child type="button">
                    <Link :href="index.url()" class="flex items-center gap-2">
                        <ArrowLeft class="h-4 w-4" /> {{ $t('Back') }}
                    </Link>
                </Button>
            </template>
            <template #submit-icon>
                <Save class="h-4 w-4" />
            </template>
        </CustomerForm>
    </div>
</template>
