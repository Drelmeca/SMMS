<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Product {
    id: number;
    name: string;
    category: string | null;
    price: number;
    stock_quantity: number;
    is_active: boolean;
}

const props = defineProps<{ products: Product[] }>();

const form = ref({ name: '', category: '', price: 0, stock_quantity: 0 });
const stockEdits = ref<Record<number, number>>({});
const editingProductId = ref<number | null>(null);
const editForm = ref({ name: '', category: '', price: 0, stock_quantity: 0, is_active: true });
const editErrors = ref<Record<string, string>>({});

const formatCurrency = (value: number) => new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
}).format(Number(value));

function submit() {
    router.post('/products', form.value, {
        onSuccess: () => {
            form.value = { name: '', category: '', price: 0, stock_quantity: 0 };
        },
    });
}

function updateStock(product: Product) {
    router.put(`/products/${product.id}`, {
        stock_quantity: stockEdits.value[product.id] ?? product.stock_quantity,
    }, {
        onSuccess: () => {
            delete stockEdits.value[product.id];
        },
    });
}

function editProduct(product: Product) {
    editingProductId.value = product.id;
    editForm.value = {
        name: product.name,
        category: product.category ?? '',
        price: Number(product.price),
        stock_quantity: product.stock_quantity,
        is_active: product.is_active,
    };
    editErrors.value = {};
}

function saveProduct(product: Product) {
    editErrors.value = {};
    router.put(`/products/${product.id}`, editForm.value, {
        onSuccess: () => {
            editingProductId.value = null;
        },
        onError: (errors) => {
            editErrors.value = errors;
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
                <input v-model.number="form.stock_quantity" required type="number" min="0" step="1" placeholder="Opening stock" class="rounded-lg border px-3 py-2" />
                <button class="rounded-lg bg-sky-600 px-4 py-2 font-semibold text-white hover:bg-sky-700">Add product</button>
            </form>

            <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr><th class="px-5 py-3">Product</th><th class="px-5 py-3">Category</th><th class="px-5 py-3">Price</th><th class="px-5 py-3">Stock</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Actions</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="product in props.products" :key="product.id">
                            <template v-if="editingProductId === product.id">
                                <td class="px-5 py-4">
                                    <input v-model="editForm.name" required maxlength="120" aria-label="Product name" class="w-40 rounded border px-2 py-1" />
                                    <p v-if="editErrors.name" class="mt-1 text-xs text-red-600">{{ editErrors.name }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <input v-model="editForm.category" maxlength="80" aria-label="Category" class="w-32 rounded border px-2 py-1" />
                                    <p v-if="editErrors.category" class="mt-1 text-xs text-red-600">{{ editErrors.category }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <input v-model.number="editForm.price" required type="number" min="0" step="0.01" aria-label="Price" class="w-28 rounded border px-2 py-1" />
                                    <p v-if="editErrors.price" class="mt-1 text-xs text-red-600">{{ editErrors.price }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <input v-model.number="editForm.stock_quantity" required type="number" min="0" step="1" aria-label="Stock quantity" class="w-24 rounded border px-2 py-1" />
                                    <p v-if="editErrors.stock_quantity" class="mt-1 text-xs text-red-600">{{ editErrors.stock_quantity }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <label class="flex items-center gap-2">
                                        <input v-model="editForm.is_active" type="checkbox" />
                                        <span>{{ editForm.is_active ? 'Active' : 'Inactive' }}</span>
                                    </label>
                                    <p v-if="editErrors.is_active" class="mt-1 text-xs text-red-600">{{ editErrors.is_active }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex gap-2">
                                        <button type="button" class="text-sm font-medium text-sky-700" @click="saveProduct(product)">Save</button>
                                        <button type="button" class="text-sm text-slate-600" @click="editingProductId = null">Cancel</button>
                                    </div>
                                </td>
                            </template>
                            <template v-else>
                                <td class="px-5 py-4 font-medium">{{ product.name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ product.category || '—' }}</td>
                                <td class="px-5 py-4">{{ formatCurrency(product.price) }}</td>
                                <td class="px-5 py-4">
                                <form class="flex items-center gap-2" @submit.prevent="updateStock(product)">
                                    <input
                                        v-model.number="stockEdits[product.id]"
                                        :placeholder="String(product.stock_quantity)"
                                        type="number"
                                        min="0"
                                        step="1"
                                        :aria-label="`Stock quantity for ${product.name}`"
                                        class="w-24 rounded border px-2 py-1"
                                    />
                                    <button type="submit" class="text-sm font-medium text-sky-700">Update</button>
                                </form>
                                </td>
                                <td class="px-5 py-4"><span class="rounded-full bg-emerald-100 px-2 py-1 text-xs text-emerald-700">{{ product.is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="px-5 py-4">
                                    <button type="button" class="text-sm font-medium text-sky-700" @click="editProduct(product)">Edit</button>
                                </td>
                            </template>
                        </tr>
                        <tr v-if="props.products.length === 0"><td colspan="6" class="px-5 py-8 text-center text-slate-500">No products recorded yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
