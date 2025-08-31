<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Plus, Trash2, X } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

interface Customer {
    id: number;
    name: string;
    email: string;
}

interface Product {
    id: number;
    name: string;
    price: number;
    description: string;
}

interface SalesOrderItem {
    id?: number;
    product_id: number;
    description: string;
    units: number;
    unit_price: number;
    discount: number;
    taxes: number;
    total: number;
}

interface SalesOrder {
    id?: number;
    order_id: string;
    order_date: string;
    status: string;
    customer_id: number;
    items: SalesOrderItem[];
    subtotal: number;
    discount: number;
    taxes: number;
    grand_total: number;
}

interface Props {
    customers: Customer[];
    products: Product[];
    salesOrder?: SalesOrder;
    isEditing?: boolean;
    admin_id: number;
}

const props = withDefaults(defineProps<Props>(), {
    isEditing: false,
});

const page = usePage();
const showProductDialog = ref(false);
const selectedProduct = ref<Product | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sales Orders',
        href: '/sales-orders',
    },
    {
        title: props.isEditing ? 'Edit Sales Order' : 'Create Sales Order',
        href: '#',
    },
];

const form = useForm({
    order_id: props.salesOrder?.order_id || '',
    order_date: props.salesOrder?.order_date || new Date().toISOString().split('T')[0],
    status: props.salesOrder?.status || 'pending',
    customer_id: props.salesOrder?.customer_id || '',
    items: props.salesOrder?.items?.map(item => ({
        ...item,
        units: Number(item.units) || 0,
        unit_price: Number(item.unit_price) || 0,
        discount: Number(item.discount) || 0,
        taxes: Number(item.taxes) || 0,
        total: Number(item.total) || 0,
    })) || [],
    admin_id: props.admin_id,
});

// Computed properties for calculations
const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (Number(item.units) * Number(item.unit_price)), 0);
});

const totalDiscount = computed(() => {
    return form.items.reduce((sum, item) => sum + Number(item.discount), 0);
});

const totalTaxes = computed(() => {
    return form.items.reduce((sum, item) => sum + Number(item.taxes), 0);
});

const grandTotal = computed(() => {
    return subtotal.value - totalDiscount.value + totalTaxes.value;
});

// Watch for changes to update item totals
watch(form.items, (newItems) => {
    newItems.forEach(item => {
        // Ensure all numeric values are properly converted
        const units = Number(item.units) || 0;
        const unitPrice = Number(item.unit_price) || 0;
        const discount = Number(item.discount) || 0;
        const taxes = Number(item.taxes) || 0;
        
        item.total = (units * unitPrice) - discount + taxes;
    });
}, { deep: true });

