<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="relative min-h-screen w-full flex items-center justify-end bg-slate-950 overflow-hidden font-sans">
        <Head title="Register" />

        <!-- Background Image with Cover sizing and specific alignment to push visuals to the left -->
        <div 
            class="absolute inset-0 z-0 bg-[url('/register-bg.jpg')] bg-cover bg-left md:bg-center bg-no-repeat"
        ></div>

        <!-- Custom gradient overlay to make text highly readable on the right, fading to transparent on the left -->
        <div class="absolute inset-0 z-10 bg-gradient-to-l from-[#030712]/90 via-[#030712]/50 to-transparent"></div>

        <!-- Registration form container positioned to the right side (empty portion of the image) -->
        <div class="relative z-20 w-full max-w-md mx-4 sm:mx-8 md:mx-16 lg:mx-24 xl:mx-32 my-8">
            <div class="bg-slate-900/70 backdrop-blur-xl border border-slate-800/80 p-8 rounded-3xl shadow-2xl space-y-8">
                
                <!-- Header -->
                <div class="space-y-1">
                    <h2 class="text-3xl font-extrabold text-white tracking-wide">Registration</h2>
                    <p class="text-xs text-slate-400">Create your account to start managing tasks.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    
                    <!-- Name Input with Silhouette Icon -->
                    <div class="flex items-start gap-4">
                        <div class="mt-2.5 text-slate-400 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="flex-grow space-y-1">
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                placeholder="Full Name"
                                class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <InputError class="mt-1" :message="form.errors.name" />
                        </div>
                    </div>

                    <!-- Email Input with Mail Icon -->
                    <div class="flex items-start gap-4">
                        <div class="mt-2.5 text-slate-400 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-grow space-y-1">
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                placeholder="Email Address"
                                class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                                required
                                autocomplete="username"
                            />
                            <InputError class="mt-1" :message="form.errors.email" />
                        </div>
                    </div>

                    <!-- Password Input with Lock Icon -->
                    <div class="flex items-start gap-4">
                        <div class="mt-2.5 text-slate-400 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div class="flex-grow space-y-1">
                            <input
                                id="password"
                                type="password"
                                v-model="form.password"
                                placeholder="Password"
                                class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                                required
                                autocomplete="new-password"
                            />
                            <InputError class="mt-1" :message="form.errors.password" />
                        </div>
                    </div>

                    <!-- Confirm Password with Shield Check Icon -->
                    <div class="flex items-start gap-4">
                        <div class="mt-2.5 text-slate-400 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="flex-grow space-y-1">
                            <input
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                placeholder="Confirm Password"
                                class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                                required
                                autocomplete="new-password"
                            />
                            <InputError class="mt-1" :message="form.errors.password_confirmation" />
                        </div>
                    </div>

                    <!-- Submit Circular Checkmark Button matching the mockup image design -->
                    <div class="flex flex-col items-center justify-center pt-4 space-y-3">
                        <button
                            type="submit"
                            class="relative w-16 h-16 rounded-full bg-gradient-to-tr from-purple-600 via-indigo-600 to-teal-400 p-0.5 flex items-center justify-center shadow-lg hover:shadow-indigo-500/35 transition-all hover:scale-105 active:scale-95 group"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                            title="Submit Registration"
                        >
                            <!-- Inner dark layer to create depth, matching the mockup's circular check design -->
                            <div class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center group-hover:bg-slate-900 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-teal-400 group-hover:text-teal-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </button>
                        <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Register Account</span>
                    </div>

                    <div class="text-center text-xs text-slate-500 font-semibold pt-2 border-t border-slate-800/40">
                        Already have an account? 
                        <Link :href="route('login')" class="text-blue-400 hover:text-blue-300 hover:underline ms-1 transition-colors">
                            Sign in instead
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
