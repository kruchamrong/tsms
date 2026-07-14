$vueTemplate = @"
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    workloads: Array,
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard (ផ្ទាំងគ្រប់គ្រងទូទៅ)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stat Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                        <div class="text-sm text-gray-500 uppercase font-bold">Total Teachers</div>
                        <div class="text-3xl font-black text-gray-800">{{ stats.total_teachers }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                        <div class="text-sm text-gray-500 uppercase font-bold">Total Classes</div>
                        <div class="text-3xl font-black text-gray-800">{{ stats.total_classes }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                        <div class="text-sm text-gray-500 uppercase font-bold">Total Subjects</div>
                        <div class="text-3xl font-black text-gray-800">{{ stats.total_subjects }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
                        <div class="text-sm text-gray-500 uppercase font-bold">Teachers on Leave Today</div>
                        <div class="text-3xl font-black text-red-600">{{ stats.teachers_on_leave_today }}</div>
                    </div>
                </div>

                <!-- Teacher Workloads -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Teacher Workloads (បន្ទុកម៉ោងបង្រៀន)</h3>
                    <div v-if="workloads && workloads.length > 0">
                        <div v-for="(workload, index) in workloads" :key="index" class="mb-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">{{ workload.khmer_name }}</span>
                                <span class="text-gray-500">{{ workload.total_hours }} ម៉ោង/សប្តាហ៍</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: Math.min((workload.total_hours / 30) * 100, 100) + '%' }"></div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">* គិតត្រឹមអតិបរមា ៣០ ម៉ោងក្នុងមួយសប្តាហ៍។</div>
                    </div>
                    <div v-else class="text-gray-500 italic">មិនទាន់មានទិន្នន័យម៉ោងបង្រៀនទេ។</div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
"@

Set-Content "resources\js\Pages\Dashboard.vue" $vueTemplate
