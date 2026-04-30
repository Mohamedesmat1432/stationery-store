<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Package, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Products', href: '/admin/products' },
        ],
    },
});

defineProps<{
    products: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <Head title="Products Management" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="text-xl font-bold flex items-center gap-2">
                        <Package class="w-6 h-6" /> Products
                    </CardTitle>
                    <CardDescription>Manage your store's products, pricing, and status.</CardDescription>
                </div>
                <Button class="flex items-center gap-2">
                    <Plus class="w-4 h-4" /> Add Product
                </Button>
            </CardHeader>
            <CardContent>
                <div class="rounded-md border border-sidebar-border">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-muted-foreground uppercase bg-sidebar border-b border-sidebar-border">
                            <tr>
                                <th class="px-6 py-3 font-medium">Product</th>
                                <th class="px-6 py-3 font-medium">Category</th>
                                <th class="px-6 py-3 font-medium">Price</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                                <th class="px-6 py-3 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in products.data" :key="product.id" class="border-b border-sidebar-border last:border-0 hover:bg-sidebar-accent/50 transition-colors">
                                <td class="px-6 py-4 font-medium">{{ product.name }}</td>
                                <td class="px-6 py-4 text-muted-foreground">{{ product.category?.name || 'Uncategorized' }}</td>
                                <td class="px-6 py-4">{{ formatCurrency(product.base_price) }}</td>
                                <td class="px-6 py-4">
                                    <Badge :variant="product.is_active ? 'default' : 'secondary'">
                                        {{ product.is_active ? 'Active' : 'Draft' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <Button variant="outline" size="icon" class="h-8 w-8">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button variant="destructive" size="icon" class="h-8 w-8">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="products.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">
                                    No products found. Start by adding one!
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Simple Pagination -->
                <div class="mt-4 flex items-center justify-between" v-if="products.total > 0">
                    <span class="text-sm text-muted-foreground">
                        Showing {{ products.data.length }} of {{ products.total }} results
                    </span>
                    <div class="flex gap-2">
                        <Button 
                            v-for="(link, i) in products.links" 
                            :key="i"
                            :variant="link.active ? 'default' : 'outline'"
                            :disabled="!link.url"
                            size="sm"
                            as-child
                        >
                            <Link v-if="link.url" :href="link.url" v-html="link.label"></Link>
                            <span v-else v-html="link.label"></span>
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
