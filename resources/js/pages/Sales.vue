<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{ sales: any; canManage: boolean; canEditSales: boolean; canDeleteSales: boolean }>();
const form = ref({ sale_date: '', amount: 0, status: 'completed', notes: '' });
const isAdmin = Number(props.canManage) === 1;

function submit() {
    router.post('/sales/store', form.value);
}

function editSale(id: number) {
    router.visit(`/sales/edit/${id}`);
}

function archiveSale(id: number) {
    if (window.confirm('Archive this sale record?')) {
        router.post(`/sales/archive/${id}`);
    }
}
</script>

<template>
    <Head title="Sales" />
    <AppLayout>
        <div class="border-t border-slate-200 bg-white p-4 text-slate-950">
            <h1 class="mb-4 text-2xl font-semibold">Sales</h1>

            <div class="mb-6 overflow-x-auto">
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <input v-model="form.sale_date" type="date" class="input" required />
                    <input v-model="form.amount" type="number" step="0.01" class="input" placeholder="Amount" required />
                    <select v-model="form.status" class="input" :disabled="!isAdmin">
                        <option value="completed">completed</option>
                        <option value="pending">pending</option>
                    </select>
                    <input v-model="form.notes" placeholder="Notes" class="input" :disabled="!isAdmin" />
                    <button type="submit" class="btn btn-primary w-fit">Add Sale</button>
                </form>
            </div>

            <div class="overflow-x-auto">
            <table v-if="sales" class="sales-table min-w-[980px] w-full table-auto">
                <thead>
                    <tr>
                        <th class="table-heading">Date</th>
                        <th class="table-heading">Amount</th>
                        <th class="table-heading">Status</th>
                        <th class="table-heading">Submitted By</th>
                        <th class="table-heading">Notes</th>
                        <th v-if="props.canEditSales" class="table-heading">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sale in sales.data" :key="sale.id">
                        <td class="table-cell">{{ sale.sale_date }}</td>
                        <td class="table-cell">{{ sale.amount }}</td>
                        <td class="table-cell">{{ sale.status }}</td>
                        <td class="table-cell">{{ sale.submitted_by?.name ?? '-' }}</td>
                        <td class="table-cell">{{ sale.notes || '-' }}</td>
                        <td v-if="props.canEditSales" class="table-cell">
                            <div class="flex gap-2">
                                <button type="button" class="action-link" @click="editSale(sale.id)">Edit</button>
                                <button v-if="props.canDeleteSales" type="button" class="action-link archive-link" @click="archiveSale(sale.id)">Archive</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>

            <div v-if="sales" class="mt-4 flex gap-4">
                <button v-if="sales.prev_page_url" class="pagination-link" @click="router.visit(sales.prev_page_url)">Previous</button>
                <button v-if="sales.next_page_url" class="pagination-link" @click="router.visit(sales.next_page_url)">Next</button>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.input { padding: 0.5rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; }
.btn { padding: 0.5rem 1rem; border-radius: 0.375rem; }
.btn-primary { background-color: #4f00e8; color: white; }
.table-heading { padding: 0.5rem; text-align: left; }
.table-cell { padding: 0.25rem 0.5rem; }
.pagination-link { padding: 0.25rem 0.5rem; }
.action-link { color: #4f00e8; font-size: 0.75rem; font-weight: 600; }
.archive-link { color: #c2410c; }
.sales-table { border: 1px solid #dbe2ea; border-radius: 0.375rem; border-collapse: separate; border-spacing: 0; overflow: hidden; font-size: 0.75rem; }
.sales-table th { background: #f8fafc; color: #243b64; font-size: 0.625rem; font-weight: 600; letter-spacing: 0.02em; text-transform: uppercase; }
.sales-table th, .sales-table td { border-bottom: 1px solid #e2e8f0; }
.sales-table tbody tr:last-child td { border-bottom: 0; }
</style>
