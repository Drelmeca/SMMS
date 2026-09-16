<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ArrowUpRight, CircleDollarSign, ClipboardCheck, Package, ShoppingCart, Users } from 'lucide-vue-next';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    totalAdmins: number;
    totalSalesCount: number;
    totalSalesAmount: number;
    pendingSales: number;
    completedSales: number;
    averageOrderValue: number;
    recentSales: Array<{ id: number; sale_date: string; amount: number; status: string; notes: string | null }>;
    monthlySales: Array<{ label: string; total: number }>;
}>();

const formatCurrency = (value: number) => new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
}).format(Number(value ?? 0));

const maxMonthlyValue = computed(() => {
    const values = props.monthlySales?.map((item) => Number(item.total ?? 0)) ?? [0];
    return Math.max(...values, 1);
});

const statusClasses: Record<string, string> = {
    completed: 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
    pending: 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
};
</script>

<template>
    <Head title="SMMS Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-2xl p-4">
            <section class="overflow-hidden rounded-2xl bg-slate-100 p-6 text-black shadow-lg shadow-slate-900/10">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-black">Sales Monitoring Management System</p>
                        <h1 class="mt-2 text-3xl font-bold">Operations Dashboard</h1>
                    </div>
                    <div class="rounded-xl border border-slate-300 bg-white px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-black">Performance</p>
                        <p class="mt-1 text-xl font-semibold">{{ formatCurrency(totalSalesAmount) }}</p>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Net Revenue</p>
                            <p class="mt-3 text-3xl font-bold text-slate-900">{{ formatCurrency(totalSalesAmount) }}</p>
                        </div>
                        <div class="rounded-xl bg-emerald-100 p-3 text-emerald-700">
                            <CircleDollarSign class="h-6 w-6" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-sm text-emerald-600">
                        <ArrowUpRight class="h-4 w-4" />
                        <span>{{ completedSales }} successful sales</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Transactions</p>
                            <p class="mt-3 text-3xl font-bold text-slate-900">{{ totalSalesCount }}</p>
                        </div>
                        <div class="rounded-xl bg-sky-100 p-3 text-sky-700">
                            <ShoppingCart class="h-6 w-6" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-sm text-slate-500">
                        <span>Avg order: {{ formatCurrency(averageOrderValue) }}</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Pending Sales</p>
                            <p class="mt-3 text-3xl font-bold text-slate-900">{{ pendingSales }}</p>
                        </div>
                        <div class="rounded-xl bg-amber-100 p-3 text-amber-700">
                            <ClipboardCheck class="h-6 w-6" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-sm text-amber-600">
                        <span>Needs follow-up</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Sales status</p>
                            <p class="mt-3 text-3xl font-bold text-slate-900">{{ completedSales }}</p>
                        </div>
                        <div class="rounded-xl bg-violet-100 p-3 text-violet-700">
                            <Package class="h-6 w-6" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-sm text-violet-600">
                        <span>{{ pendingSales }} transactions to review</span>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.7fr_1fr]">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Monthly revenue</p>
                            <h2 class="text-xl font-semibold text-slate-900">Sales trend</h2>
                        </div>
                        <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Last 6 months</div>
                    </div>

                    <div class="flex h-52 items-end gap-3">
                        <div v-for="item in monthlySales" :key="item.label" class="flex flex-1 flex-col items-center gap-2">
                            <div class="flex h-36 w-full items-end justify-center">
                                <div
                                    class="w-full rounded-t-xl bg-gradient-to-t from-sky-600 to-indigo-500 shadow-sm"
                                    :style="{ height: `${(Number(item.total) / maxMonthlyValue) * 100}%` }"
                                />
                            </div>
                            <span class="text-xs font-medium text-slate-500">{{ item.label }}</span>
                            <span class="text-[10px] text-slate-400">{{ formatCurrency(item.total) }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Operations snapshot</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-900">Team status</h2>

                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-sky-100 p-2 text-sky-700">
                                    <ClipboardCheck class="h-4 w-4" />
                                </div>
                                <span class="font-medium text-slate-700">Recorded transactions</span>
                            </div>
                            <span class="text-lg font-semibold text-slate-900">{{ totalSalesCount }}</span>
                        </div>

                        <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-violet-100 p-2 text-violet-700">
                                    <Users class="h-4 w-4" />
                                </div>
                                <span class="font-medium text-slate-700">System admins</span>
                            </div>
                            <span class="text-lg font-semibold text-slate-900">{{ totalAdmins }}</span>
                        </div>

                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Current focus</p>
                            <p class="mt-2 text-lg font-semibold text-slate-800">Keep sales conversion above target and clear the pending transactions queue.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Recent activity</p>
                        <h2 class="text-xl font-semibold text-slate-900">Latest sales</h2>
                    </div>
                    <a href="/sales" class="text-sm font-medium text-sky-600 hover:text-sky-700">View all sales</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-[0.12em] text-slate-500">
                                <th class="pb-3 pr-4 font-medium">Date</th>
                                <th class="pb-3 pr-4 font-medium">Amount</th>
                                <th class="pb-3 pr-4 font-medium">Status</th>
                                <th class="pb-3 font-medium">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="sale in recentSales" :key="sale.id" class="align-top">
                                <td class="py-3 pr-4 text-sm text-slate-700">{{ sale.sale_date }}</td>
                                <td class="py-3 pr-4 text-sm font-semibold text-slate-900">{{ formatCurrency(sale.amount) }}</td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="statusClasses[sale.status] ?? 'bg-slate-100 text-slate-700'">
                                        {{ sale.status }}
                                    </span>
                                </td>
                                <td class="py-3 text-sm text-slate-600">{{ sale.notes || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
