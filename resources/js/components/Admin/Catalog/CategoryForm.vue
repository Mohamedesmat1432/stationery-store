<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Save, Image as ImageIcon, X, FolderTree } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useAutoSlug } from '@/composables/useAutoSlug';
import * as categoryRoutes from '@/routes/admin/categories/index';

type CategoryData = Modules.Catalog.Data.CategoryData;

const form = defineModel<any>('form', { required: true });
const {
    category,
    available_categories = [],
    isEdit,
    categoryName,
} = defineProps<{
    category?: CategoryData;
    available_categories?: CategoryData[];
    isEdit?: boolean;
    categoryName?: string;
}>();

defineEmits(['submit']);

const { autoSlug } = useAutoSlug(form.value, 'name', 'slug', isEdit);

const iconPreview = ref(category?.icon || null);
const bannerPreview = ref(category?.banner_image || null);

const bannerInput = ref<HTMLInputElement | null>(null);
const iconInput = ref<HTMLInputElement | null>(null);

const handleIconUpload = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (file) {
        form.value.icon = file;
        iconPreview.value = URL.createObjectURL(file);
    }
};

const handleBannerUpload = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (file) {
        form.value.banner_image = file;
        bannerPreview.value = URL.createObjectURL(file);
    }
};

const removeIcon = () => {
    form.value.icon = null;
    iconPreview.value = null;
};

const removeBanner = () => {
    form.value.banner_image = null;
    bannerPreview.value = null;
};

