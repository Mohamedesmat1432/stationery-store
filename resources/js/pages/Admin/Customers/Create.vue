<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { UserCircle, Save, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
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

const handleSubmit = () => {
    submit();
};
</script>

<template>
    <Head title="Create Customer" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 max-w-3xl mx-auto w-full">
        <Card>
            <CardHeader>
                <CardTitle class="text-xl font-bold flex items-center gap-2">
                    <UserCircle class="w-6 h-6" /> Create Customer
                </CardTitle>
                <CardDescription>Add a new customer to your CRM.</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <Label for="user_id">User Account</Label>
                            <Select v-model="form.user_id" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select User" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="user in available_users" :key="user.id" :value="user.id">
                                        {{ user.name }} ({{ user.email }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.user_id" class="text-sm text-destructive">{{ form.errors.user_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">Phone Number</Label>
                            <Input id="phone" v-model="form.phone" placeholder="e.g. +1234567890" />
                            <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="customer_group_id">Customer Group</Label>
                            <Select v-model="form.customer_group_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select Group" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="group in available_groups" :key="group.id" :value="group.id">
                                        {{ group.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.customer_group_id" class="text-sm text-destructive">{{ form.errors.customer_group_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="company_name">Company Name</Label>
                            <Input id="company_name" v-model="form.company_name" placeholder="e.g. Acme Corp" />
                            <p v-if="form.errors.company_name" class="text-sm text-destructive">{{ form.errors.company_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="tax_number">Tax Number</Label>
                            <Input id="tax_number" v-model="form.tax_number" placeholder="e.g. TAX-123" />
                            <p v-if="form.errors.tax_number" class="text-sm text-destructive">{{ form.errors.tax_number }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="birth_date">Birth Date</Label>
                            <Input id="birth_date" type="date" v-model="form.birth_date" />
                            <p v-if="form.errors.birth_date" class="text-sm text-destructive">{{ form.errors.birth_date }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="gender">Gender</Label>
                            <Select v-model="form.gender">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select Gender" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="male">Male</SelectItem>
                                    <SelectItem value="female">Female</SelectItem>
                                    <SelectItem value="other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.gender" class="text-sm text-destructive">{{ form.errors.gender }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-sidebar-border">
                        <Button type="button" variant="ghost" as-child>
                            <Link href="/admin/customers">
                                <X class="w-4 h-4 mr-2" /> Cancel
                            </Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Save class="w-4 h-4 mr-2" /> Save Customer
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
