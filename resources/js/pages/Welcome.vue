<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Sales Monitoring Management System">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="min-h-screen bg-[#050505] text-white"
        style="background-image: radial-gradient(ellipse at 12% 30%, rgba(34, 197, 94, 0.65) 0%, transparent 30%), radial-gradient(ellipse at 82% 28%, rgba(249, 115, 22, 0.38) 0%, transparent 22%), radial-gradient(ellipse at 52% 76%, rgba(255, 255, 255, 0.18) 0%, transparent 18%), linear-gradient(135deg, #050505 0%, #0b2417 48%, #050505 100%);"
    >
        <section class="container mx-auto grid min-h-[calc(100vh-76px)] items-center gap-12 px-6 py-12 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="text-center lg:text-left">
                <p class="mb-3 text-sm uppercase tracking-[0.25em] text-white">SMMS</p>
                <h1 class="text-4xl font-bold text-white md:text-6xl">Sale's Monitoring Management System</h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-200 lg:mx-0">
                    Record sales, manage products, and review business performance in one organized workspace.
                </p>
            </div>

            <div class="mx-auto w-full max-w-sm rounded-2xl border border-white/60 bg-slate-950/85 p-8 text-white shadow-2xl backdrop-blur-md">
                <h2 class="text-center text-2xl font-bold">Login</h2>
                <p class="mt-2 text-center text-sm text-slate-300">Login to Access your sales workspace</p>

                <form @submit.prevent="submit" class="mt-7 space-y-5">
                    <div>
                        <label for="welcome-email" class="mb-2 block text-xs font-medium text-slate-300">Email</label>
                        <input id="welcome-email" v-model="form.email" type="email" required autocomplete="email"
                            class="w-full border-0 border-b border-slate-400 bg-transparent px-0 py-2 text-sm text-white outline-none placeholder:text-slate-400 focus:border-white focus:ring-0"
                            placeholder="Email address" />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-300">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label for="welcome-password" class="mb-2 block text-xs font-medium text-slate-300">Password</label>
                        <input id="welcome-password" v-model="form.password" type="password" required autocomplete="current-password"
                            class="w-full border-0 border-b border-slate-400 bg-transparent px-0 py-2 text-sm text-white outline-none placeholder:text-slate-400 focus:border-white focus:ring-0"
                            placeholder="Password" />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-300">{{ form.errors.password }}</p>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-300">
                        <label class="flex items-center gap-2">
                            <input v-model="form.remember" type="checkbox" class="rounded border-slate-400 bg-transparent" />
                            Remember me
                        </label>
                        <Link :href="route('password.request')" class="hover:text-white">Forgot Password?</Link>
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="w-full rounded-full bg-white py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-200 disabled:opacity-60">
                        {{ form.processing ? 'Logging in...' : 'Login' }}
                    </button>
                </form>

                <p class="mt-6 text-center text-xs text-slate-300">
                    <Link :href="route('register')" class="font-semibold text-white hover:underline">Register</Link>
                </p>
            </div>
        </section>
    </div>
</template>