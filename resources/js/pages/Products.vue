<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Product {
    id: number;
    name: string;
    category: string | null;
    price: number;
    is_active: boolean;
}

defineProps<{ products: Product[] }>();

const form = ref({ name: '', category: '', price: 0 });

const formatCurrency = (value: number) => new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
}).format(Number(value));

function submit() {
    router.post('/products', form.value, {
        onSuccess: () => {
            form.value = { name: '', category: '', price: 0 };
        },
    });
}
</script>

<template>
    <Head title="Products" />
    <AppLayout>
        <div class="space-y-6 p-6">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-sky-600">Product management</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Products</h1>
            </div>

            <form @submit.prevent="submit" class="grid gap-3 rounded-xl border bg-white p-5 shadow-sm md:grid-cols-4">
                <input v-model="form.name" required placeholder="Product name" class="rounded-lg border px-3 py-2" />
                <input v-model="form.category" placeholder="Category" class="rounded-lg border px-3 py-2" />
                <input v-model="form.price" required type="number" min="0" step="0.01" placeholder="Price" class="rounded-lg border px-3 py-2" />
                <button class="rounded-lg bg-sky-600 px-4 py-2 font-semibold text-white hover:bg-sky-700">Add product</button>
            </form>

            <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr><th class="px-5 py-3">Product</th><th class="px-5 py-3">Category</th><th class="px-5 py-3">Price</th><th class="px-5 py-3">Status</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="product in products" :key="product.id">
                            <td class="px-5 py-4 font-medium">{{ product.name }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ product.category || '—' }}</td>
                            <td class="px-5 py-4">{{ formatCurrency(product.price) }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-emerald-100 px-2 py-1 text-xs text-emerald-700">{{ product.is_active ? 'Active' : 'Inactive' }}</span></td>
                        </tr>
                        <tr v-if="products.length === 0"><td colspan="4" class="px-5 py-8 text-center text-slate-500">No products recorded yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
