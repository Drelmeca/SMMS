<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SalesEntryForm from '@/components/SalesEntryForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    sale: {
        id: number;
        sale_date: string;
        amount: number;
        status: string;
        notes: string | null;
        items: Array<{ product_id: number; quantity: number; unit_price: number }>;
    };
    products: Array<{ id: number; name: string; price: number; stock_quantity: number }>;
    canManage: boolean;
}>();
</script>

<template>
    <Head title="Edit Sale" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Edit Sale</h1>
            <SalesEntryForm :sale="sale" :products="products" :can-manage="canManage" />
        </div>
    </AppLayout>
</template>
