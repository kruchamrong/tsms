<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    recentActivities: Array,
});
</script>

<template>
    <Head title="ផ្ទាំងដើម (Super Admin)" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">ផ្ទាំងគ្រប់គ្រងទូទៅ (Super Admin)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stats Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- Total Schools -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-row items-center border-l-4 border-indigo-500 hover:shadow-md transition-shadow">
                        <div class="p-6 flex-1">
                            <div class="text-sm font-medium text-gray-500 mb-1">សាលារៀនសរុប</div>
                            <div class="text-3xl font-bold text-gray-900">{{ stats.total_schools }}</div>
                        </div>
                        <div class="p-6">
                            <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Users -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-row items-center border-l-4 border-blue-500 hover:shadow-md transition-shadow">
                        <div class="p-6 flex-1">
                            <div class="text-sm font-medium text-gray-500 mb-1">គណនីសាលាសរុប</div>
                            <div class="text-3xl font-bold text-gray-900">{{ stats.total_users }}</div>
                        </div>
                        <div class="p-6">
                            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Subject Templates -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-row items-center border-l-4 border-amber-500 hover:shadow-md transition-shadow">
                        <div class="p-6 flex-1">
                            <div class="text-sm font-medium text-gray-500 mb-1">មុខវិជ្ជាគំរូសរុប</div>
                            <div class="text-3xl font-bold text-gray-900">{{ stats.total_subject_templates }}</div>
                        </div>
                        <div class="p-6">
                            <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center text-amber-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Curricula Templates -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-row items-center border-l-4 border-emerald-500 hover:shadow-md transition-shadow">
                        <div class="p-6 flex-1">
                            <div class="text-sm font-medium text-gray-500 mb-1">កម្មវិធីសិក្សាគំរូ</div>
                            <div class="text-3xl font-bold text-gray-900">{{ stats.total_curricula_templates }}</div>
                        </div>
                        <div class="p-6">
                            <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- System Health Link -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 flex items-center justify-between border-b border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900">ស្ថានភាពប្រព័ន្ធ (System Health)</h3>
                            <Link :href="route('admin.health.view')" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">មើលលម្អិត &rarr;</Link>
                        </div>
                        <div class="p-8 text-center bg-gray-50">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">ប្រព័ន្ធដំណើរការធម្មតា</h4>
                            <p class="text-gray-500">សេវាកម្មទាំងអស់រួមមាន Database, Cache, និង Storage កំពុងដំណើរការដោយរលូន។</p>
                            <div class="mt-6">
                                <Link :href="route('admin.health.view')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    ពិនិត្យ System Health
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col h-full">
                        <div class="p-6 flex items-center justify-between border-b border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900">សកម្មភាពថ្មីៗ (Recent Activities)</h3>
                            <Link :href="route('admin.audits.index')" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">មើលទាំងអស់ &rarr;</Link>
                        </div>
                        <div class="p-0 flex-1 overflow-y-auto max-h-[400px]">
                            <ul class="divide-y divide-gray-100">
                                <li v-for="activity in recentActivities" :key="activity.id" class="p-4 hover:bg-gray-50">
                                    <div class="flex space-x-3">
                                        <div class="flex-shrink-0">
                                            <span class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center ring-8 ring-white">
                                                <svg v-if="activity.event === 'created'" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                <svg v-else-if="activity.event === 'updated'" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                                <svg v-else-if="activity.event === 'deleted'" class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <svg v-else class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    បាន <span class="font-medium text-gray-900">{{ activity.event }}</span> ទិន្នន័យ 
                                                    <span class="font-medium text-gray-900">{{ activity.log_name }}</span>
                                                </p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time :datetime="activity.created_at">{{ new Date(activity.created_at).toLocaleString('km-KH') }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li v-if="recentActivities.length === 0" class="p-6 text-center text-gray-500">
                                    មិនមានសកម្មភាពថ្មីៗទេ
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
