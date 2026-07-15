<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const healthData = ref(null);
const loading = ref(true);
const error = ref(null);
const lastUpdated = ref(null);

const fetchHealthData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/admin/health');
        healthData.value = response.data;
        lastUpdated.value = new Date().toLocaleTimeString();
    } catch (err) {
        console.error(err);
        error.value = 'បច្ចុប្បន្នមិនអាចទាញយកទិន្នន័យសុខភាពប្រព័ន្ធបានទេ។ សូមសាកល្បងម្ដងទៀតនៅពេលក្រោយ។';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchHealthData();
    // Refresh every 30 seconds
    setInterval(fetchHealthData, 30000);
});

const getStatusColor = (status) => {
    switch (status) {
        case 'ok': return 'text-green-500 bg-green-50 border-green-200';
        case 'warning': return 'text-yellow-600 bg-yellow-50 border-yellow-200';
        case 'failed': return 'text-red-500 bg-red-50 border-red-200';
        case 'skipped': return 'text-gray-500 bg-gray-50 border-gray-200';
        default: return 'text-gray-500 bg-gray-50 border-gray-200';
    }
};

const getStatusIcon = (status) => {
    switch (status) {
        case 'ok': 
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
        case 'warning': 
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`;
        case 'failed': 
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
        default: 
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 'ok': return 'ដំណើរការល្អ (OK)';
        case 'warning': return 'ព្រមាន (Warning)';
        case 'failed': return 'មានបញ្ហា (Failed)';
        default: return status;
    }
};

</script>

<template>
    <Head title="System Health" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">ស្ថានភាពសុខភាពប្រព័ន្ធ (System Health)</h2>
                <div class="flex items-center gap-4">
                    <span v-if="lastUpdated" class="text-sm text-gray-500">អាប់ដេតចុងក្រោយ៖ {{ lastUpdated }}</span>
                    <button @click="fetchHealthData" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="error" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ error }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="healthData && !loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="check in healthData.checkResults" :key="check.name" 
                         class="bg-white/80 backdrop-blur-md rounded-2xl p-6 shadow-sm border border-gray-100 transform transition duration-300 hover:scale-[1.02] hover:shadow-lg flex flex-col relative overflow-hidden group">
                        
                        <!-- Status Indicator Bar -->
                        <div class="absolute top-0 left-0 w-full h-1" 
                             :class="{'bg-green-500': check.status === 'ok', 'bg-yellow-500': check.status === 'warning', 'bg-red-500': check.status === 'failed'}"></div>
                             
                        <div class="flex items-start justify-between mb-4 mt-2">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 leading-tight mb-1">{{ check.label }}</h3>
                                <p class="text-sm text-gray-500 h-10 line-clamp-2">{{ check.shortSummary }}</p>
                            </div>
                            <div class="p-3 rounded-xl border ml-4 flex-shrink-0 shadow-sm" :class="getStatusColor(check.status)" v-html="getStatusIcon(check.status)">
                            </div>
                        </div>

                        <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase shadow-sm" :class="getStatusColor(check.status)">
                                {{ getStatusText(check.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Skeleton Loader -->
                <div v-if="loading && !healthData" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="i in 6" :key="i" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col animate-pulse">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="h-5 bg-gray-200 rounded w-3/4 mb-3"></div>
                                <div class="h-3 bg-gray-200 rounded w-full mb-1.5"></div>
                                <div class="h-3 bg-gray-200 rounded w-5/6"></div>
                            </div>
                            <div class="w-12 h-12 bg-gray-200 rounded-xl ml-4"></div>
                        </div>
                        <div class="mt-auto pt-4 border-t border-gray-100">
                            <div class="h-5 bg-gray-200 rounded-full w-24"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
