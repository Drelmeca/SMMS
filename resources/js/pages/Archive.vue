<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

type ArchivedUser = {
    id: number;
    name: string;
    email: string;
    role: number;
    archived_at: string;
};

type ArchivedSale = {
    id: number;
    sale_date: string;
    amount: number;
    status: string;
    notes: string | null;
    submitted_by?: { name: string };
    archived_at: string;
};

const props = defineProps<{ users: ArchivedUser[]; sales: ArchivedSale[] }>();

function restoreUser(id: number) {
    router.patch(`/archive/users/${id}/restore`, {}, { preserveScroll: true });
}

function restoreSale(id: number) {
    router.patch(`/archive/sales/${id}/restore`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Archive" />
    <AppLayout>
        <div class="space-y-8 border-t border-slate-200 bg-white p-4 text-slate-950">
            <div>
                <h1 class="text-2xl font-semibold">Archive</h1>
                <p class="mt-1 text-sm text-slate-500">Review and restore archived users and sales records.</p>
            </div>

            <section>
                <h2 class="mb-3 text-lg font-semibold">Archived Users</h2>
                <div class="archive-card">
                    <table class="archive-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Archived</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in props.users" :key="user.id">
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.role === 2 ? 'Admin' : user.role === 0 ? 'Guest' : 'User' }}</td>
                                <td>{{ user.archived_at }}</td>
                                <td><button class="restore-link" @click="restoreUser(user.id)">Restore</button></td>
                            </tr>
                            <tr v-if="!props.users.length">
                                <td colspan="5" class="empty-cell">No archived users.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h2 class="mb-3 text-lg font-semibold">Archived Sales</h2>
                <div class="archive-card">
                    <table class="archive-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Submitted By</th>
                                <th>Notes</th>
                                <th>Archived</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sale in props.sales" :key="sale.id">
                                <td>{{ sale.sale_date }}</td>
                                <td>{{ sale.amount }}</td>
                                <td>{{ sale.status }}</td>
                                <td>{{ sale.submitted_by?.name ?? '-' }}</td>
                                <td>{{ sale.notes || '-' }}</td>
                                <td>{{ sale.archived_at }}</td>
                                <td><button class="restore-link" @click="restoreSale(sale.id)">Restore</button></td>
                            </tr>
                            <tr v-if="!props.sales.length">
                                <td colspan="7" class="empty-cell">No archived sales.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.archive-card { overflow-x: auto; border: 1px solid #dbe2ea; border-radius: 0.375rem; }
.archive-table { min-width: 760px; width: 100%; border-collapse: collapse; font-size: 0.75rem; }
.archive-table th, .archive-table td { border-bottom: 1px solid #e2e8f0; padding: 0.65rem 0.75rem; text-align: left; }
.archive-table th { background: #f8fafc; color: #243b64; font-size: 0.625rem; text-transform: uppercase; }
.archive-table tr:last-child td { border-bottom: 0; }
.restore-link { color: #2563eb; font-weight: 600; }
.empty-cell { color: #64748b; text-align: center !important; }
</style>
