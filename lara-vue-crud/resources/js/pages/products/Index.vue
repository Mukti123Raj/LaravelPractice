<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Button from '@/components/ui/button/Button.vue';
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

interface Props {
    products: Product[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '/products',
    },
];
const page = usePage();
const { isAdmin } = useAdmin();

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('products.destroy', {id}));
    }
}
</script>

<template>
    <Head title="Dashboard" />

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
            <Link :href="route('products.create')" v-if="isAdmin()"> <Button>Create a Product</Button></Link>
            <Table>
                <TableCaption>A list of your Product.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-[100px] text-start">Id</TableHead>
                        <TableHead>Name</TableHead>
                        <TableHead>Price</TableHead>
                        <TableHead>Description</TableHead>
                        <TableHead className="text-center">Action</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="product in props.products" :key="product.id">
                        <TableCell>{{ product.id }}</TableCell>
                        <TableCell>{{ product.name }}</TableCell>
                        <TableCell>{{ product.price }}</TableCell>
                        <TableCell>{{ product.description }}</TableCell>
                        <TableCell class="text-center space-x-2">
                            <Link :href="route('products.edit', {id: product.id} )" v-if="isAdmin()"><Button class="bg-slate-600">Edit</Button></Link>
                            <Button 
                                class="bg-red-600" 
                                :class="{ 'opacity-50 cursor-not-allowed': !isAdmin() }"
                                @click="isAdmin() ? handleDelete(product.id) : null"
                                :disabled="!isAdmin()"
                            >
                                Delete
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <div></div>
        </div>
    </AppLayout>
</template>
