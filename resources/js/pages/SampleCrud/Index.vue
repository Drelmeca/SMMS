<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Sample Crud',
        href: '/sample-crud',
    },
];

defineProps({persons:Array});

const onEdit = (id) => {
    // Redirect to the edit page for the selected person
    //inertia visit(`/sample-crud/${id}/edit`);
    // or use window.location.href  
    router.visit(`/sample-crud/edit/${id}`);
};

</script>

<template>

    <Head title="Sample Crud List" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Sample Crud List</h1>
            <Link href="/sample-crud/create" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Create Sample Crud
            </Link>
        </div>

        <hr class="my-4" />
       <!-- create a table list of user make it compact -->
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="overflow-hidden rounded-lg border bg-card shadow-md dark:border-slate-700 dark:bg-slate-800">
                <table class="w-full table-auto text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Phone</th>
                            <th scope="col" class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Add user rows here -->
                         <tr v-for="person in persons" :key="person.id" class="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ persons.name }}</td>
                            <td class="px-6 py-4">{{ persons.email }}</td>
                            <td class="px-6 py-4">{{ persons.phone }}</td>
                            <td class="px-6 py-4">
                                <button class="text-blue-600 hover:text-blue-900 dark:text-blue-500 dark:hover:text-blue-400" @click="onEdit(person.id)">Edit</button>
                                &nbsp;|&nbsp;
                                <button class="text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
    </AppLayout>
</template>