const addProduct = () => {
    if (!selectedProduct.value) return;
    
    const product = selectedProduct.value;
    form.items.push({
        product_id: product.id,
        description: product.description,
        units: 1,
        unit_price: product.price,
        discount: 0,
        taxes: 0,
        total: product.price,
    });
    
    selectedProduct.value = null;
    showProductDialog.value = false;
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const handleSubmit = () => {
    if (props.isEditing) {
        form.put(route('sales-orders.update', { salesOrder: props.salesOrder?.id }));
    } else {
        form.post(route('sales-orders.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Sales Order' : 'Create Sales Order'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-6">
            <div v-if="page.props.flash?.message" class="mb-4">
                <Alert class="bg-blue-200">
                    <AlertTitle>Notification!</AlertTitle>
                    <AlertDescription>
                        {{ page.props.flash.message }}
                    </AlertDescription>
                </Alert>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Order Information</CardTitle>
                        <CardDescription>Basic details of the sales order</CardDescription>
                    </CardHeader>
                    <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="order_id">Order ID</Label>
                            <Input 
                                v-model="form.order_id" 
                                type="text" 
                                placeholder="Order ID"
                                :class="{ 'border-red-500': form.errors.order_id }"
                            />
                            <div v-if="form.errors.order_id" class="text-sm text-red-500">{{ form.errors.order_id }}</div>
                        </div>

                        <div class="space-y-2">
                            <Label for="order_date">Order Date</Label>
                            <Input 
                                v-model="form.order_date" 
                                type="date"
                                :class="{ 'border-red-500': form.errors.order_date }"
                            />
                            <div v-if="form.errors.order_date" class="text-sm text-red-500">{{ form.errors.order_date }}</div>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <select 
                                v-model="form.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.status }"
                            >
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <div v-if="form.errors.status" class="text-sm text-red-500">{{ form.errors.status }}</div>
                        </div>

                        <div class="space-y-2">
                            <Label for="customer_id">Customer</Label>
                            <select 
                                v-model="form.customer_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.customer_id }"
                            >
                                <option value="">Select Customer</option>
                                <option 
                                    v-for="customer in customers" 
                                    :key="customer.id" 
                                    :value="customer.id"
                                >
                                    {{ customer.name }} ({{ customer.email }})
                                </option>
                            </select>
                            <div v-if="form.errors.customer_id" class="text-sm text-red-500">{{ form.errors.customer_id }}</div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Products Section -->
                <Card>
                    <CardHeader>
                        <CardTitle>Products</CardTitle>
                        <CardDescription>Add products to the order</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium">Order Items</h3>
                            <Dialog v-model:open="showProductDialog">
                                <DialogTrigger as-child>
                                    <Button type="button">
                                        <Plus class="h-4 w-4 mr-2" />
                                        Add Product
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="max-w-2xl">
                                    <DialogHeader>
                                        <DialogTitle>Select Product</DialogTitle>
                                        <DialogDescription>Choose a product to add to the order</DialogDescription>
                                    </DialogHeader>
                                    
                                    <div class="space-y-4">
                                        <div class="space-y-2">
                                            <Label>Product</Label>
                                            <select 
                                                v-model="selectedProduct"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            >
                                                <option :value="null">Select a product</option>
                                                <option 
                                                    v-for="product in products" 
                                                    :key="product.id" 
                                                    :value="product"
                                                >
                                                    {{ product.name }} - ${{ product.price }}
                                                </option>
                                            </select>
                                        </div>

                                        <div v-if="selectedProduct" class="space-y-4 p-4 border rounded-lg">
                                            <h4 class="font-medium">{{ selectedProduct.name }}</h4>
                                            <p class="text-sm text-gray-600">{{ selectedProduct.description }}</p>
                                            <p class="text-sm font-medium">Price: ${{ selectedProduct.price }}</p>
                                        </div>
                                    </div>

                                    <DialogFooter>
                                        <Button type="button" variant="outline" @click="showProductDialog = false">
                                            Cancel
                                        </Button>
                                        <Button type="button" @click="addProduct" :disabled="!selectedProduct">
                                            Add Product
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </div>

                        <!-- Products Table -->
                        <div v-if="form.items.length > 0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Product</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Units</TableHead>
                                        <TableHead>Unit Price</TableHead>
                                        <TableHead>Discount</TableHead>
                                        <TableHead>Taxes</TableHead>
                                        <TableHead>Total</TableHead>
                                        <TableHead>Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(item, index) in form.items" :key="index">
                                        <TableCell>
                                            {{ products.find(p => p.id === item.product_id)?.name }}
                                        </TableCell>
                                        <TableCell>
                                            <Input 
                                                v-model="item.description" 
                                                type="text" 
                                                placeholder="Description"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input 
                                                v-model="item.units" 
                                                type="number" 
                                                min="1"
                                                class="w-20"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input 
                                                v-model="item.unit_price" 
                                                type="number" 
                                                min="0"
                                                step="0.01"
                                                class="w-24"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input 
                                                v-model="item.discount" 
                                                type="number" 
                                                min="0"
                                                step="0.01"
                                                class="w-24"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input 
                                                v-model="item.taxes" 
                                                type="number" 
                                                min="0"
                                                step="0.01"
                                                class="w-24"
                                            />
                                        </TableCell>
                                        <TableCell class="font-medium">
                                            ${{ (Number(item.total) || 0).toFixed(2) }}
                                        </TableCell>
                                        <TableCell>
                                            <Button 
                                                type="button" 
                                                variant="outline" 
                                                size="sm"
                                                @click="removeItem(index)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div v-else class="text-center py-8 text-gray-500">
                            No products added yet. Click "Add Product" to get started.
                        </div>
                    </CardContent>
                </Card>

                <!-- Summary Section -->
                <Card v-if="form.items.length > 0">
                    <CardHeader>
                        <CardTitle>Order Summary</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2 text-right">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>${{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Discount:</span>
                                <span>-${{ totalDiscount.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Taxes:</span>
                                <span>+${{ totalTaxes.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t pt-2">
                                <span>Grand Total:</span>
                                <span>${{ grandTotal.toFixed(2) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <Button type="button" variant="outline" @click="window.history.back()">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing || form.items.length === 0">
                        {{ isEditing ? 'Update Sales Order' : 'Create Sales Order' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template> 