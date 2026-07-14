<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
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
    <Head title="Admin Portal | TSMS" />

    <div class="min-h-screen flex items-center justify-center bg-slate-950 relative overflow-hidden font-sans selection:bg-cyan-500/30">
        <!-- Ambient Background Glows -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-cyan-600/20 rounded-full blur-[120px] mix-blend-screen pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-600/20 rounded-full blur-[120px] mix-blend-screen pointer-events-none"></div>
        
        <!-- Subtle Grid Pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PHBhdGggZD0iTTAgMGg0MHY0MEgweiIgZmlsbD0ibm9uZSIvPjxwYXRoIGQ9Ik0wIDM5LjVoNDBNMzkuNSAwdiM0MCIgc3Ryb2tlPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDMpIiBzdHJva2Utd2lkdGg9IjEiLz48L3N2Zz4=')] opacity-50"></div>

        <div class="max-w-md w-full z-10 px-6 lg:px-0">
            <!-- Glassmorphism Card -->
            <div class="bg-slate-900/60 backdrop-blur-2xl border border-white/10 rounded-[2rem] p-8 sm:p-10 shadow-2xl relative overflow-hidden group">
                
                <!-- Inner card highlight -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/[0.05] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                <div class="relative z-10">
                    <!-- Icon / Logo -->
                    <div class="flex justify-center mb-8">
                        <div class="relative">
                            <div class="absolute inset-0 bg-cyan-400 blur-xl opacity-20 animate-pulse"></div>
                            <div class="w-20 h-20 bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-2xl flex items-center justify-center shadow-inner relative z-10">
                                <svg class="w-10 h-10 text-cyan-400 drop-shadow-[0_0_15px_rgba(34,211,238,0.5)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <!-- Admin Badge -->
                            <div class="absolute -bottom-3 -right-3 bg-gradient-to-r from-cyan-500 to-blue-500 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-lg border border-cyan-400/30 uppercase tracking-wider z-20">
                                Admin
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-white tracking-tight mb-2">
                            ផ្ទាំងរដ្ឋបាល
                        </h2>
                        <p class="text-sm text-slate-400">
                            ប្រព័ន្ធរៀបចំកាលវិភាគអប់រំ (TSMS)
                        </p>
                    </div>

                    <div v-if="status" class="mb-6 text-sm font-medium text-cyan-400 bg-cyan-950/50 p-4 rounded-xl border border-cyan-900/50 text-center backdrop-blur-sm">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-5">
                            <!-- Email Input -->
                            <div class="relative group/input">
                                <label for="email" class="block text-sm font-medium text-slate-300 mb-2 ml-1 transition-colors group-focus-within/input:text-cyan-400">អ៉ីមែល (Email)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-slate-500 group-focus-within/input:text-cyan-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                        </svg>
                                    </div>
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="block w-full pl-11 pr-4 py-3.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all duration-300"
                                        v-model="form.email"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="admin@tsms.edu.kh"
                                    />
                                </div>
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <!-- Password Input -->
                            <div class="relative group/input">
                                <label for="password" class="block text-sm font-medium text-slate-300 mb-2 ml-1 transition-colors group-focus-within/input:text-cyan-400">ពាក្យសម្ងាត់ (Password)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-slate-500 group-focus-within/input:text-cyan-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <TextInput
                                        id="password"
                                        type="password"
                                        class="block w-full pl-11 pr-4 py-3.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all duration-300"
                                        v-model="form.password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                    />
                                </div>
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <div class="flex items-center">
                                <Checkbox 
                                    id="remember" 
                                    name="remember" 
                                    v-model:checked="form.remember" 
                                    class="h-4 w-4 bg-slate-900 border-slate-600 rounded text-cyan-500 focus:ring-cyan-500 focus:ring-offset-slate-900" 
                                />
                                <label for="remember" class="ml-2 block text-sm text-slate-400 hover:text-slate-300 cursor-pointer transition-colors">
                                    ចងចាំគណនីនេះ
                                </label>
                            </div>

                            <div class="text-sm">
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="font-medium text-cyan-500 hover:text-cyan-400 transition-colors"
                                >
                                    ភ្លេចពាក្យសម្ងាត់?
                                </Link>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button
                                type="submit"
                                class="relative w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-cyan-500 transition-all duration-300 shadow-[0_0_20px_rgba(34,211,238,0.3)] hover:shadow-[0_0_30px_rgba(34,211,238,0.5)] transform hover:-translate-y-0.5"
                                :class="{ 'opacity-70 cursor-not-allowed transform-none': form.processing }"
                                :disabled="form.processing"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="tracking-wide">ចូលប្រព័ន្ធអ្នកគ្រប់គ្រង</span>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Footer Info -->
                    <div class="mt-8 text-center border-t border-slate-700/50 pt-6">
                        <p class="text-xs text-slate-500">
                            &copy; 2026 TimeTable System Management. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Scoped styles if any extra animations are needed */
</style>
