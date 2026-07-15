<script setup>
import PrintLayout from '@/Layouts/PrintLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    classes: Array,
    school: Object,
});

const chunkedClasses = computed(() => {
    const chunks = [];
    for (let i = 0; i < props.classes.length; i += 4) {
        chunks.push(props.classes.slice(i, i + 4));
    }
    return chunks;
});

const getFirstPhone = (phoneString) => {
    if (!phoneString) return '';
    const match = phoneString.match(/0[0-9]{1,2}[\s\-\.]?[0-9]{3}[\s\-\.]?[0-9]{3,4}/);
    if (match) return match[0];
    return phoneString.substring(0, 12);
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="របាយការណ៍គ្រូបង្រៀនតាមថ្នាក់" />

    <PrintLayout>
        <!-- Print Actions (Hidden when printing) -->
        <div class="no-print p-4 bg-gray-50 border-b flex justify-between items-center mb-8 max-w-5xl mx-auto">
            <div>
                <h2 class="text-lg font-bold text-gray-800">របាយការណ៍គ្រូបង្រៀនតាមថ្នាក់</h2>
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

        <!-- Printable Document -->
        <div class="bg-gray-100 py-8 print:py-0 print:bg-transparent min-h-screen print:min-h-0 flex flex-col items-center">
            
            <template v-for="(chunk, pageIndex) in chunkedClasses" :key="pageIndex">
                <!-- A4 Portrait Container -->
                <div class="w-[210mm] min-h-[297mm] bg-white print:bg-transparent shadow-lg print:shadow-none mb-8 print:mb-0 px-8 py-6 print:w-full print:min-h-[297mm] box-border relative font-khmer flex flex-col"
                     :class="{ 'page-break': pageIndex < chunkedClasses.length - 1 }">
                     
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-2">
                        <div class="text-center font-content">
                            <p class="text-[11px]">មន្ទីរអប់រំ យុវជន និងកីឡា {{ school?.province || 'រាជធានី/ខេត្ត' }}</p>
                            <p class="text-[11px] mt-1">{{ school?.name || 'ឈ្មោះសាលា' }}</p>
                        </div>
                        <!-- Kingdom -->
                        <div class="text-center font-moul">
                            <p class="text-[11px]">ព្រះរាជាណាចក្រកម្ពុជា</p>
                            <p class="text-[11px] mt-1 font-khmer">ជាតិ សាសនា ព្រះមហាក្សត្រ</p>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="text-center mb-4">
                        <h2 class="text-base font-bold font-moul">របាយការណ៍គ្រូបង្រៀនតាមថ្នាក់</h2>
                        <p class="text-[11px] mt-1 font-khmer">ឆ្នាំសិក្សា៖ ...........................................................</p>
                    </div>

                    <!-- Content: 4 classes grid -->
                    <div class="grid grid-cols-2 gap-x-8 gap-y-2 flex-grow print:grid-cols-2">
                        <template v-for="schoolClass in chunk" :key="schoolClass.id">
                            <div class="break-inside-avoid">
                                <h3 class="text-[13px] font-bold bg-gray-200 px-2 py-1.5 border border-gray-900 font-moul text-center">ថ្នាក់ទី {{ schoolClass.name }}</h3>
                                <table class="min-w-full border-collapse border border-gray-900">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="border border-gray-900 px-1 py-1 text-center text-[10px] font-bold w-8">ល.រ</th>
                                            <th class="border border-gray-900 px-1 py-1 text-center text-[10px] font-bold">មុខវិជ្ជា</th>
                                            <th class="border border-gray-900 px-1 py-1 text-center text-[10px] font-bold">គ្រូបង្រៀន</th>
                                            <th class="border border-gray-900 px-1 py-1 text-center text-[10px] font-bold">លេខទូរសព្ទ</th>
                                            <th class="border border-gray-900 px-1 py-1 text-center text-[10px] font-bold w-10">ម៉ោង</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(assign, aIndex) in schoolClass.assignments" :key="aIndex">
                                            <td class="border border-gray-900 px-1 py-0.5 text-center text-[10px]">{{ aIndex + 1 }}</td>
                                            <td class="border border-gray-900 px-1 py-0.5 text-left text-[10px]">{{ assign.subject_name }}</td>
                                            <td class="border border-gray-900 px-1 py-0.5 text-left text-[10px]">{{ assign.teacher_name }}</td>
                                            <td class="border border-gray-900 px-1 py-0.5 text-center text-[10px]">{{ getFirstPhone(assign.teacher_phone) }}</td>
                                            <td class="border border-gray-900 px-1 py-0.5 text-center text-[10px]">{{ assign.weekly_hours }}</td>
                                        </tr>
                                        <tr v-if="schoolClass.assignments.length === 0">
                                            <td colspan="5" class="border border-gray-900 px-1 py-2 text-center text-gray-500 text-[10px]">មិនទាន់មានគ្រូបង្រៀន</td>
                                        </tr>
                                        <!-- Total Hours for this class -->
                                        <tr v-if="schoolClass.assignments.length > 0" class="bg-gray-100 font-bold">
                                            <td colspan="4" class="border border-gray-900 px-1 py-0.5 text-right text-[10px]">សរុបម៉ោងសិក្សា៖</td>
                                            <td class="border border-gray-900 px-1 py-0.5 text-center text-[10px]">{{ schoolClass.assignments.reduce((sum, a) => sum + parseInt(a.weekly_hours || 0), 0) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>

                    <!-- Footer Signatures -->
                    <div class="mt-12 pt-4 pb-16 flex justify-between px-10 print:px-8 shrink-0">
                        <div class="text-center ml-[2cm]">
                            <p class="font-bold text-sm mb-2">បានឃើញ និងឯកភាព</p>
                            <p class="font-bold font-moul text-sm mb-24">នាយកសាលា</p>
                        </div>
                        <div class="text-center mr-8">
                            <p class="text-sm mb-2 text-gray-800">ធ្វើនៅ {{ school?.province || '...........................' }}, ថ្ងៃទី...........ខែ...........ឆ្នាំ...........</p>
                            <p class="font-bold text-sm mb-24 text-gray-800">អ្នករៀបចំកាលវិភាគ</p>
                        </div>
                    </div>
                </div>
            </template>
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

/* Ensure no page break inside a class table */
.break-inside-avoid {
    page-break-inside: avoid;
    break-inside: avoid;
}

.page-break {
    page-break-after: always;
    break-after: page;
}

@media print {
    .no-print {
        display: none !important;
    }
    
    @page {
        margin: 0;
        size: A4 portrait;
    }
}
</style>
