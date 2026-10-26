<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SalesEntryForm from '@/components/SalesEntryForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    products: Array<{ id: number; name: string; price: number; stock_quantity: number }>;
    canManage: boolean;
}>();
</script>

<template>
    <Head title="Create Sale" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Create Sale</h1>
            <SalesEntryForm :products="products" :can-manage="canManage" />
        </div>
    </AppLayout>
</template>
