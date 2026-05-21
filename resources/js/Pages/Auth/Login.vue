<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

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
    <div class="relative min-h-screen w-full flex items-center justify-start bg-slate-950 overflow-hidden font-sans">
        <Head title="Log in" />

        <!-- Background Image with Cover sizing and specific alignment to push visuals to the right -->
        <div 
            class="absolute inset-0 z-0 bg-[url('/login-bg.jpg')] bg-cover bg-right md:bg-center bg-no-repeat"
        ></div>

        <!-- Custom gradient overlay to make text highly readable on the left, fading to transparent on the right -->
        <div class="absolute inset-0 z-10 bg-gradient-to-r from-[#030712]/90 via-[#030712]/50 to-transparent"></div>

        <!-- Form container positioned to the left side (empty portion of the image) -->
        <div class="relative z-20 w-full max-w-md mx-4 sm:mx-8 md:mx-16 lg:mx-24 xl:mx-32 my-8">
            <div class="bg-slate-900/70 backdrop-blur-xl border border-slate-800/80 p-8 rounded-2xl shadow-2xl space-y-6">
                
                <!-- Branding Header -->
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <span class="text-2xl font-extrabold text-white flex items-center gap-1.5">
                      <span class="text-blue-500">AI</span> Task Flow
                    </span>
                  </div>
                  <h2 class="text-xl font-bold text-white tracking-tight">Welcome Back</h2>
                  <p class="text-xs text-slate-400">Log in to manage your tasks and view AI summaries.</p>
                </div>

                <div v-if="status" class="p-3 rounded-lg bg-emerald-950/50 border border-emerald-800/50 text-sm font-semibold text-emerald-400">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email Address -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            placeholder="e.g. admin@example.com"
                            class="w-full bg-slate-950/60 border border-slate-800 rounded-lg px-4 py-2.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center">
                            <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Password</label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-xs text-blue-400 hover:text-blue-300 hover:underline transition-colors"
                            >
                                Forgot your password?
                            </Link>
                        </div>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            placeholder="••••••••"
                            class="w-full bg-slate-950/60 border border-slate-800 rounded-lg px-4 py-2.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                            required
                            autocomplete="current-password"
                        />
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer select-none">
                            <input 
                                type="checkbox"
                                name="remember" 
                                v-model="form.remember"
                                class="rounded bg-slate-950 border-slate-800 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-offset-slate-900 focus:ring-offset-2"
                            />
                            <span class="ms-2 text-xs font-semibold text-slate-400">Remember this device</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-3 pt-2">
                        <button
                            type="submit"
                            class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-500 active:bg-blue-700 text-white font-bold rounded-lg shadow-lg transition-colors text-sm flex items-center justify-center gap-2"
                            :class="{ 'opacity-30 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Signing In...' : 'Sign In' }}</span>
                        </button>

                        <div class="text-center text-xs text-slate-500 font-semibold pt-1">
                            Don't have an account? 
                            <Link :href="route('register')" class="text-blue-400 hover:text-blue-300 hover:underline ms-1 transition-colors">
                                Register here
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
