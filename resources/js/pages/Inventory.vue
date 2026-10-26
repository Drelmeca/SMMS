<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface InventoryProduct {
    id: number;
    name: string;
    category: string | null;
    price: number;
    stock_quantity: number;
    is_active: boolean;
}

const props = defineProps<{ products: InventoryProduct[] }>();

const search = ref('');
const filter = ref<'all' | 'in-stock' | 'low-stock' | 'out-of-stock'>('all');
const lowStockThreshold = ref(50);
const stockEdits = ref<Record<number, number>>({});
const errors = ref<Record<number, string>>({});
const filterOptions = [
    { value: 'all', label: 'All products' },
    { value: 'in-stock', label: 'In stock' },
    { value: 'low-stock', label: 'Low stock' },
    { value: 'out-of-stock', label: 'Out of stock' },
] as const;
const currencyFormatter = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' });

const totalUnits = computed(() => props.products.reduce((total, product) => total + product.stock_quantity, 0));
const outOfStockCount = computed(() => props.products.filter((product) => product.stock_quantity === 0).length);
const lowStockCount = computed(() =>
    props.products.filter((product) => product.stock_quantity > 0 && product.stock_quantity <= lowStockThreshold.value).length,
);
const filteredProducts = computed(() => {
    const term = search.value.trim().toLocaleLowerCase();

    return props.products.filter((product) => {
        const matchesSearch =
            term === '' ||
            product.name.toLocaleLowerCase().includes(term) ||
            (product.category ?? '').toLocaleLowerCase().includes(term);
        const matchesFilter =
            filter.value === 'all' ||
            (filter.value === 'in-stock' && product.stock_quantity > lowStockThreshold.value) ||
            (filter.value === 'low-stock' &&
                product.stock_quantity > 0 &&
                product.stock_quantity <= lowStockThreshold.value) ||
            (filter.value === 'out-of-stock' && product.stock_quantity === 0);

        return matchesSearch && matchesFilter;
    });
});

function updateStock(product: InventoryProduct) {
    errors.value[product.id] = '';
    router.put(
        route('products.update', product.id),
        { stock_quantity: stockEdits.value[product.id] ?? product.stock_quantity },
        {
            preserveScroll: true,
            onSuccess: () => {
                delete stockEdits.value[product.id];
            },
            onError: (validationErrors) => {
                errors.value[product.id] = validationErrors.stock_quantity ?? 'Unable to update stock.';
            },
        },
    );
}

function stockLabel(product: InventoryProduct): string {
    if (!product.is_active) {
        return 'Inactive';
    }
    if (product.stock_quantity === 0) {
        return 'Out of stock';
    }
    if (product.stock_quantity <= lowStockThreshold.value) {
        return 'Low stock';
    }
    return 'In stock';
}

function stockClass(product: InventoryProduct): string {
    if (!product.is_active || product.stock_quantity === 0) {
        return 'bg-red-100 text-red-700';
    }
    if (product.stock_quantity <= lowStockThreshold.value) {
        return 'bg-amber-100 text-amber-700';
    }
    return 'bg-emerald-100 text-emerald-700';
}
</script>

<template>
    <Head title="Inventory" />
    <AppLayout>
        <main class="space-y-6 p-6">
            <header>
                <p class="text-sm uppercase tracking-[0.2em] text-sky-600">Stock management</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Inventory</h1>
                <p class="mt-1 text-sm text-slate-600">Monitor available stock and update quantities as inventory changes.</p>
            </header>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Inventory summary">
                <article class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Products</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ products.length }}</p>
                </article>
                <article class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Units in stock</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ totalUnits }}</p>
                </article>
                <article class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Low stock</p>
                    <p class="mt-2 text-3xl font-bold text-amber-700">{{ lowStockCount }}</p>
                </article>
                <article class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Out of stock</p>
                    <p class="mt-2 text-3xl font-bold text-red-700">{{ outOfStockCount }}</p>
                </article>
            </section>

            <section class="space-y-4 rounded-xl border bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <label class="grid gap-1 text-sm font-medium text-slate-700">
                        Search inventory
                        <input v-model="search" type="search" placeholder="Product or category" class="rounded-lg border px-3 py-2" />
                    </label>
                    <label class="grid gap-1 text-sm font-medium text-slate-700">
                        Low-stock threshold
                        <input v-model.number="lowStockThreshold" type="number" min="1" class="w-40 rounded-lg border px-3 py-2" />
                    </label>
                </div>

                <div class="flex flex-wrap gap-2" aria-label="Filter inventory">
                    <button
                        v-for="option in filterOptions"
                        :key="option.value"
                        type="button"
                        class="rounded-full px-3 py-1.5 text-sm font-medium"
                        :class="filter === option.value ? 'bg-sky-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        @click="filter = option.value"
                    >
                        {{ option.label }}
                    </button>
                </div>

                <div class="overflow-x-auto rounded-lg border">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Price</th>
                                <th class="px-4 py-3">Available</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Update stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="product in filteredProducts" :key="product.id">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ product.name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ product.category || '—' }}</td>
                                <td class="px-4 py-3">{{ currencyFormatter.format(product.price) }}</td>
                                <td class="px-4 py-3 font-semibold">{{ product.stock_quantity }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="stockClass(product)">{{ stockLabel(product) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <form class="flex items-center gap-2" @submit.prevent="updateStock(product)">
                                        <input
                                            v-model.number="stockEdits[product.id]"
                                            :placeholder="String(product.stock_quantity)"
                                            type="number"
                                            min="0"
                                            step="1"
                                            :aria-label="`New stock quantity for ${product.name}`"
                                            class="w-24 rounded border px-2 py-1.5"
                                        />
                                        <button type="submit" class="font-medium text-sky-700 hover:text-sky-900">Save</button>
                                    </form>
                                    <p v-if="errors[product.id]" role="alert" class="mt-1 text-xs text-red-600">{{ errors[product.id] }}</p>
                                </td>
                            </tr>
                            <tr v-if="filteredProducts.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    {{ products.length ? 'No products match these filters.' : 'No products have been added yet.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
