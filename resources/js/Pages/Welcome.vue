<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome to TSMS" />
    <div class="min-h-screen font-sans text-gray-900 bg-gradient-to-br from-indigo-50 via-white to-blue-50 selection:bg-blue-500 selection:text-white overflow-x-hidden">
        
        <!-- Navigation -->
        <nav class="absolute w-full top-0 z-50 bg-white/70 backdrop-blur-md border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <span class="font-extrabold text-2xl bg-clip-text text-transparent bg-gradient-to-r from-blue-700 to-indigo-700 tracking-tight">TSMS</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-6">
                        <a v-if="$page.props.auth.user" :href="route('dashboard')" class="text-gray-600 hover:text-blue-600 font-semibold transition">
                            ផ្ទាំងគ្រប់គ្រង (Dashboard)
                        </a>
                        <template v-else>
                            <a :href="route('login')" class="hidden sm:inline-block text-gray-600 hover:text-blue-600 font-semibold transition">
                                ចូលគណនី (Login)
                            </a>
                            <a v-if="canRegister" :href="route('register')" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-2.5 rounded-full font-semibold transition-all duration-300 shadow-lg shadow-blue-500/30 hover:shadow-blue-600/40 hover:-translate-y-0.5">
                                ចុះឈ្មោះសាកល្បង
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative pt-32 pb-20 sm:pt-40 sm:pb-24 lg:pb-32 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="lg:grid lg:grid-cols-12 lg:gap-16 items-center">
                    
                    <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left z-10">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 text-green-700 text-sm font-semibold border border-green-100 mb-6">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            ជំនាន់ថ្មី v2.0
                        </span>
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl leading-[1.3] pb-2">
                            <span class="block mb-2">រៀបចំកាលវិភាគគ្រូ</span>
                            <span class="block bg-clip-text text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 pb-2 whitespace-nowrap">បានយ៉ាងងាយស្រួល និងរហ័ស</span>
                        </h1>
                        <p class="mt-6 text-lg text-gray-600 sm:text-xl leading-relaxed">
                            ដំណោះស្រាយដ៏ល្អឥតខ្ចោះក្នុងការចាត់ចែងម៉ោងបង្រៀន បំបាត់បញ្ហាជាន់ម៉ោង និងសន្សំសំចៃពេលវេលាបានរហូតដល់ទៅ ៥០%។
                        </p>
                        <div class="mt-10 sm:flex sm:justify-center lg:justify-start gap-4">
                            <a :href="route('register')" class="w-full sm:w-auto flex items-center justify-center px-8 py-4 border border-transparent text-lg font-bold rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-xl shadow-blue-500/30 hover:shadow-blue-600/40 hover:-translate-y-1">
                                ចាប់ផ្តើមឥឡូវនេះ
                                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                            </a>
                            <a href="#pricing" class="mt-4 sm:mt-0 w-full sm:w-auto flex items-center justify-center px-8 py-4 border-2 border-gray-200 text-lg font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all duration-300 hover:-translate-y-1">
                                មើលតារាងតម្លៃ
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-16 sm:mt-24 lg:mt-0 lg:col-span-6 relative z-10">
                        <div class="relative mx-auto w-full rounded-2xl shadow-2xl lg:max-w-md overflow-hidden bg-white ring-1 ring-black/5">
                            <div class="absolute inset-0 bg-gradient-to-tr from-blue-100 to-purple-50 opacity-50"></div>
                            <img class="relative w-full h-64 sm:h-80 lg:h-96 object-cover rounded-2xl transform hover:scale-105 transition-transform duration-700 ease-out" src="/images/hero_image.jpg" alt="Timetable UI Dashboard" />
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/50 to-transparent rounded-2xl"></div>
                            
                            <!-- Floating Card Overlay -->
                            <div class="absolute bottom-6 left-6 right-6 bg-white/90 backdrop-blur-md rounded-xl p-4 shadow-xl border border-white/20 transform translate-y-2 hover:translate-y-0 transition-transform duration-300 group cursor-default">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">ងាយស្រួលប្រើប្រាស់បំផុត</p>
                                        <p class="text-sm text-gray-500">មិនទាមទារជំនាញកុំព្យូទ័រខ្ពស់</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Decorative Blobs -->
                        <div class="absolute -top-10 -right-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
                        <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse" style="animation-delay: 2000ms;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Section -->
        <div id="pricing" class="bg-white py-24 relative overflow-hidden border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="sm:flex sm:flex-col sm:align-center mb-16">
                    <h2 class="text-4xl font-extrabold text-gray-900 sm:text-center">កញ្ចប់សេវាកម្ម</h2>
                    <p class="mt-5 text-xl text-gray-500 sm:text-center max-w-2xl mx-auto">
                        <span class="block">ជ្រើសរើសកញ្ចប់ដែលស័ក្តិសមបំផុត សម្រាប់សាលារៀនរបស់អ្នក។</span>
                        <span class="block mt-1">ការវិនិយោគដ៏ឆ្លាតវៃ ចំណាយតិច តែទទួលបានប្រសិទ្ធភាពការងារទ្វេដង!</span>
                    </p>
                </div>
                
                <div class="space-y-12 lg:space-y-0 lg:grid lg:grid-cols-3 lg:gap-x-8">
                    <!-- Basic Plan -->
                    <div class="relative p-8 bg-gray-50 border border-gray-100 rounded-3xl shadow-sm flex flex-col hover:shadow-xl transition-all duration-300">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">Basic</h3>
                            <p class="mt-4 flex items-baseline text-gray-900">
                                <span class="text-5xl font-extrabold tracking-tight">$60</span>
                                <span class="ml-1 text-xl font-semibold text-gray-500">/ឆ្នាំ</span>
                            </p>
                            <p class="mt-6 text-gray-600">ស័ក្តិសមសម្រាប់សាលាតូចៗ ដែលមានចំនួនគ្រូបង្រៀនតិច។</p>
                            <ul role="list" class="mt-6 space-y-6">
                                <li class="flex items-start">
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="ml-3 text-gray-700">ចំនួនគ្រូបង្រៀន <b>ត្រឹម ៣០នាក់</b></span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="ml-3 text-gray-700">រៀបចំកាលវិភាគបង្រៀន (Timetables)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="ml-3 text-gray-700">គ្រប់គ្រងការសុំច្បាប់របស់គ្រូ (Leave)</span>
                                </li>
                            </ul>
                        </div>
                        <a :href="route('register')" class="mt-8 block w-full py-3 px-6 bg-white border border-gray-300 rounded-xl text-center font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                            ជ្រើសរើសកញ្ចប់នេះ
                        </a>
                    </div>

                    <!-- Pro Plan -->
                    <div class="relative p-8 bg-gradient-to-b from-blue-600 to-indigo-700 rounded-3xl shadow-2xl shadow-blue-500/20 flex flex-col transform lg:-translate-y-4 hover:-translate-y-6 transition-all duration-300">
                        <div class="absolute top-0 right-6 -translate-y-1/2">
                            <span class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-4 py-1.5 rounded-full text-sm font-bold uppercase tracking-wide shadow-lg">
                                ពេញនិយមបំផុត
                            </span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-white">Pro</h3>
                            <p class="mt-4 flex items-baseline text-white">
                                <span class="text-5xl font-extrabold tracking-tight">$150</span>
                                <span class="ml-1 text-xl font-semibold text-blue-200">/ឆ្នាំ</span>
                            </p>
                            <p class="mt-6 text-blue-100">ជម្រើសដ៏ល្អឥតខ្ចោះសម្រាប់សាលាមធ្យម។</p>
                            <ul role="list" class="mt-6 space-y-6">
                                <li class="flex items-start">
                                    <svg class="flex-shrink-0 w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="ml-3 text-white">ចំនួនគ្រូបង្រៀន <b>រហូតដល់ ៨០នាក់</b></span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="flex-shrink-0 w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="ml-3 text-white">មុខងារទាំងអស់ក្នុង Basic</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="flex-shrink-0 w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="ml-3 text-white">ប្រព័ន្ធគ្រប់គ្រងគ្រូជំនួសស្វ័យប្រវត្តិ (Substitutes)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="flex-shrink-0 w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="ml-3 text-white">របាយការណ៍កម្រិតខ្ពស់ (Advanced Reports)</span>
                                </li>
                            </ul>
                        </div>
                        <a :href="route('register')" class="mt-8 block w-full py-3 px-6 bg-white rounded-xl text-center font-bold text-blue-600 hover:bg-gray-50 transition-colors shadow-lg shadow-white/10">
                            ជ្រើសរើសកញ្ចប់នេះ
                        </a>
                    </div>

                    <!-- Enterprise Plan -->
                    <div class="relative p-8 bg-gray-50 border border-gray-100 rounded-3xl shadow-sm flex flex-col hover:shadow-xl transition-all duration-300">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">Enterprise</h3>
                            <p class="mt-4 flex items-baseline text-gray-900">
                                <span class="text-5xl font-extrabold tracking-tight">$250</span>
                                <span class="ml-1 text-xl font-semibold text-gray-500">/ឆ្នាំ</span>
                            </p>
                            <p class="mt-6 text-gray-600">សម្រាប់សាលាធំៗ ដែលមានសាខាច្រើន និងគ្រូខ្ទង់រយនាក់។</p>
                            <ul role="list" class="mt-6 space-y-6">
                                <li class="flex items-start">
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="ml-3 text-gray-700">ចំនួនគ្រូបង្រៀន <b>គ្មានដែនកំណត់</b></span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="ml-3 text-gray-700">មុខងារទាំងអស់ក្នុង Pro</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="ml-3 text-gray-700">ផ្តល់សេវាកម្មជួយគាំទ្រ 24/7 (Priority Support)</span>
                                </li>
                            </ul>
                        </div>
                        <a href="https://t.me/PICHCHAMRONG" target="_blank" class="mt-8 block w-full py-3 px-6 bg-white border border-gray-300 rounded-xl text-center font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                            ទាក់ទងផ្នែកលក់
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 flex flex-col items-center justify-center gap-3">
                <div class="flex items-center gap-2 text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span class="font-bold text-2xl text-gray-900">TSMS</span>
                </div>
                <p class="text-center text-sm text-gray-500">
                    &copy; 2026 TSMS (Timetable & School Management System). រក្សាសិទ្ធិគ្រប់យ៉ាង។
                </p>
            </div>
        </footer>

        <!-- Floating Telegram Button -->
        <a href="https://t.me/PICHCHAMRONG" target="_blank" class="fixed bottom-6 right-6 z-50 bg-[#229ED9] text-white p-4 rounded-full shadow-lg hover:shadow-xl hover:scale-110 transition-all duration-300 flex items-center justify-center group">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
            <!-- Tooltip -->
            <span class="absolute right-full mr-4 bg-white text-gray-900 px-4 py-2 rounded-lg text-sm font-bold opacity-0 group-hover:opacity-100 transition-opacity shadow-md pointer-events-none whitespace-nowrap">
                ទាក់ទងមកយើងខ្ញុំតាម Telegram
            </span>
        </a>
    </div>
</template>
