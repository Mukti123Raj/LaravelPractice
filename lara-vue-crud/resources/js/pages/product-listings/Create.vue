<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

interface Product {
    id: number;
    name: string;
    price: number;
    description: string;
}

interface Seller {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface ProductListing {
    id: number;
    name: string;
    sku: string;
    price: number;
    product: Product;
    seller: Seller;
}

interface Props {
    products: Product[];
    productListing?: ProductListing;
    isEditing?: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.isEditing ? 'Edit Product Listing' : 'Create Product Listing',
        href: props.isEditing ? '/product-listings/edit' : '/product-listings/create',
    },
];

const form = useForm({
    name: props.productListing?.name || '',
    sku: props.productListing?.sku || '',
    product_id: props.productListing?.product_id || '',
});

const handleSubmit = () => {
    if (props.isEditing && props.productListing) {
        form.put(route('product-listings.update', { productListing: props.productListing.id }));
    } else {
        form.post(route('product-listings.store'));
    }
};

const getProductName = (productId: number) => {
    const product = props.products.find(p => p.id === productId);
    return product ? product.name : 'Select Product';
};

const getSelectedProduct = () => {
    return props.products.find(p => p.id === form.product_id);
};

const getSellerName = () => {
    const product = getSelectedProduct();
    return product?.seller?.name || 'Select Product First';
};

const getProductPrice = () => {
    const product = getSelectedProduct();
    return product?.price || 0;
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Product Listing' : 'Create Product Listing'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <form @submit.prevent="handleSubmit" class="w-8/12 space-y-4">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input v-model="form.name" type="text" placeholder="Product Listing Name" />
                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="sku">SKU</Label>
                    <Input v-model="form.sku" type="text" placeholder="Stock Keeping Unit" />
                    <div v-if="form.errors.sku" class="mt-1 text-sm text-red-500">{{ form.errors.sku }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="product">Product</Label>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="w-full justify-start">
                                {{ getProductName(form.product_id) }}
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-full">
                            <DropdownMenuItem 
                                v-for="product in products" 
                                :key="product.id"
                                @click="form.product_id = product.id"
                            >
                                {{ product.name }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <div v-if="form.errors.product_id" class="mt-1 text-sm text-red-500">{{ form.errors.product_id }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="seller">Seller</Label>
                    <Input :value="getSellerName()" type="text" readonly placeholder="Select a product first" />
                </div>

                <div class="space-y-2">
                    <Label for="price">Price</Label>
                    <Input :value="getProductPrice()" type="number" step="0.01" readonly placeholder="Price will be synced from product" />
                </div>

                <Button type="submit" :disabled="form.processing">
                    {{ isEditing ? 'Update Product Listing' : 'Create Product Listing' }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>
