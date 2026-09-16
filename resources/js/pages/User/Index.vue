<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Users',
        href: '/users',
    },
];

const props = defineProps({ users: Array });

const onEdit = (id: number) => {
    router.visit(`/users/edit/${id}`);
};

const toggleStatus = async (id: number, currentStatus: number | boolean) => {
    try {
        const response = await axios.patch(`/users/${id}/status`, {
            status: currentStatus ? 0 : 1,
        });

        const users = props.users as Array<Record<string, any>> | undefined;
        if (users) {
            const user = users.find((item) => item.id === id);
            if (user) {
               user.status = response.data.status;
            }
        }
    } catch (error) {
        console.error('Unable to update status:', error);
        alert('Unable to update user status.');
    }
};

const archiveUser = async (id: number) => {
    if (!window.confirm('Archive this user?')) {
        return;
    }

    try {
        await axios.patch(`/users/${id}/archive`);
        const users = props.users as Array<Record<string, any>> | undefined;
        if (users) {
            const index = users.findIndex((item) => item.id === id);
            if (index >= 0) {
                users.splice(index, 1);
            }
        }
    } catch (error) {
        console.error('Unable to archive user:', error);
        alert('Unable to archive user.');
    }
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">User's</h1>
            <Link href="/users/create" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
               Create
            </Link>
        </div>

        <hr class="my-4" />

        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="overflow-hidden rounded-lg border bg-card shadow-md dark:border-slate-700 dark:bg-slate-800">
               <table class="w-full table-auto text-left text-sm text-gray-500 dark:text-gray-400">
                   <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                       <tr>
                           <th scope="col" class="px-6 py-3">Name</th>
                           <th scope="col" class="px-6 py-3">Email</th>
                           <th scope="col" class="px-6 py-3">Status</th>
                           <th scope="col" class="px-6 py-3">Role</th>
                           <th scope="col" class="px-6 py-3">Actions</th>
                       </tr>
                   </thead>
                   <tbody>
                       <tr v-for="user in props.users" :key="user.id" class="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
                           <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ user.name }}</td>
                           <td class="px-6 py-4">{{ user.email }}</td>
                           <td class="px-6 py-4">
                               <button
                                   type="button"
                                   :class="user.status ? 'text-green-600' : 'text-red-600'"
                                   class="font-medium underline-offset-2 hover:underline"
                                   @click="toggleStatus(user.id, user.status)"
                               >
                                   {{ user.status ? 'Active' : 'Inactive' }}
                               </button>
                           </td>
                           <td class="px-6 py-4">
                               <span v-if="user.role === 2" class="text-blue-600">Admin</span>
                               <span v-else-if="user.role === 1" class="text-yellow-600">User</span>
                               <span v-else class="text-gray-600">Guest</span>
                           </td>
                           <td class="px-6 py-4">
                               <button class="text-blue-600 hover:text-blue-900 dark:text-blue-500 dark:hover:text-blue-400" @click="onEdit(user.id)">Edit</button>
                               &nbsp;|&nbsp;
                               <button class="text-orange-600 hover:text-orange-900" @click="archiveUser(user.id)">Archive</button>
                           </td>
                       </tr>
                   </tbody>
               </table>
            </div>
        </div>
    </AppLayout>
</template>