// Flat list for the parent selector
const flattenedCategories = computed(() => {
    const list: { id: string; name: string; depth: number }[] = [];
    
    const flatten = (items: CategoryData[], depth = 0) => {
        if (!items) {
            return;
        }
        
        items.forEach(item => {
            // Skip the current category AND all its descendants
            if (item.id === category?.id) {
                return;
            }
            
            list.push({ 
                id: item.id!, 
                name: '—'.repeat(depth) + ' ' + item.name,
                depth 
            });
            
            if (item.children) {
                flatten(item.children, depth + 1);
            }
        });
    };
    
    flatten(available_categories);

    return list;
});
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl font-bold">
                            <FolderTree class="h-6 w-6 text-primary" />
                            {{ isEdit ? $t('Edit Category') + ': ' + categoryName : $t('Create New Category') }}
                        </CardTitle>
                        <CardDescription>{{
                            isEdit
                                ? $t('Update category details, parent hierarchy, and media assets.')
                                : $t('Add a new category to organize your catalog.')
                        }}</CardDescription>
                    </div>
                    <slot name="header-actions"></slot>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <Label for="name">
                                {{ $t('Name') }}
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
                                @input="autoSlug"
                                :placeholder="$t('e.g. Fountain Pens')"
                                required 
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="slug">
                                {{ $t('Slug') }}
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input 
                                id="slug" 
                                v-model="form.slug" 
                                :placeholder="$t('fountain-pens')"
                                required 
                            />
                            <InputError :message="form.errors.slug" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">{{ $t('Description') }}</Label>
                        <Textarea 
                            id="description" 
                            v-model="form.description" 
                            :placeholder="$t('Briefly describe what this category contains...')"
                            rows="4" 
                            class="resize-none"
                        />
                        <InputError :message="form.errors.description" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">{{ $t('Search Engine Optimization') }}</CardTitle>
                    <CardDescription>{{ $t('Customize how this category appears in search engine results.') }}</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="meta_title">{{ $t('Meta Title') }}</Label>
                        <Input id="meta_title" v-model="form.meta_title" :placeholder="$t('SEO Friendly Title')" />
                        <InputError :message="form.errors.meta_title" />
                    </div>
                    
                    <div class="space-y-2">
                        <Label for="meta_description">{{ $t('Meta Description') }}</Label>
                        <Textarea 
                            id="meta_description" 
                            v-model="form.meta_description" 
                            :placeholder="$t('Short summary for search engines...')" 
                            rows="2" 
                            class="resize-none"
                        />
                        <InputError :message="form.errors.meta_description" />
                    </div>

                    <div class="space-y-2">
                        <Label for="meta_keywords">{{ $t('Keywords') }}</Label>
                        <Input id="meta_keywords" v-model="form.meta_keywords" :placeholder="$t('pens, stationery, fountain')" />
                        <InputError :message="form.errors.meta_keywords" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">{{ $t('Organization') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="parent_id">{{ $t('Parent Category') }}</Label>
                        <Select :model-value="form.parent_id || 'none'" @update:model-value="v => form.parent_id = (v === 'none' ? null : v)">
                            <SelectTrigger>
                                <SelectValue :placeholder="$t('None (Root Level)')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">{{ $t('None (Root Level)') }}</SelectItem>
                                <SelectItem 
                                    v-for="cat in flattenedCategories" 
                                    :key="cat.id" 
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.parent_id" />
                    </div>

                    <div class="space-y-2">
                        <Label for="sort_order">{{ $t('Sort Order') }}</Label>
                        <Input id="sort_order" type="number" v-model="form.sort_order" />
                        <InputError :message="form.errors.sort_order" />
                    </div>

                    <div class="space-y-4 pt-2">
                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active" class="cursor-pointer">{{ $t('Visible in Storefront') }}</Label>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_featured" v-model:checked="form.is_featured" />
                            <Label for="is_featured" class="cursor-pointer">{{ $t('Feature on Homepage') }}</Label>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">{{ $t('Media Assets') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Banner Upload -->
                    <div class="space-y-2">
                        <Label>{{ $t('Banner Image') }}</Label>
                        <div 
                            class="relative border-2 border-dashed border-sidebar-border rounded-xl p-4 flex flex-col items-center justify-center text-muted-foreground hover:bg-sidebar-accent/50 transition-all cursor-pointer min-h-35 group"
                            @click="bannerInput?.click()"
                        >
                            <input 
                                type="file" 
                                ref="bannerInput" 
                                class="hidden" 
                                accept="image/*" 
                                @change="handleBannerUpload" 
                            />
                            
                            <template v-if="bannerPreview">
                                <img :src="bannerPreview" class="absolute inset-0 w-full h-full object-cover rounded-xl opacity-60 group-hover:opacity-40 transition-opacity" />
                                <div class="relative z-10 flex flex-col items-center bg-background/50 backdrop-blur-sm p-2 rounded-lg">
                                    <ImageIcon class="w-6 h-6 mb-1" />
                                    <span class="text-[10px] font-bold uppercase">{{ $t('Change Banner') }}</span>
                                </div>
                                <Button 
                                    type="button"
                                    variant="destructive" 
                                    size="icon" 
                                    class="absolute top-2 right-2 h-7 w-7 z-20 shadow-lg"
                                    @click.stop="removeBanner"
                                >
                                    <X class="w-4 h-4" />
                                </Button>
                            </template>
                            <template v-else>
                                <ImageIcon class="w-10 h-10 mb-2 opacity-20 group-hover:scale-110 transition-transform" />
                                <span class="text-xs font-medium">{{ $t('Drop or Click to Upload Banner') }}</span>
                            </template>
                        </div>
                        <InputError :message="form.errors.banner_image" />
                    </div>

                    <!-- Icon Upload -->
                    <div class="space-y-2">
                        <Label>{{ $t('Category Icon') }}</Label>
                        <div 
                            class="relative border-2 border-dashed border-sidebar-border rounded-xl p-4 flex flex-col items-center justify-center text-muted-foreground hover:bg-sidebar-accent/50 transition-all cursor-pointer min-h-25 group"
                            @click="iconInput?.click()"
                        >
                            <input 
                                type="file" 
                                ref="iconInput" 
                                class="hidden" 
                                accept="image/*" 
                                @change="handleIconUpload" 
                            />
                            
                            <template v-if="iconPreview">
                                <img :src="iconPreview" class="absolute inset-0 w-full h-full object-contain p-4 opacity-70 group-hover:opacity-40 transition-opacity" />
                                <div class="relative z-10 flex flex-col items-center bg-background/50 backdrop-blur-sm p-2 rounded-lg">
                                    <span class="text-[9px] font-bold uppercase">{{ $t('Change Icon') }}</span>
                                </div>
                                <Button 
                                    type="button"
                                    variant="destructive" 
                                    size="icon" 
                                    class="absolute top-2 right-2 h-6 w-6 z-20 shadow-lg"
                                    @click.stop="removeIcon"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </Button>
                            </template>
                            <template v-else>
                                <ImageIcon class="w-8 h-8 mb-1 opacity-20 group-hover:scale-110 transition-transform" />
                                <span class="text-[10px] font-medium">{{ $t('Upload Category Icon') }}</span>
                            </template>
                        </div>
                        <InputError :message="form.errors.icon" />
                    </div>
                </CardContent>
            </Card>

                <Button type="submit" class="w-full h-12 text-lg shadow-xl" :disabled="form.processing">
                    <slot name="submit-icon">
                        <Save v-if="!form.processing" class="w-5 h-5 mr-2" />
                        <span v-else class="mr-2 animate-spin">◌</span>
                    </slot>
                    {{ isEdit ? $t('Update Category') : $t('Save Category') }}
                </Button>

                <Button variant="ghost" as-child class="w-full" type="button">
                    <Link :href="categoryRoutes.index.url()">
                        {{ $t('Cancel') }}
                    </Link>
                </Button>
            </div>
    </form>
</template>
