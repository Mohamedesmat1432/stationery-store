<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Users, Save, ArrowLeft } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
    CardFooter,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useCustomerGroups } from '@/composables/useCustomerGroups';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Customer Groups', href: '/admin/customer-groups' },
            { title: 'Create Group', href: '/admin/customer-groups/create' },
        ],
    },
});

const { form, updateSlug, submit } = useCustomerGroups();
const handleSubmit = () => submit();
</script>

<template>
    <Head :title="$t('Create Customer Group')" />

    <div
        class="mx-auto flex h-full w-full max-w-4xl flex-1 flex-col gap-4 overflow-x-auto p-4"
    >
        <form @submit.prevent="handleSubmit">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle
                            class="flex items-center gap-2 text-xl font-bold"
                        >
                            <Users class="h-6 w-6" />
                            {{ $t('Create Customer Group') }}
                        </CardTitle>
                        <CardDescription>{{
                            $t('Define a new segment for your customers.')
                        }}</CardDescription>
                    </div>
                    <Button variant="outline" as-child type="button">
                        <Link
                            href="/admin/customer-groups"
                            class="flex items-center gap-2"
                        >
                            <ArrowLeft class="h-4 w-4" /> {{ $t('Back') }}
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name"
                                >{{ $t('Group Name') }}
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                id="name"
                                v-model="form.name"
                                @input="updateSlug"
                                :placeholder="$t('e.g. VIP Customers')"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="slug"
                                >{{ $t('Slug') }}
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                id="slug"
                                v-model="form.slug"
                                :placeholder="$t('e.g. vip-customers')"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.slug" />
                        </div>
                    </div>

                    <div class="max-w-md space-y-2">
                        <Label for="discount"
                            >{{ $t('Discount Percentage (%)') }}
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="discount"
                            type="number"
                            step="0.01"
                            v-model="form.discount_percentage"
                            :disabled="form.processing"
                        />
                        <InputError
                            :message="form.errors.discount_percentage"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="description">{{ $t('Description') }}</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            :placeholder="$t('Describe this group...')"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="flex items-center space-x-2">
                        <Checkbox
                            id="is_active"
                            :model-value="form.is_active"
                            @update:model-value="
                                (val) => (form.is_active = !!val)
                            "
                        />
                        <Label for="is_active">{{ $t('Active') }}</Label>
                    </div>
                </CardContent>
                <CardFooter
                    class="flex justify-end border-t border-sidebar-border pt-6"
                >
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2"
                    >
                        <Save class="h-4 w-4" /> {{ $t('Save Group') }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
