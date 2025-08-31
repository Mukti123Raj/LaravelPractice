<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create a Product',
        href: '/create',
    },
];

const form = useForm({
    name: '',
    price: '',
    description: '',
});

const handleSubmit = () =>{
    form.post(route('products.store'));
}
</script>

<template>
    <Head title="Create a Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <form @submit.prevent="handleSubmit" class="w=8/12 space-y-4">
                <div class="space-y-2">
                    <Label for="Product name">Name</Label>
                    <Input v-model="form.name" type="text" placeholder="Name"></Input>
                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</div>
                </div>
                <div class="space-y-2">
                    <Label for="Product price">Price</Label>
                    <Input v-model="form.price" type="number" placeholder="Price"></Input>
                    <div v-if="form.errors.price" class="mt-1 text-sm text-red-500">{{ form.errors.price }}</div>
                </div>
                <div class="space-y-2">
                    <Label for="Product description">Description</Label>
                    <Input type="text" v-model="form.description" placeholder="Description"></Input>
                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</div>
                </div>

                <Button type="submit" :disabled="form.processing">Add a Product</Button>
            </form>
        </div>
    </AppLayout>
</template>
