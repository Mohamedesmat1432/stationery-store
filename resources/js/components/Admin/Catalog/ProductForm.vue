<script setup lang="ts">
import { computed, ref } from 'vue';
import { Save, ChevronLeft, Image as ImageIcon, X, Package, Tag, Layers, Star, DollarSign, Barcode } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useAutoSlug } from '@/composables/useAutoSlug';
import * as productRoutes from '@/routes/admin/products/index';

type ProductData = Modules.Catalog.Data.ProductData;
type CategoryData = Modules.Catalog.Data.CategoryData;
type BrandData = { id: string, name: string };

const props = withDefaults(
    defineProps<{
        form: any;
        product?: ProductData;
        categories?: CategoryData[];
        brands?: BrandData[];
        isEdit?: boolean;
        productName?: string;
    }>(),
    {
        categories: () => [],
        brands: () => [],
    }
);

const emit = defineEmits(['submit']);

const { autoSlug } = useAutoSlug(props.form, 'name', 'slug', props.isEdit);

const imagePreview = ref(props.product?.featured_image || null);
const imageInput = ref<HTMLInputElement | null>(null);

const handleImageUpload = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        props.form.featured_image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const removeImage = () => {
    props.form.featured_image = null;
    imagePreview.value = null;
};

// Flat list for categories
const flattenedCategories = computed(() => {
    const list: { id: string; name: string; depth: number }[] = [];
    
    const flatten = (items: CategoryData[], depth = 0) => {
        if (!items) return;
        
        items.forEach(item => {
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
    
    flatten(props.categories);
    return list;
});
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-12">
        <div class="lg:col-span-2 space-y-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl font-bold">
                            <Package class="h-6 w-6 text-primary" />
                            {{ isEdit ? $t('Edit Product') + ': ' + productName : $t('Create New Product') }}
                        </CardTitle>
                        <CardDescription>{{
                            isEdit
                                ? $t('Update product details, pricing, and inventory information.')
                                : $t('Add a new product to your catalog.')
                        }}</CardDescription>
                    </div>
                    <slot name="header-actions"></slot>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <Label for="name">
                                {{ $t('Product Name') }}
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
                                @input="autoSlug"
                                :placeholder="$t('e.g. Parker Jotter Special Edition')"
                                required 
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="sku">
                                {{ $t('SKU') }}
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input 
                                id="sku" 
                                v-model="form.sku" 
                                :placeholder="$t('PK-JOTTER-BLUE')"
                                required 
                            />
                            <InputError :message="form.errors.sku" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="slug">
                            {{ $t('Slug') }}
                            <span class="text-destructive">*</span>
                        </Label>
                        <Input 
                            id="slug" 
                            v-model="form.slug" 
                            :placeholder="$t('parker-jotter-special')"
                            required 
                        />
                        <InputError :message="form.errors.slug" />
                    </div>

                    <div class="space-y-2">
                        <Label for="short_description">{{ $t('Short Description') }}</Label>
                        <Textarea 
                            id="short_description" 
                            v-model="form.short_description" 
                            :placeholder="$t('A brief summary for listing pages...')"
                            rows="2" 
                            class="resize-none"
                        />
                        <InputError :message="form.errors.short_description" />
                    </div>

                    <div class="space-y-2">
                        <Label for="description">{{ $t('Description') }}</Label>
                        <Textarea 
                            id="description" 
                            v-model="form.description" 
                            :placeholder="$t('Detailed product description...')"
                            rows="6" 
                            class="resize-none"
                        />
                        <InputError :message="form.errors.description" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <DollarSign class="h-5 w-5 text-green-600" />
                        <CardTitle class="text-lg font-semibold">{{ $t('Pricing Details') }}</CardTitle>
                    </div>
                    <CardDescription>{{ $t('Configure base price and financial tracking for this product.') }}</CardDescription>
                </CardHeader>
                <CardContent class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <Label for="price">{{ $t('Base Price') }} <span class="text-destructive">*</span></Label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-muted-foreground text-sm">$</span>
                            <Input id="price" type="number" step="0.01" v-model="form.price" class="pl-7" required />
                        </div>
                        <InputError :message="form.errors.price" />
                    </div>

                    <div class="space-y-2">
                        <Label for="compare_at_price">{{ $t('Compare at Price') }}</Label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-muted-foreground text-sm">$</span>
                            <Input id="compare_at_price" type="number" step="0.01" v-model="form.compare_at_price" class="pl-7" />
                        </div>
                        <InputError :message="form.errors.compare_at_price" />
                    </div>

                    <div class="space-y-2">
                        <Label for="cost_price">{{ $t('Cost per Item') }}</Label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-muted-foreground text-sm">$</span>
                            <Input id="cost_price" type="number" step="0.01" v-model="form.cost_price" class="pl-7" />
                        </div>
                        <p class="text-[10px] text-muted-foreground">{{ $t('Customers won\'t see this.') }}</p>
                        <InputError :message="form.errors.cost_price" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Tag class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">{{ $t('Organization') }}</CardTitle>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="category_id">{{ $t('Primary Category') }}</Label>
                        <Select v-model="form.category_id">
                            <SelectTrigger>
                                <SelectValue :placeholder="$t('Select Category')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">{{ $t('Uncategorized') }}</SelectItem>
                                <SelectItem 
                                    v-for="cat in flattenedCategories" 
                                    :key="cat.id" 
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.category_id" />
                    </div>

                    <div class="space-y-2">
                        <Label for="brand_id">{{ $t('Brand') }}</Label>
                        <Select v-model="form.brand_id">
                            <SelectTrigger>
                                <SelectValue :placeholder="$t('Select Brand')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">{{ $t('Generic / No Brand') }}</SelectItem>
                                <SelectItem 
                                    v-for="brand in brands" 
                                    :key="brand.id" 
                                    :value="brand.id"
                                >
                                    {{ brand.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.brand_id" />
                    </div>

                    <div class="space-y-4 pt-2">
                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active" class="cursor-pointer font-medium">{{ $t('Active & Buyable') }}</Label>
                        </div>
                        <p class="text-[10px] text-muted-foreground pl-6">
                            {{ $t('When disabled, the product is hidden from the storefront.') }}
                        </p>

                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_featured" v-model:checked="form.is_featured" />
                            <Label for="is_featured" class="cursor-pointer font-medium">{{ $t('Featured Product') }}</Label>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <ImageIcon class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">{{ $t('Product Image') }}</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <div 
                        class="relative border-2 border-dashed border-sidebar-border rounded-xl p-4 flex flex-col items-center justify-center text-muted-foreground hover:bg-sidebar-accent/50 transition-all cursor-pointer min-h-[200px] group"
                        @click="imageInput?.click()"
                    >
                        <input 
                            type="file" 
                            ref="imageInput" 
                            class="hidden" 
                            accept="image/*" 
                            @change="handleImageUpload" 
                        />
                        
                        <template v-if="imagePreview">
                            <img :src="imagePreview" class="absolute inset-0 w-full h-full object-contain p-4 opacity-100 group-hover:opacity-40 transition-opacity" />
                            <div class="relative z-10 hidden group-hover:flex flex-col items-center bg-background/80 backdrop-blur-sm p-3 rounded-lg shadow-xl">
                                <ImageIcon class="w-6 h-6 mb-1 text-primary" />
                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ $t('Change Image') }}</span>
                            </div>
                            <Button 
                                type="button"
                                variant="destructive" 
                                size="icon" 
                                class="absolute top-3 right-3 h-8 w-8 z-20 shadow-lg"
                                @click.stop="removeImage"
                            >
                                <X class="w-4 h-4" />
                            </Button>
                        </template>
                        <template v-else>
                            <ImageIcon class="w-12 h-12 mb-3 opacity-20 group-hover:scale-110 transition-transform text-primary" />
                            <span class="text-sm font-semibold">{{ $t('Upload Product Image') }}</span>
                            <span class="text-[10px] opacity-60 mt-1 uppercase">{{ $t('PNG, JPG up to 2MB') }}</span>
                        </template>
                    </div>
                    <InputError :message="form.errors.featured_image" />
                </CardContent>
            </Card>

            <div class="flex flex-col gap-3">
                <Button type="submit" class="w-full h-12 text-lg shadow-xl font-bold" :disabled="form.processing">
                    <slot name="submit-icon">
                        <Save v-if="!form.processing" class="w-5 h-5 mr-2" />
                        <span v-else class="mr-2 animate-spin">◌</span>
                    </slot>
                    {{ isEdit ? $t('Update Product') : $t('Save Product') }}
                </Button>
                
                <Button variant="ghost" as-child class="w-full" type="button">
                    <Link :href="productRoutes.index.url()">
                        {{ $t('Cancel') }}
                    </Link>
                </Button>
            </div>
        </div>
    </form>
</template>
