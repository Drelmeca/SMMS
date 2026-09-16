<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { reactive, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    mode: String,
});

const users = reactive({
    id: null,
    name: '',
    email: '',
    status: true,
    role: 1,
    password: '',
    password_confirmation: '',

});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: 'Create/Edit',
        href: '/users',
    },
];

const createusers = () => {

    if (props.mode === 'edit') {
        users.id = props.user.id;
    }

    axios.post('/users/store', users)
        .then(response => {

            alert(response.data.message);
            users.name = '';
            users.email = '';
            users.status = true;
            users.role = 1;

            router.visit('/users'); // Redirect to the index page after creation
            // Optionally, redirect or show a success message
        })
        .catch(error => {
            console.error('Error creating User:', error);
            alert('An error occurred while creating User.');
        });


};

onMounted(() => {
    if (props.mode === 'edit') {
        users.id = props.user.id;
        users.name = props.user.name;
        users.email = props.user.email;
        users.status = Boolean(props.user.status);
        users.role = props.user.role;
    }
});

</script>

<template>

    <Head title="User" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h1 class="text-2xl font-bold mb-4">{{ mode === 'edit' ? 'Edit User' : 'Create User' }}</h1>
                        <form @submit.prevent="createusers">
                            <div class="mb-4">
                                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                                <input type="text" id="name" v-model="users.name"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required />
                            </div>
                            <div class="mb-4">
                                <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Role:</label>
                                <select id="role" v-model="users.role"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required>
                                    <option :value="2">Admin</option>
                                    <option :value="0">Guest</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                                <input type="email" id="email" v-model="users.email"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required />
                            </div>
                            <div class="mb-4">
                                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                                <input type="password" id="password" v-model="users.password"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    :required="mode !== 'edit'" />

                            </div>
                            <div class="mb-4">
                                <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm
                                    Password:</label>
                                <input type="password" id="password_confirmation" v-model="users.password_confirmation"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    :required="mode !== 'edit'" />
                            </div>
                            <div class="mb-4">
                                <label for="phone" class="block text-gray-500 text-sm font-bold mb-2">Status:</label>
                                <select id="status" v-model="users.status"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required >
                                    <option :value="true">Active</option>
                                    <option :value="false">Inactive</option>

                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    {{ mode === 'edit' ? 'Update' : 'Create' }} User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
