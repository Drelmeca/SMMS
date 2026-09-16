<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = ref({ sale_date: '', amount: 0, status: 'completed', notes: '' });
const page = usePage();
const canManage = (page.props.auth as any)?.user?.role === 2;

function submit() {
    router.post('/sales/store', form.value);
}
</script>

<template>
    <Head title="Create Sale" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Create Sale</h1>
            <form @submit.prevent="submit" class="grid max-w-md grid-cols-1 gap-4">
                <input v-model="form.sale_date" type="date" class="input" required />
                <input v-model="form.amount" type="number" step="0.01" class="input" placeholder="Amount" required />
                <select v-if="canManage" v-model="form.status" class="input">
                    <option value="completed">completed</option>
                    <option value="pending">pending</option>
                </select>
                <textarea v-if="canManage" v-model="form.notes" class="input" placeholder="Notes"></textarea>
                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.input { padding: 0.5rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; }
.btn { padding: 0.5rem 1rem; border-radius: 0.375rem; }
.btn-primary { background-color: #2563eb; color: white; }
</style>
