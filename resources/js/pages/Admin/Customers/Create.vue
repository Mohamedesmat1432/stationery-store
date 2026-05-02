<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { UserCircle, Save, ArrowLeft } from 'lucide-vue-next';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useCustomers } from '@/composables/useCustomers';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Customers', href: '/admin/customers' },
            { title: 'Create Customer', href: '/admin/customers/create' },
        ],
    },
});

const props = defineProps<{
    available_groups: any[];
    available_users: any[];
}>();

const { form, submit } = useCustomers();
const handleSubmit = () => submit();
</script>

<template>
    <Head :title="$t('Create Customer')" />

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
                            <UserCircle class="h-6 w-6" />
                            {{ $t('Create Customer') }}
                        </CardTitle>
                        <CardDescription>{{
                            $t('Add a new customer to your CRM.')
                        }}</CardDescription>
                    </div>
                    <Button variant="outline" as-child type="button">
                        <Link
                            href="/admin/customers"
                            class="flex items-center gap-2"
                        >
                            <ArrowLeft class="h-4 w-4" /> {{ $t('Back') }}
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="user_id"
                                >{{ $t('User Account') }}
                                <span class="text-destructive">*</span></Label
                            >
                            <Select v-model="form.user_id" required>
                                <SelectTrigger>
                                    <SelectValue
                                        :placeholder="$t('Select User')"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="user in available_users"
                                        :key="user.id"
                                        :value="user.id"
                                    >
                                        {{ user.name }} ({{ user.email }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.user_id" />
                        </div>
                        <div class="space-y-2">
                            <Label for="phone">{{ $t('Phone Number') }}</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                placeholder="e.g. +1234567890"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.phone" />
                        </div>
                        <div class="space-y-2">
                            <Label for="customer_group_id">{{
                                $t('Customer Group')
                            }}</Label>
                            <Select v-model="form.customer_group_id">
                                <SelectTrigger
                                    ><SelectValue
                                        :placeholder="$t('Select Group')"
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="group in available_groups"
                                        :key="group.id"
                                        :value="group.id"
                                        >{{ group.name }}</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError
                                :message="form.errors.customer_group_id"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="company_name">{{
                                $t('Company Name')
                            }}</Label>
                            <Input
                                id="company_name"
                                v-model="form.company_name"
                                placeholder="e.g. Acme Corp"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.company_name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="tax_number">{{
                                $t('Tax Number')
                            }}</Label>
                            <Input
                                id="tax_number"
                                v-model="form.tax_number"
                                placeholder="e.g. TAX-123"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.tax_number" />
                        </div>
                        <div class="space-y-2">
                            <Label for="birth_date">{{
                                $t('Birth Date')
                            }}</Label>
                            <Input
                                id="birth_date"
                                type="date"
                                v-model="form.birth_date"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.birth_date" />
                        </div>
                        <div class="space-y-2">
                            <Label for="gender">{{ $t('Gender') }}</Label>
                            <Select v-model="form.gender">
                                <SelectTrigger
                                    ><SelectValue
                                        :placeholder="$t('Select Gender')"
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="male">{{
                                        $t('Male')
                                    }}</SelectItem>
                                    <SelectItem value="female">{{
                                        $t('Female')
                                    }}</SelectItem>
                                    <SelectItem value="other">{{
                                        $t('Other')
                                    }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.gender" />
                        </div>
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
                        <Save class="h-4 w-4" /> {{ $t('Save Customer') }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
