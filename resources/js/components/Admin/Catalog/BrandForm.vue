<script setup lang="ts">
import { ref } from 'vue';
import { Save, ChevronLeft, Image as ImageIcon, X, Globe, Tag } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { useAutoSlug } from '@/composables/useAutoSlug';
import * as brandRoutes from '@/routes/admin/brands/index';

type BrandData = Modules.Catalog.Data.BrandData;

const props = defineProps<{
    form: any;
    brand?: BrandData;
    isEdit?: boolean;
    brandName?: string;
}>();

const emit = defineEmits(['submit']);

const { autoSlug } = useAutoSlug(props.form, 'name', 'slug', props.isEdit);

const logoPreview = ref(props.brand?.logo || null);
const logoInput = ref<HTMLInputElement | null>(null);

const handleLogoUpload = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        props.form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const removeLogo = () => {
    props.form.logo = null;
    logoPreview.value = null;
};
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-12">
        <div class="lg:col-span-2 space-y-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl font-bold">
                            <Tag class="h-6 w-6 text-primary" />
                            {{ isEdit ? $t('Edit Brand') + ': ' + brandName : $t('Create New Brand') }}
                        </CardTitle>
                        <CardDescription>{{
                            isEdit
                                ? $t('Update brand identity, website, and visual assets.')
                                : $t('Add a new brand to your catalog.')
                        }}</CardDescription>
                    </div>
                    <slot name="header-actions"></slot>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <Label for="name">
                                {{ $t('Brand Name') }}
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
                                @input="autoSlug"
                                :placeholder="$t('e.g. Parker')"
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
                                :placeholder="$t('parker')"
                                required 
                            />
                            <InputError :message="form.errors.slug" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="website" class="flex items-center gap-2">
                            <Globe class="h-4 w-4" />
                            {{ $t('Official Website') }}
                        </Label>
                        <Input 
                            id="website" 
                            type="url"
                            v-model="form.website" 
                            :placeholder="$t('https://www.parkerpen.com')"
                        />
                        <InputError :message="form.errors.website" />
                    </div>

                    <div class="space-y-2">
                        <Label for="description">{{ $t('Description') }}</Label>
                        <Textarea 
                            id="description" 
                            v-model="form.description" 
                            :placeholder="$t('Brand history or details...')"
                            rows="6" 
                            class="resize-none"
                        />
                        <InputError :message="form.errors.description" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">{{ $t('Status & Order') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="sort_order">{{ $t('Sort Order') }}</Label>
                        <Input id="sort_order" type="number" v-model="form.sort_order" />
                        <InputError :message="form.errors.sort_order" />
                    </div>

                    <div class="flex items-center space-x-2 pt-2">
                        <Checkbox id="is_active" v-model:checked="form.is_active" />
                        <Label for="is_active" class="cursor-pointer font-medium">{{ $t('Active & Published') }}</Label>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <ImageIcon class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">{{ $t('Brand Logo') }}</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <div 
                        class="relative border-2 border-dashed border-sidebar-border rounded-xl p-4 flex flex-col items-center justify-center text-muted-foreground hover:bg-sidebar-accent/50 transition-all cursor-pointer min-h-[160px] group"
                        @click="logoInput?.click()"
                    >
                        <input 
                            type="file" 
                            ref="logoInput" 
                            class="hidden" 
                            accept="image/*" 
                            @change="handleLogoUpload" 
                        />
                        
                        <template v-if="logoPreview">
                            <img :src="logoPreview" class="absolute inset-0 w-full h-full object-contain p-6 opacity-100 group-hover:opacity-40 transition-opacity" />
                            <div class="relative z-10 hidden group-hover:flex flex-col items-center bg-background/80 backdrop-blur-sm p-3 rounded-lg shadow-xl">
                                <ImageIcon class="w-6 h-6 mb-1 text-primary" />
                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ $t('Change Logo') }}</span>
                            </div>
                            <Button 
                                type="button"
                                variant="destructive" 
                                size="icon" 
                                class="absolute top-3 right-3 h-8 w-8 z-20 shadow-lg"
                                @click.stop="removeLogo"
                            >
                                <X class="w-4 h-4" />
                            </Button>
                        </template>
                        <template v-else>
                            <ImageIcon class="w-12 h-12 mb-3 opacity-20 group-hover:scale-110 transition-transform text-primary" />
                            <span class="text-sm font-semibold">{{ $t('Upload Brand Logo') }}</span>
                            <span class="text-[10px] opacity-60 mt-1 uppercase">{{ $t('PNG, JPG or SVG') }}</span>
                        </template>
                    </div>
                    <InputError :message="form.errors.logo" />
                </CardContent>
            </Card>

            <div class="flex flex-col gap-3">
                <Button type="submit" class="w-full h-12 text-lg shadow-xl font-bold" :disabled="form.processing">
                    <Save v-if="!form.processing" class="w-5 h-5 mr-2" />
                    <span v-else class="mr-2 animate-spin">◌</span>
                    {{ isEdit ? $t('Update Brand') : $t('Save Brand') }}
                </Button>
                
                <Button variant="ghost" as-child class="w-full" type="button">
                    <Link :href="brandRoutes.index.url()">
                        {{ $t('Cancel') }}
                    </Link>
                </Button>
            </div>
        </div>
    </form>
</template>
