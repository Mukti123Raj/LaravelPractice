<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Rocket } from 'lucide-vue-next';
import { useAdmin } from '@/composables/useAdmin';

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
    productListings: ProductListing[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Product Listings',
        href: '/product-listings',
    },
];

const page = usePage();
const { isAdmin } = useAdmin();

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this product listing?')) {
        router.delete(route('product-listings.destroy', { id }));
    }
}
</script>

<template>
    <Head title="Product Listings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div v-if="page.props.flash?.message" class="mb-4">
                <Alert class="bg-blue-200">
                    <Rocket class="h-4 w-4" />
                    <AlertTitle>Notification!</AlertTitle>
                    <AlertDescription>
                        {{ page.props.flash.message }}
                    </AlertDescription>
                </Alert>
            </div>
            <Link :href="route('product-listings.create')" v-if="isAdmin()">
                <Button>Create a Product Listing</Button>
            </Link>
            <Table>
                <TableCaption>A list of your Product Listings.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-[100px] text-start">Id</TableHead>
                        <TableHead>Name</TableHead>
                        <TableHead>SKU</TableHead>
                        <TableHead>Product</TableHead>
                        <TableHead>Seller</TableHead>
                        <TableHead>Price</TableHead>
                        <TableHead className="text-center">Action</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="listing in props.productListings" :key="listing.id">
                        <TableCell>{{ listing.id }}</TableCell>
                        <TableCell>{{ listing.name }}</TableCell>
                        <TableCell>{{ listing.sku }}</TableCell>
                        <TableCell>{{ listing.product.name }}</TableCell>
                        <TableCell>{{ listing.seller.name }}</TableCell>
                        <TableCell>${{ listing.price }}</TableCell>
                        <TableCell class="text-center space-x-2">
                            <Link :href="route('product-listings.edit', { id: listing.id })" v-if="isAdmin()">
                                <Button class="bg-slate-600">Edit</Button>
                            </Link>
                            <Button 
                                class="bg-red-600" 
                                :class="{ 'opacity-50 cursor-not-allowed': !isAdmin() }"
                                @click="isAdmin() ? handleDelete(listing.id) : null"
                                :disabled="!isAdmin()"
                            >
                                Delete
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
