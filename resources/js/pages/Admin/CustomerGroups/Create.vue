<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Users, Save, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
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

const handleSubmit = () => {
    submit();
};
</script>

<template>
    <Head title="Create Customer Group" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 max-w-2xl mx-auto w-full">
        <Card>
            <CardHeader>
                <CardTitle class="text-xl font-bold flex items-center gap-2">
                    <Users class="w-6 h-6" /> Create Customer Group
                </CardTitle>
                <CardDescription>Define a new segment for your customers.</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="name">Group Name</Label>
                        <Input 
                            id="name" 
                            v-model="form.name" 
                            @input="updateSlug"
                            placeholder="e.g. VIP Customers" 
                            required 
                        />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="slug">Slug</Label>
                        <Input 
                            id="slug" 
                            v-model="form.slug" 
                            placeholder="e.g. vip-customers" 
                            required 
                        />
                        <p v-if="form.errors.slug" class="text-sm text-destructive">{{ form.errors.slug }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="discount">Discount Percentage (%)</Label>
                        <Input 
                            id="discount" 
                            type="number"
                            step="0.01"
                            v-model="form.discount_percentage" 
                            required 
                        />
                        <p v-if="form.errors.discount_percentage" class="text-sm text-destructive">{{ form.errors.discount_percentage }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea 
                            id="description" 
                            v-model="form.description" 
                            placeholder="Describe this group..." 
                        />
                        <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Checkbox 
                            id="is_active" 
                            :model-value="form.is_active"
                            @update:model-value="(val) => form.is_active = !!val"
                        />
                        <Label for="is_active">Active</Label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-sidebar-border">
                        <Button type="button" variant="ghost" as-child>
                            <Link href="/admin/customer-groups">
                                <X class="w-4 h-4 mr-2" /> Cancel
                            </Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Save class="w-4 h-4 mr-2" /> Save Group
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
