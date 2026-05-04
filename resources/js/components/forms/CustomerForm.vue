<script setup lang="ts">
import { Deferred } from '@inertiajs/vue3';
import { UserCircle } from 'lucide-vue-next';
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
import { Skeleton } from '@/components/ui/skeleton';

withDefaults(
    defineProps<{
        form: any;
        available_groups?: Modules.CRM.Data.CustomerGroupData[];
        available_users?: Modules.Identity.Data.UserData[];
        isEdit?: boolean;
    }>(),
    {
        available_groups: () => [],
        available_users: () => [],
    },
);

defineEmits(['submit']);
</script>

<template>
    <form @submit.prevent="$emit('submit')">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="flex items-center gap-2 text-xl font-bold">
                        <UserCircle class="h-6 w-6" />
                        {{ isEdit ? $t('Edit Customer') : $t('Create Customer') }}
                    </CardTitle>
                    <CardDescription>{{
                        isEdit
                            ? $t('Update customer profile and group assignments.')
                            : $t('Add a new customer to your CRM.')
                    }}</CardDescription>
                </div>
                <slot name="header-actions"></slot>
            </CardHeader>
            <CardContent class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="user_id">
                            {{ $t('User Account') }}
                            <span v-if="!isEdit" class="text-destructive">*</span>
                        </Label>
                        <Deferred data="available_users">
                            <template #fallback>
                                <Skeleton id="user_id" as="button" type="button" class="h-10 w-full cursor-wait" />
                            </template>
                            <Select v-model="form.user_id!" name="user_id" :disabled="isEdit" :required="!isEdit">
                                <SelectTrigger id="user_id" :aria-label="$t('User Account')">
                                    <SelectValue :placeholder="$t('Select User')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="user in available_users ?? []"
                                        :key="user.id!"
                                        :value="user.id"
                                    >
                                        {{ user.name }} ({{ user.email }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </Deferred>
                        <p v-if="isEdit" class="text-xs text-muted-foreground">
                            {{ $t('User account cannot be changed after creation.') }}
                        </p>
                        <InputError :message="form.errors.user_id" />
                    </div>
                    <div class="space-y-2">
                        <Label for="phone">{{ $t('Phone Number') }}</Label>
                        <Input
                            id="phone"
                            name="phone"
                            autocomplete="tel"
                            v-model="form.phone!"
                            placeholder="e.g. +1234567890"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.phone" />
                    </div>
                    <div class="space-y-2">
                        <Label for="customer_group_id">{{ $t('Customer Group') }}</Label>
                        <Deferred data="available_groups">
                            <template #fallback>
                                <Skeleton id="customer_group_id" as="button" type="button" class="h-10 w-full cursor-wait" />
                            </template>
                            <Select v-model="form.customer_group_id!" name="customer_group_id">
                                <SelectTrigger id="customer_group_id" :aria-label="$t('Customer Group')">
                                    <SelectValue :placeholder="$t('Select Group')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="group in available_groups ?? []"
                                        :key="group.id!"
                                        :value="group.id"
                                        >{{ group.name }}</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </Deferred>
                        <InputError :message="form.errors.customer_group_id" />
                    </div>
                    <div class="space-y-2">
                        <Label for="company_name">{{ $t('Company Name') }}</Label>
                        <Input
                            id="company_name"
                            name="company_name"
                            autocomplete="organization"
                            v-model="form.company_name!"
                            placeholder="e.g. Acme Corp"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.company_name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="tax_number">{{ $t('Tax Number') }}</Label>
                        <Input
                            id="tax_number"
                            name="tax_number"
                            v-model="form.tax_number!"
                            placeholder="e.g. TAX-123"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.tax_number" />
                    </div>
                    <div class="space-y-2">
                        <Label for="birth_date">{{ $t('Birth Date') }}</Label>
                        <Input
                            id="birth_date"
                            name="birth_date"
                            autocomplete="bday"
                            type="date"
                            v-model="form.birth_date!"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.birth_date" />
                    </div>
                    <div class="space-y-2">
                        <Label for="gender">{{ $t('Gender') }}</Label>
                        <Select v-model="form.gender!" name="gender" autocomplete="sex">
                            <SelectTrigger id="gender" :aria-label="$t('Gender')">
                                <SelectValue :placeholder="$t('Select Gender')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="male">{{ $t('Male') }}</SelectItem>
                                <SelectItem value="female">{{ $t('Female') }}</SelectItem>
                                <SelectItem value="other">{{ $t('Other') }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.gender" />
                    </div>
                </div>
            </CardContent>
            <CardFooter class="flex justify-end border-t border-sidebar-border pt-6">
                <Button type="submit" :disabled="form.processing" class="flex items-center gap-2">
                    <slot name="submit-icon"></slot>
                    {{ isEdit ? $t('Update Customer') : $t('Save Customer') }}
                </Button>
            </CardFooter>
        </Card>
    </form>
</template>
