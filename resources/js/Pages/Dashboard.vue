<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: Object,
    workloads: Array,
    alerts: Array,
    totalAlerts: Number,
    activeClasses: Array,
    currentPeriod: String,
});

const searchLiveClass = ref('');

const filteredActiveClasses = computed(() => {
    if (!props.activeClasses) return [];
    if (!searchLiveClass.value) return props.activeClasses;
    
    const search = searchLiveClass.value.toLowerCase();
    return props.activeClasses.filter(cls => 
        cls.class_code.toLowerCase().includes(search) || 
        cls.teacher_name.toLowerCase().includes(search) ||
        cls.subject_name.toLowerCase().includes(search)
    );
});

const currentPage = ref(1);
const itemsPerPage = 6;

const paginatedWorkloads = computed(() => {
    if (!props.workloads) return [];
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return props.workloads.slice(start, end);
});

const totalPages = computed(() => {
    if (!props.workloads) return 0;
    return Math.ceil(props.workloads.length / itemsPerPage);
});
</script>

<template>
    <Head title="ផ្ទាំងគ្រប់គ្រងទូទៅ" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">ផ្ទាំងគ្រប់គ្រងទូទៅ</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stat Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Link :href="route('teachers.index')" class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border-l-4 border-blue-500 hover:shadow-md transition-shadow block group">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-500 font-bold mb-1 group-hover:text-blue-500 transition-colors">គ្រូបង្រៀនសរុប</div>
                                <div class="text-3xl font-black text-gray-800">{{ stats.total_teachers }}</div>
                            </div>
                            <div class="p-3 bg-blue-50 rounded-full text-blue-500 group-hover:bg-blue-100 transition-colors">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                        </div>
                    </Link>
                    <Link :href="route('classes.index')" class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border-l-4 border-green-500 hover:shadow-md transition-shadow block group">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-500 font-bold mb-1 group-hover:text-green-500 transition-colors">ថ្នាក់រៀនសរុប</div>
                                <div class="text-3xl font-black text-gray-800">{{ stats.total_classes }}</div>
                            </div>
                            <div class="p-3 bg-green-50 rounded-full text-green-500 group-hover:bg-green-100 transition-colors">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                        </div>
                    </Link>
                    <Link :href="route('subjects.index')" class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border-l-4 border-yellow-500 hover:shadow-md transition-shadow block group">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-500 font-bold mb-1 group-hover:text-yellow-600 transition-colors">មុខវិជ្ជាសរុប</div>
                                <div class="text-3xl font-black text-gray-800">{{ stats.total_subjects }}</div>
                            </div>
                            <div class="p-3 bg-yellow-50 rounded-full text-yellow-500 group-hover:bg-yellow-100 transition-colors">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                        </div>
                    </Link>
                    <Link :href="route('teacher-leaves.index')" class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border-l-4 border-red-500 hover:shadow-md transition-shadow block group">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-500 font-bold mb-1 group-hover:text-red-500 transition-colors">គ្រូសុំច្បាប់ថ្ងៃនេះ</div>
                                <div class="text-3xl font-black text-red-600">{{ stats.teachers_on_leave_today }}</div>
                            </div>
                            <div class="p-3 bg-red-50 rounded-full text-red-500 group-hover:bg-red-100 transition-colors">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Live Overview -->
                    <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100 flex flex-col h-full">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                            <div class="flex items-center gap-2">
                                <h3 class="text-xl font-bold text-gray-900">កំពុងបង្រៀនម៉ោងនេះ</h3>
                                <span v-if="currentPeriod" class="px-2.5 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-bold animate-pulse flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    {{ currentPeriod }}
                                </span>
                            </div>
                            
                            <!-- Search Filter for Live Classes -->
                            <div v-if="activeClasses && activeClasses.length > 0" class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input type="text" v-model="searchLiveClass" placeholder="ស្វែងរកថ្នាក់ ឬគ្រូ..." class="block w-full pl-9 pr-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 transition-colors">
                            </div>
                        </div>

                        <div v-if="activeClasses && activeClasses.length > 0" class="overflow-y-auto pr-2 custom-scrollbar flex-1" style="max-height: 350px;">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-2">
                                <div v-for="(cls, index) in filteredActiveClasses" :key="index" class="flex items-start gap-4 p-4 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-blue-100 hover:shadow-sm transition-all">
                                    <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg shrink-0">
                                        {{ cls.class_code }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-900 truncate">{{ cls.subject_name }}</h4>
                                        <div class="text-sm text-gray-500 flex items-center gap-1 truncate mt-0.5">
                                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            {{ cls.teacher_name }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            បន្ទប់ {{ cls.room }}
                                        </div>
                                    </div>
                                </div>
                                <div v-if="filteredActiveClasses.length === 0" class="col-span-full py-8 text-center text-gray-400 text-sm">
                                    មិនមានទិន្នន័យស្វែងរកទេ
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-8 px-4 bg-gray-50 rounded-xl border border-dashed border-gray-200 h-full">
                            <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-gray-500 font-medium">មិនមានម៉ោងសិក្សាទេនៅពេលនេះ</p>
                        </div>
                    </div>

                    <!-- Alerts Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100 flex flex-col h-full">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                សាររំលឹក
                                <span v-if="totalAlerts > 0" class="px-2 py-0.5 rounded-full bg-red-100 text-red-600 text-xs font-bold">{{ totalAlerts }}</span>
                            </h3>
                        </div>

                        <div v-if="alerts && alerts.length > 0" class="flex-1 overflow-y-auto pr-2 space-y-3" style="max-height: 300px;">
                            <div v-for="(alert, index) in alerts" :key="index" 
                                class="flex gap-3 p-3 rounded-lg text-sm"
                                :class="alert.type === 'missing_teacher' || alert.type === 'missing_curriculum' ? 'bg-red-50 text-red-800 border border-red-100' : 'bg-amber-50 text-amber-800 border border-amber-100'">
                                <svg v-if="alert.type === 'missing_teacher' || alert.type === 'missing_curriculum'" class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <svg v-else class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>{{ alert.message }}</span>
                            </div>
                            <div v-if="totalAlerts > 10" class="text-center text-xs text-gray-500 pt-2 border-t border-gray-100">
                                និង {{ totalAlerts - 10 }} បញ្ហាផ្សេងទៀត...
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-8 text-center h-full">
                            <div class="w-12 h-12 rounded-full bg-green-100 text-green-500 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <p class="text-gray-500 font-medium">មិនមានបញ្ហាខ្វះខាតទេ</p>
                            <p class="text-xs text-gray-400 mt-1">ការរៀបចំទាំងអស់ដំណើរការល្អ</p>
                        </div>
                    </div>
                </div>

                <!-- Teacher Workloads -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">បន្ទុកម៉ោងបង្រៀនរបស់គ្រូ (Top 12)</h3>
                        <Link :href="route('reports.teacher-workloads')" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center gap-1 transition-colors">
                            មើលទាំងអស់
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </Link>
                    </div>
                    
                    <div v-if="workloads && workloads.length > 0">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Link v-for="(workload, index) in paginatedWorkloads" :key="index" :href="route('teachers.edit', workload.id)"
                                class="relative bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-blue-200 transition-all duration-300 group overflow-hidden block">
                            
                            <!-- Card Background Decoration -->
                            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-10 transition-transform group-hover:scale-150 duration-500"
                                :class="workload.gender === 'F' ? 'bg-pink-500' : 'bg-blue-500'"></div>
                            
                            <div class="flex items-start gap-4 mb-4 relative z-10">
                                <!-- Avatar -->
                                <div class="relative shrink-0">
                                    <template v-if="workload.photo">
                                        <img :src="'/storage/' + workload.photo" class="w-14 h-14 rounded-full object-cover border-2 shadow-sm"
                                            :class="workload.gender === 'F' ? 'border-pink-200' : 'border-blue-200'" />
                                    </template>
                                    <template v-else>
                                        <div class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-sm"
                                             :class="workload.gender === 'F' ? 'bg-gradient-to-br from-pink-400 to-pink-600' : 'bg-gradient-to-br from-blue-400 to-blue-600'">
                                            {{ workload.khmer_name ? workload.khmer_name.charAt(0) : '?' }}
                                        </div>
                                    </template>
                                    
                                    <!-- Rank Badge -->
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-white flex items-center justify-center shadow">
                                        <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold text-white transition-transform group-hover:scale-110"
                                            :class="index < 3 ? 'bg-amber-500' : 'bg-gray-400'">
                                            {{ index + 1 }}
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-base font-bold text-gray-900 truncate group-hover:text-blue-600 transition-colors">{{ workload.khmer_name }}</h4>
                                    <div class="text-xs text-gray-500 truncate mb-1">
                                        <span v-if="workload.phone" class="inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            {{ workload.phone }}
                                        </span>
                                        <span v-else>{{ workload.english_name || 'គ្មានឈ្មោះឡាតាំង' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium"
                                            :class="workload.gender === 'F' ? 'bg-pink-50 text-pink-700 border border-pink-100' : 'bg-blue-50 text-blue-700 border border-blue-100'">
                                            {{ workload.gender === 'F' ? 'ស្រី' : 'ប្រុស' }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-purple-50 text-purple-700 border border-purple-100">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            {{ workload.total_classes }} ថ្នាក់
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress Section -->
                            <div class="mt-4" :title="`នៅខ្វះ ${Math.max(0, 30 - workload.total_hours)} ម៉ោងទៀតទើបពេញកម្រិតអតិបរមា`">
                                <div class="flex justify-between items-end mb-1.5">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider group-hover:text-blue-500 transition-colors">សរុបម៉ោងបង្រៀន</span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-xl font-black text-gray-800" :class="{'text-red-600': workload.total_hours > 30}">{{ workload.total_hours }}</span>
                                        <span class="text-xs font-medium text-gray-500">ម៉ោង</span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden shadow-inner">
                                    <div class="h-2.5 rounded-full transition-all duration-1000 ease-out" 
                                        :class="{
                                            'bg-gradient-to-r from-red-400 to-red-600': workload.total_hours > 30,
                                            'bg-gradient-to-r from-green-400 to-green-600': workload.total_hours <= 30 && workload.total_hours >= 20,
                                            'bg-gradient-to-r from-blue-400 to-blue-600': workload.total_hours < 20
                                        }"
                                        :style="{ width: Math.min((workload.total_hours / 30) * 100, 100) + '%' }">
                                    </div>
                                </div>
                                <div class="flex justify-between mt-1 text-[10px] text-gray-400">
                                    <span>០</span>
                                    <span>៣០ ម៉ោងអតិបរមា</span>
                                </div>
                            </div>
                        </Link>
                        </div>
                        
                        <!-- Pagination Controls -->
                        <div v-if="totalPages > 1" class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-100">
                            <div class="text-sm text-gray-500">
                                បង្ហាញពី {{ ((currentPage - 1) * itemsPerPage) + 1 }} ដល់ {{ Math.min(currentPage * itemsPerPage, workloads.length) }} នៃ {{ workloads.length }} គ្រូបង្រៀនសរុប
                            </div>
                            <div class="flex items-center space-x-1">
                                <button @click="currentPage > 1 ? currentPage-- : null" 
                                    :disabled="currentPage === 1"
                                    class="px-3 py-1.5 border rounded-md text-sm font-medium transition-colors"
                                    :class="currentPage === 1 ? 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:text-blue-600'">
                                    &laquo; មុន
                                </button>
                                
                                <button v-for="page in totalPages" :key="page"
                                    @click="currentPage = page"
                                    class="px-3 py-1.5 border rounded-md text-sm font-medium transition-colors"
                                    :class="currentPage === page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:text-blue-600'">
                                    {{ page }}
                                </button>
                                
                                <button @click="currentPage < totalPages ? currentPage++ : null"
                                    :disabled="currentPage === totalPages"
                                    class="px-3 py-1.5 border rounded-md text-sm font-medium transition-colors"
                                    :class="currentPage === totalPages ? 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:text-blue-600'">
                                    បន្ទាប់ &raquo;
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-12 px-4 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50">
                        <svg class="w-16 h-16 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-gray-500 font-medium">មិនទាន់មានទិន្នន័យម៉ោងបង្រៀនទេ</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
