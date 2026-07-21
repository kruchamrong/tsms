<script setup>
import PrintLayout from '@/Layouts/PrintLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    classes: Array,
    grades: Array,
    shifts: Array,
    filters: Object,
    school: Object,
});

const search = ref(props.filters?.search || '');
const grade_id = ref(props.filters?.grade_id || '');
const shift_id = ref(props.filters?.shift_id || '');

watch([search, grade_id, shift_id], () => {
    router.get(route('reports.homeroom-teachers'), {
        search: search.value,
        grade_id: grade_id.value,
        shift_id: shift_id.value,
    }, { preserveState: true, replace: true });
});

const formatPhone = (phone) => {
    if (!phone) return '-';
    return phone.replace(/ (Smart|Cellcard|Metfone|smart|cellcard|metfone)/g, '\n$1');
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="របាយការណ៍គ្រូបន្ទុកថ្នាក់រៀន" />

    <PrintLayout>
        <!-- Print Actions (Hidden when printing) -->
        <div class="no-print max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-100 pb-4 mb-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-amber-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        ការកំណត់ទិន្នន័យរបាយការណ៍គ្រូបន្ទុកថ្នាក់រៀន
                    </h2>
                    
                    <div class="flex items-center gap-3">
                        <Link :href="route('reports.index')" class="text-sm text-gray-500 hover:text-gray-700 font-medium flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                            </svg>
                            ត្រឡប់ក្រោយ
                        </Link>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">ស្វែងរក</label>
                        <input type="text" v-model="search" placeholder="ស្វែងរកតាមឈ្មោះ ឬថ្នាក់រៀន..." class="w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">កម្រិតថ្នាក់</label>
                        <select v-model="grade_id" class="w-full sm:w-48 border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm sm:text-sm">
                            <option value="">គ្រប់កម្រិតថ្នាក់</option>
                            <option v-for="grade in grades" :key="grade.id" :value="grade.id">{{ grade.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">វេនសិក្សា</label>
                        <select v-model="shift_id" class="w-full sm:w-48 border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm sm:text-sm">
                            <option value="">គ្រប់វេន</option>
                            <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
                        </select>
                    </div>
                    
                    <button @click="printReport" class="ml-auto inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm h-[38px]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        បោះពុម្ពរបាយការណ៍
                    </button>
                </div>
            </div>
        </div>

        <div class="py-8 print:py-0 bg-gray-100 print:bg-transparent min-h-screen print:min-h-0 flex justify-center print:block">
            <!-- A4 Portrait Paper Container -->
            <div class="w-[210mm] print:w-full min-h-[297mm] print:min-h-0 bg-white print:bg-transparent shadow-xl print:shadow-none p-10 print:p-0 font-khmer">
            
            <!-- Header -->
            <div class="flex justify-between items-start mb-6 print:mb-4 px-4 print:px-0">
                <!-- Ministry -->
                <div class="text-center font-content text-sm leading-relaxed pt-9">
                    <p>មន្ទីរអប់រំ យុវជន និងកីឡា {{ school?.province || 'រាជធានី/ខេត្ត' }}</p>
                    <p class="mt-0.5 font-bold">{{ school?.name || 'ឈ្មោះសាលា' }}</p>
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
                <h2 class="text-2xl print:text-xl font-bold font-moul tracking-wide">បញ្ជីរាយនាមគ្រូបន្ទុកថ្នាក់រៀន</h2>
                <p class="text-sm mt-2 font-khmer">ឆ្នាំសិក្សា៖ .............................................</p>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto px-4 print:px-0">
                <table class="w-full border-collapse border border-gray-900">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="border border-gray-900 px-1 py-2 text-center text-[11px] font-bold font-moul w-8">ល.រ</th>
                            <th class="border border-gray-900 px-1 py-2 text-center text-[11px] font-bold font-moul w-16">អត្តលេខ</th>
                            <th class="border border-gray-900 px-2 py-2 text-center text-[11px] font-bold font-moul w-36">គោត្តនាមនិងនាមខ្លួន</th>
                            <th class="border border-gray-900 px-1 py-2 text-center text-[11px] font-bold font-moul w-10">ភេទ</th>
                            <th class="border border-gray-900 px-2 py-2 text-center text-[11px] font-bold font-moul w-16">ថ្នាក់បន្ទុក</th>
                            <th class="border border-gray-900 px-1 py-2 text-center text-[11px] font-bold font-moul w-14">សិស្សសរុប</th>
                            <th class="border border-gray-900 px-1 py-2 text-center text-[11px] font-bold font-moul w-12">បន្ទប់រៀន</th>
                            <th class="border border-gray-900 px-2 py-2 text-center text-[11px] font-bold font-moul w-14">វេនសិក្សា</th>
                            <th class="border border-gray-900 px-2 py-2 text-center text-[11px] font-bold font-moul w-36">លេខទូរសព្ទ</th>
                            <th class="border border-gray-900 px-2 py-2 text-center text-[11px] font-bold font-moul">ផ្សេងៗ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(cls, index) in classes" :key="cls.id">
                            <td class="border border-gray-900 px-1 py-1.5 text-center text-[11.5px]">{{ index + 1 }}</td>
                            <td class="border border-gray-900 px-1 py-1.5 text-center text-[11.5px] text-gray-700">{{ cls.homeroom_teacher?.teacher_code || '-' }}</td>
                            <td class="border border-gray-900 px-2 py-1.5 text-left text-[11.5px] font-bold text-gray-800">{{ cls.homeroom_teacher?.khmer_name || '-' }}</td>
                            <td class="border border-gray-900 px-1 py-1.5 text-center text-[11.5px]">{{ cls.homeroom_teacher?.gender === 'F' ? 'ស្រី' : 'ប្រុស' }}</td>
                            <td class="border border-gray-900 px-2 py-1.5 text-center text-[11.5px] font-bold text-gray-800">{{ cls.class_code }}</td>
                            <td class="border border-gray-900 px-1 py-1.5 text-center text-[11.5px]">{{ cls.student_count || 0 }}</td>
                            <td class="border border-gray-900 px-1 py-1.5 text-center text-[11.5px]">{{ cls.room?.room_name?.replace(/បន្ទប់(?:លេខ)?\s*/g, '') || '-' }}</td>
                            <td class="border border-gray-900 px-1 py-1.5 text-center text-[11.5px]">{{ cls.shift?.name || '-' }}</td>
                            <td class="border border-gray-900 px-2 py-1.5 text-center text-[11.5px] whitespace-pre leading-relaxed">{{ formatPhone(cls.homeroom_teacher?.phone) }}</td>
                            <td class="border border-gray-900 px-1 py-1.5 text-center text-[11.5px]"></td>
                        </tr>
                        <tr v-if="classes.length === 0">
                            <td colspan="10" class="border border-gray-900 px-4 py-8 text-center text-gray-500">មិនទាន់មានទិន្នន័យគ្រូបន្ទុកថ្នាក់ឡើយ</td>
                        </tr>
                        <!-- Total Row -->
                        <tr v-if="classes.length > 0" class="bg-gray-200/60 font-bold">
                            <td colspan="4" class="border border-gray-900 px-4 py-2 text-right text-sm font-moul">សរុបរួម៖</td>
                            <td class="border border-gray-900 px-1 py-2 text-center text-sm whitespace-nowrap">{{ classes.length }} ថ្នាក់</td>
                            <td class="border border-gray-900 px-1 py-2 text-center text-sm whitespace-nowrap">{{ classes.reduce((sum, cls) => sum + (parseInt(cls.student_count) || 0), 0) }} នាក់</td>
                            <td colspan="4" class="border border-gray-900 px-2 py-2 text-right text-sm"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Signatures -->
            <div class="mt-12 pt-4 pb-16 flex justify-between px-10 print:px-8">
                <div class="text-center ml-[2cm]">
                    <p class="font-bold text-sm mb-2">បានឃើញ និងឯកភាព</p>
                    <p class="font-bold font-moul text-sm mb-24">នាយកសាលា</p>
                </div>
                <div class="text-center">
                    <p class="text-sm mb-2 text-gray-800">ធ្វើនៅ {{ school?.province || '...........................' }}, ថ្ងៃទី...........ខែ...........ឆ្នាំ...........</p>
                    <p class="font-bold text-sm mb-24 text-gray-800">អ្នករៀបចំរបាយការណ៍</p>
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
