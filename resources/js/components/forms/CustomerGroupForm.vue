<script setup lang="ts">
import { Users } from 'lucide-vue-next';
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

defineProps<{
    form: any;
    isEdit?: boolean;
}>();

const emit = defineEmits(['submit', 'update:slug', 'update:is_active']);

const onNameInput = () => {
    emit('update:slug');
};

const onIsActiveChange = (val: boolean | 'indeterminate') => {
    emit('update:is_active', val === true);
};
</script>

<template>
    <form @submit.prevent="$emit('submit')">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="flex items-center gap-2 text-xl font-bold">
                        <Users class="h-6 w-6" />
                        {{ isEdit ? $t('Edit Customer Group') : $t('Create Customer Group') }}
                    </CardTitle>
                    <CardDescription>{{
                        isEdit
                            ? $t('Update segment details and discount rules.')
                            : $t('Define a new segment for your customers.')
                    }}</CardDescription>
                </div>
                <slot name="header-actions"></slot>
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
                            v-model="form.name!"
                            @input="onNameInput"
                            :placeholder="$t('e.g. VIP Customers')"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="slug"
                            >{{ $t('Slug') }} <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="slug"
                            v-model="form.slug!"
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
                        v-model="form.discount_percentage!"
                        :disabled="form.processing"
                    />
                    <InputError :message="form.errors.discount_percentage" />
                </div>

                <div class="space-y-2">
                    <Label for="description">{{ $t('Description') }}</Label>
                    <Textarea
                        id="description"
                        v-model="form.description!"
                        :placeholder="$t('Describe this group...')"
                        :disabled="form.processing"
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="is_active"
                        :model-value="form.is_active"
                        @update:model-value="onIsActiveChange"
                    />
                    <Label for="is_active">{{ $t('Active') }}</Label>
                </div>
            </CardContent>
            <CardFooter class="flex justify-end border-t border-sidebar-border pt-6">
                <Button type="submit" :disabled="form.processing" class="flex items-center gap-2">
                    <slot name="submit-icon"></slot>
                    {{ isEdit ? $t('Update Group') : $t('Save Group') }}
                </Button>
            </CardFooter>
        </Card>
    </form>
</template>
