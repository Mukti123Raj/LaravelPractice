<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Rocket } from 'lucide-vue-next';
import { useAdmin } from '@/composables/useAdmin';

interface Customer {
    id: number;
    name: string;
    email: string;
}

interface SalesOrderItem {
    id: number;
    total: number;
}

interface SalesOrder {
    id: number;
    order_id: string;
    order_date: string;
    status: string;
    customer: Customer;
    admin: {
        id: number;
        name: string;
        email: string;
    };
    items: SalesOrderItem[];
    subtotal: number;
    grand_total: number;
}

interface Props {
    salesOrders: SalesOrder[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sales Orders',
        href: '/sales-orders',
    },
];

const page = usePage();
const { isAdmin } = useAdmin();

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this sales order?')) {
        router.delete(route('sales-orders.destroy', { salesOrder: id }));
    }
}

const getStatusColor = (status: string) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'processing': return 'bg-blue-100 text-blue-800';
        case 'shipped': return 'bg-purple-100 text-purple-800';
        case 'delivered': return 'bg-green-100 text-green-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

// Helper function to safely format currency
const formatCurrency = (value: any) => {
    const num = Number(value || 0);
    return isNaN(num) ? '0.00' : num.toFixed(2);
}
</script>

<template>
    <Head title="Sales Orders" />

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
            
            <div class="mb-4">
                <Link :href="route('sales-orders.create')" v-if="isAdmin()">
                    <Button>Create Sales Order</Button>
                </Link>
            </div>

            <Table>
                <TableCaption>A list of your Sales Orders.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-[100px] text-start">Order ID</TableHead>
                        <TableHead>Order Date</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Customer</TableHead>
                        <TableHead>Admin</TableHead>
                        <TableHead>Subtotal</TableHead>
                        <TableHead>Grand Total</TableHead>
                        <TableHead className="text-center">Action</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="order in props.salesOrders" :key="order.id">
                        <TableCell>{{ order.order_id }}</TableCell>
                        <TableCell>{{ new Date(order.order_date).toLocaleDateString() }}</TableCell>
                        <TableCell>
                            <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusColor(order.status)]">
                                {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                            </span>
                        </TableCell>
                        <TableCell>{{ order.customer.name }}</TableCell>
                        <TableCell>{{ order.admin?.name || 'N/A' }}</TableCell>
                        <TableCell>${{ formatCurrency(order.subtotal) }}</TableCell>
                        <TableCell>${{ formatCurrency(order.grand_total) }}</TableCell>
                        <TableCell class="text-center space-x-2">
                            <Link :href="route('sales-orders.edit', { salesOrder: order.id })" v-if="isAdmin()">
                                <Button class="bg-slate-600">Edit</Button>
                            </Link>
                            <Button 
                                class="bg-red-600" 
                                :class="{ 'opacity-50 cursor-not-allowed': !isAdmin() }"
                                @click="isAdmin() ? handleDelete(order.id) : null"
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