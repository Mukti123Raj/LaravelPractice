<template>
  <Head title="Customers" />

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
      <Link :href="route('customers.create')" v-if="isAdmin()">
        <Button>Create a Customer</Button>
      </Link>
      <Table>
        <TableCaption>A list of your Customers.</TableCaption>
        <TableHeader>
          <TableRow>
            <TableHead className="w-[100px] text-start">Id</TableHead>
            <TableHead>Name</TableHead>
            <TableHead>Phone Number</TableHead>
            <TableHead>Email</TableHead>
            <TableHead>Country</TableHead>
            <TableHead className="text-center">Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="customer in customers" :key="customer.id">
            <TableCell>{{ customer.id }}</TableCell>
            <TableCell>{{ customer.name }}</TableCell>
            <TableCell>{{ customer.phone_number }}</TableCell>
            <TableCell>{{ customer.email }}</TableCell>
            <TableCell>{{ customer.country }}</TableCell>
            <TableCell className="text-center space-x-2">
              <Link :href="route('customers.edit', customer.id)" v-if="isAdmin()">
                <Button className="bg-slate-600">Edit</Button>
              </Link>
              <Button 
                className="bg-red-600" 
                :className="{ 'opacity-50 cursor-not-allowed': !isAdmin() }"
                @click="isAdmin() ? deleteCustomer(customer.id) : null"
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

<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Button from '@/components/ui/button/Button.vue';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Rocket } from 'lucide-vue-next';
import { useAdmin } from '@/composables/useAdmin';

interface Customer {
    id: number;
    name: string;
    phone_number: string;
    email: string;
    address_line_1: string;
    address_line_2?: string;
    country: string;
    state: string;
    city: string;
}

interface Props {
    customers: Customer[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Customers',
        href: '/customers',
    },
];

const page = usePage();
const { isAdmin } = useAdmin();

const deleteCustomer = (id: number) => {
    if (confirm('Are you sure you want to delete this customer?')) {
        router.delete(route('customers.destroy', { id }));
    }
}
</script>
