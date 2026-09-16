<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{ sale: any; canManage: boolean }>();
const isAdmin = Number(props.canManage) === 1;

const form = ref({
    sale_date: props.sale?.sale_date ?? '',
    amount: props.sale?.amount ?? 0,
    status: props.sale?.status ?? 'completed',
    notes: props.sale?.notes ?? '',
});

function submit() {
    router.post(`/sales/update/${props.sale.id}`, form.value);
}
</script>

<template>
    <Head title="Edit Sale" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Edit Sale</h1>
            <form @submit.prevent="submit" class="grid max-w-md grid-cols-1 gap-4">
                <input v-model="form.sale_date" type="date" class="input" required />
                <input v-model="form.amount" type="number" step="0.01" class="input" placeholder="Amount" required />
                <select v-model="form.status" class="input" :disabled="!isAdmin">
                    <option value="completed">completed</option>
                    <option value="pending">pending</option>
                </select>
                <textarea v-model="form.notes" class="input" placeholder="Notes" :disabled="!isAdmin"></textarea>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.input { padding: 0.5rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; }
.btn { padding: 0.5rem 1rem; border-radius: 0.375rem; }
.btn-primary { background-color: #2563eb; color: white; }
</style>
