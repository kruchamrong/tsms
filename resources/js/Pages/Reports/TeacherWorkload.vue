<script setup>
import PrintLayout from '@/Layouts/PrintLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    workloads: Array,
    school: Object,
});

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="របាយការណ៍បន្ទុកម៉ោងបង្រៀន" />

    <PrintLayout>
        <!-- Print Actions (Hidden when printing) -->
        <div class="no-print p-4 bg-gray-50 border-b flex justify-between items-center mb-8 max-w-[210mm] mx-auto">
            <div>
                <h2 class="text-lg font-bold text-gray-800">របាយការណ៍បន្ទុកម៉ោងបង្រៀន</h2>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="route('reports.index')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                    </svg>
                    ត្រឡប់ក្រោយ
                </Link>
                <button @click="printReport" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    បោះពុម្ពរបាយការណ៍ (Print)
                </button>
            </div>
        </div>

        <div class="py-8 print:py-0 bg-gray-100 print:bg-transparent min-h-screen print:min-h-0 flex justify-center print:block">
            <!-- A4 Portrait Paper Container -->
            <div class="w-[210mm] print:w-full min-h-[297mm] print:min-h-0 bg-white print:bg-transparent shadow-xl print:shadow-none p-10 print:p-0 font-khmer">
            
            <!-- Header -->
            <div class="flex justify-between items-start mb-6 print:mb-4 px-4 print:px-0">
                <!-- Ministry -->
                <div class="text-center font-content text-sm leading-relaxed pt-9">
                    <p>មន្ទីរអប់រំ យុវជន និងកីឡា រាជធានី/ខេត្ត</p>
                    <p class="mt-0.5 font-bold">{{ school?.name || 'វិទ្យាល័យជាស៊ីមសន្ធរម៉ុក' }}</p>
                </div>
                
                <!-- Kingdom -->
                <div class="text-center font-moul">
                    <p class="text-[17px]">ព្រះរាជាណាចក្រកម្ពុជា</p>
                    <p class="text-sm mt-1.5">ជាតិ សាសនា ព្រះមហាក្សត្រ</p>
                    <div class="flex justify-center items-center mt-2 opacity-80">
                        <div class="w-10 border-t-2 border-gray-900"></div>
                        <div class="mx-2 text-gray-900 text-lg font-serif">❧</div>
                        <div class="w-10 border-t-2 border-gray-900"></div>
                    </div>
                </div>
            </div>

            <!-- Title -->
            <div class="text-center mb-8 print:mb-6">
                <h2 class="text-2xl print:text-xl font-bold font-moul tracking-wide">របាយការណ៍បន្ទុកម៉ោងបង្រៀន</h2>
                <p class="text-sm mt-2 font-khmer">ឆ្នាំសិក្សា៖ .............................................</p>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto px-4 print:px-0">
                <table class="w-full border-collapse border border-gray-900">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="border border-gray-900 px-1 py-2 text-center text-[11px] font-bold font-moul w-10">ល.រ</th>
                            <th class="border border-gray-900 px-2 py-2 text-center text-[11px] font-bold font-moul w-36">គោត្តនាមនិងនាម</th>
                            <th class="border border-gray-900 px-1 py-2 text-center text-[11px] font-bold font-moul w-10">ភេទ</th>
                            <th class="border border-gray-900 px-2 py-2 text-center text-[11px] font-bold font-moul w-36">មុខវិជ្ជាបង្រៀន</th>
                            <th class="border border-gray-900 px-2 py-2 text-center text-[11px] font-bold font-moul">ថ្នាក់រៀន</th>
                            <th class="border border-gray-900 px-1 py-2 text-center text-[11px] font-bold font-moul w-14">ម៉ោងសរុប</th>
                            <th class="border border-gray-900 px-2 py-2 text-center text-[11px] font-bold font-moul w-14">ផ្សេងៗ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(workload, index) in workloads" :key="workload.id">
                            <td class="border border-gray-900 px-1 py-1.5 text-center text-[11.5px]">{{ index + 1 }}</td>
                            <td class="border border-gray-900 px-2 py-1.5 text-left text-[11.5px] font-bold text-gray-800">{{ workload.khmer_name }}</td>
                            <td class="border border-gray-900 px-1 py-1.5 text-center text-[11.5px]">{{ workload.gender === 'F' ? 'ស្រី' : 'ប្រុស' }}</td>
                            <td class="border border-gray-900 px-2 py-1.5 text-center text-[11.5px] font-bold text-gray-800">
                                <div class="flex flex-wrap gap-1 justify-center">
                                    <span v-for="(assign, aIndex) in Array.from(new Set(workload.assignments.map(a => a.subject_name)))" :key="aIndex">
                                        {{ assign }}<span v-if="aIndex < Array.from(new Set(workload.assignments.map(a => a.subject_name))).length - 1">,</span>
                                    </span>
                                </div>
                            </td>
                            <td class="border border-gray-900 px-2 py-1.5 text-center text-[11.5px] text-gray-700 leading-tight">
                                <div class="flex flex-wrap gap-1 justify-center">
                                    <span v-for="(cls, cIndex) in Array.from(new Set(workload.assignments.map(a => a.class_name)))" :key="cIndex">
                                        {{ cls }}<span v-if="cIndex < Array.from(new Set(workload.assignments.map(a => a.class_name))).length - 1">,</span>
                                    </span>
                                </div>
                            </td>
                            <td class="border border-gray-900 px-1 py-1.5 text-center font-bold text-[11.5px] bg-gray-50/50">{{ workload.total_hours }}</td>
                            <td class="border border-gray-900 px-2 py-1.5 text-center text-[11.5px]"></td>
                        </tr>
                        <tr v-if="workloads.length === 0">
                            <td colspan="7" class="border border-gray-900 px-4 py-8 text-center text-gray-500">មិនទាន់មានទិន្នន័យបន្ទុកម៉ោងបង្រៀនឡើយ</td>
                        </tr>
                        <!-- Total Row -->
                        <tr v-if="workloads.length > 0" class="bg-gray-200/60 font-bold">
                            <td colspan="5" class="border border-gray-900 px-4 py-2 text-right text-sm font-moul">សរុបរួម៖</td>
                            <td class="border border-gray-900 px-4 py-2 text-center text-sm">{{ workloads.reduce((sum, w) => sum + parseInt(w.total_hours), 0) }}</td>
                            <td class="border border-gray-900 px-4 py-2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Signatures -->
            <div class="mt-8 print:mt-4 flex justify-between px-4 print:px-0">
                <div class="text-center ml-[2cm]">
                    <p class="font-bold mb-1 print:mb-0.5 text-sm print:text-xs">បានឃើញ និងឯកភាព</p>
                    <p class="font-bold font-moul mb-12 print:mb-8 text-sm print:text-xs">នាយកសាលា</p>
                </div>
                <div class="text-center w-80 mr-8">
                    <p class="mb-1 print:mb-0.5 text-center text-sm print:text-xs">រាជធានី.....................ថ្ងៃទី...... ខែ...... ឆ្នាំ......</p>
                    <p class="font-bold mb-10 print:mb-8 text-center text-sm print:text-xs">អ្នករៀបចំកាលវិភាគ</p>
                    <p class="font-bold text-center border-b border-dashed border-gray-400 mb-1">&nbsp;</p>
                </div>
            </div>

        </div>
        </div>
    </PrintLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Moul&display=swap');

.font-khmer {
    font-family: 'Battambang', cursive;
}
.font-moul {
    font-family: 'Moul', 'Khmer OS Muol Light', cursive;
}
.font-content {
    font-family: 'Khmer OS Content', 'Battambang', sans-serif;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 1cm;
    }
    .no-print {
        display: none !important;
    }
}
</style>
