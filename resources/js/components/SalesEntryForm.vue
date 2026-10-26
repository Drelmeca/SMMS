<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface ProductOption {
    id: number;
    name: string;
    price: number;
    stock_quantity: number;
}

interface SaleItem {
    product_id: number;
    quantity: number;
    unit_price: number;
}

interface SaleRecord {
    id: number;
    sale_date: string;
    amount: number;
    status: string;
    notes: string | null;
    items: SaleItem[];
}

interface SaleFormItem {
    product_id: number | null;
    quantity: number;
    unit_price?: number;
}

const props = defineProps<{
    products: ProductOption[];
    canManage: boolean;
    sale?: SaleRecord;
}>();

const form = ref<{
    sale_date: string;
    amount: number;
    status: string;
    notes: string;
    items: SaleFormItem[];
}>({
    sale_date: props.sale?.sale_date ?? '',
    amount: Number(props.sale?.amount ?? 0),
    status: props.sale?.status ?? 'completed',
    notes: props.sale?.notes ?? '',
    items: props.sale?.items.map((item) => ({
        product_id: item.product_id,
        quantity: item.quantity,
        unit_price: item.unit_price,
    })) ?? [{ product_id: null as number | null, quantity: 1 }],
});

const productById = computed(() => new Map(props.products.map((product) => [product.id, product])));
const originalPrices = new Map((props.sale?.items ?? []).map((item) => [item.product_id, item.unit_price]));
const errors = ref<Record<string, string>>({});
const calculatedTotal = computed(() =>
    form.value.items.reduce((total, item) => {
        const unitPrice = item.unit_price ?? productById.value.get(Number(item.product_id))?.price ?? 0;
        return total + Number(unitPrice) * Number(item.quantity);
    }, 0),
);
const isLegacySale = computed(() => Boolean(props.sale && props.sale.items.length === 0));

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);

function addItem() {
    form.value.items.push({ product_id: null, quantity: 1 });
}

function removeItem(index: number) {
    form.value.items.splice(index, 1);
}

function changeProduct(item: SaleFormItem, event: Event) {
    if (!(event.currentTarget instanceof HTMLSelectElement)) {
        return;
    }

    item.product_id = Number(event.currentTarget.value);
    item.unit_price = originalPrices.get(item.product_id);
}

function submit() {
    errors.value = {};
    const data = {
        sale_date: form.value.sale_date,
        status: form.value.status,
        notes: form.value.notes,
        ...(isLegacySale.value
            ? { amount: form.value.amount }
            : {
                  items: form.value.items.map(({ product_id, quantity }) => ({ product_id, quantity })),
              }),
    };

    if (props.sale) {
        router.post(`/sales/update/${props.sale.id}`, data, {
            onError: (validationErrors) => {
                errors.value = validationErrors;
            },
        });
        return;
    }

    router.post('/sales/store', data, {
        onError: (validationErrors) => {
            errors.value = validationErrors;
        },
    });
}
</script>

<template>
    <form @submit.prevent="submit" class="grid max-w-2xl grid-cols-1 gap-4">
        <p v-for="(message, field) in errors" :key="field" role="alert" class="text-sm text-red-700">
            {{ message }}
        </p>
        <input v-model="form.sale_date" type="date" class="input" required />

        <template v-if="!isLegacySale">
            <div class="space-y-3">
                <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-[minmax(0,1fr)_7rem_auto] gap-2">
                    <select :value="item.product_id ?? ''" class="input" required @change="changeProduct(item, $event)">
                        <option :value="null" disabled>Select product</option>
                        <option
                            v-for="product in products"
                            :key="product.id"
                            :value="product.id"
                            :disabled="product.stock_quantity < item.quantity && product.id !== item.product_id"
                        >
                            {{ product.name }} · {{ formatCurrency(product.price) }} · {{ product.stock_quantity }} in stock
                        </option>
                    </select>
                    <input v-model.number="item.quantity" type="number" min="1" step="1" class="input" required aria-label="Quantity" />
                    <button v-if="form.items.length > 1" type="button" class="remove-button" @click="removeItem(index)">Remove</button>
                </div>
                <button type="button" class="text-sm font-medium text-sky-700" @click="addItem">Add another product</button>
            </div>

            <p class="font-semibold text-slate-800">Total: {{ formatCurrency(calculatedTotal) }}</p>
            <p v-if="products.length === 0" class="text-sm text-amber-700">No products are currently in stock.</p>
        </template>
        <input v-else v-model.number="form.amount" type="number" min="0" step="0.01" class="input" placeholder="Amount" required />

        <select v-if="canManage" v-model="form.status" class="input">
            <option value="completed">completed</option>
            <option value="pending">pending</option>
        </select>
        <textarea v-if="canManage" v-model="form.notes" class="input" placeholder="Notes"></textarea>
        <button type="submit" class="btn btn-primary" :disabled="!isLegacySale && products.length === 0">
            {{ sale ? 'Update sale' : 'Add sale' }}
        </button>
    </form>
</template>

<style scoped>
.input { padding: 0.5rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; }
.btn { padding: 0.5rem 1rem; border-radius: 0.375rem; }
.btn-primary { background-color: #2563eb; color: white; }
.btn-primary:disabled { cursor: not-allowed; opacity: 0.5; }
.remove-button { color: #b91c1c; font-size: 0.875rem; }
</style>
