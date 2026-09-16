<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive, onMounted, defineProps } from 'vue';
import axios from 'axios';

const props = defineProps({
    person: Object,
    mode: String,
});

const sampleCrud = reactive({
    id: null,
    name: '',
    email: '',
    phone: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Sample Crud List',
        href: '/sample-crud',
    },
    {
        title: 'Create/Edit',
        href: '/sample-crud/create',
    },
];

const createSampleCrud = () => {

    if (props.mode === 'edit') {
        sampleCrud.id = props.person.id;
    }

    axios.post('/sample-crud/store', sampleCrud)
        .then(response => {

            alert(response.data.message);
            sampleCrud.name = '';
            sampleCrud.email = '';
            sampleCrud.phone = '';

            router.visit('/sample-crud'); // Redirect to the index page after creation
            // Optionally, redirect or show a success message
        })
        .catch(error => {
            console.error('Error creating Sample Crud:', error);
            alert('An error occurred while creating Sample Crud.');
        });


};

onMounted(() => {
    if (props.mode === 'edit') {
        sampleCrud.id = props.person.id;
        sampleCrud.name = props.person.name;
        sampleCrud.email = props.person.email;
        sampleCrud.phone = props.person.phone;
    }
});

</script>

<template>

    <Head title="Sample Crud" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h1 class="text-2xl font-bold mb-4">Create Sample Crud</h1>
                        <form @submit.prevent="createSampleCrud">
                            <div class="mb-4">
                                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                                <input type="text" id="name" v-model="sampleCrud.name"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required />
                            </div>
                            <div class="mb-4">
                                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                                <input type="email" id="email" v-model="sampleCrud.email"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required />
                            </div>
                            <div class="mb-4">
                                <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone:</label>
                                <input type="text" id="phone" v-model="sampleCrud.phone"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required />
                            </div>
                            <div class="flex items-center justify-between">
                                <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    {{ mode === 'edit' ? 'Update' : 'Create' }} Sample Crud
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
