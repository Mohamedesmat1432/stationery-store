<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Save, ArrowLeft } from 'lucide-vue-next';
import { index } from '@/actions/Modules/CRM/Http/Controllers/CustomerGroupController';
import { Button } from '@/components/ui/button';
import CustomerGroupForm from '@/components/forms/CustomerGroupForm.vue';
import { useCustomerGroups } from '@/composables/useCustomerGroups';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Customer Groups', href: index.url() },
            { title: 'Edit Group', href: '#' },
        ],
    },
});

const props = defineProps<{
    group: Modules.CRM.Data.CustomerGroupData;
}>();

const { form, submit } = useCustomerGroups(props.group);
const handleSubmit = () => submit(props.group.id!);
</script>

<template>
    <Head :title="$t('Edit Customer Group')" />

    <div class="mx-auto flex h-full w-full max-w-4xl flex-1 flex-col gap-4 overflow-x-auto p-4">
        <CustomerGroupForm is-edit :form="form" @submit="handleSubmit">
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
        </CustomerGroupForm>
    </div>
</template>

