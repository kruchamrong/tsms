<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrintLayout from '@/Layouts/PrintLayout.vue';

const props = defineProps({
    school: Object,
    classes: Array,
    periods: Array,
    slots: Array,
    teacherSubjectCodes: Object,
    filters: Object,
    shifts: Array
});

const filterForm = useForm({
    level_id: props.filters.level_id || 'lower',
    shift_id: props.filters.shift_id || (props.shifts.length > 0 ? props.shifts[0].id : 1),
});

const printReport = () => {
    window.print();
};

watch(() => [filterForm.level_id, filterForm.shift_id], () => {
    filterForm.get(route('reports.master-timetables'), {
        preserveState: true,
        preserveScroll: true,
    });
});

const days = [
    { id: 1, name: 'ចន្ទ' },
    { id: 2, name: 'អង្គារ' },
    { id: 3, name: 'ពុធ' },
    { id: 4, name: 'ព្រហស្បតិ៍' },
    { id: 5, name: 'សុក្រ' },
    { id: 6, name: 'សៅរ៍' }
];

const getSlot = (classId, dayId, periodId) => {
    if (!props.slots) return null;
    const slotsArray = Array.isArray(props.slots) ? props.slots : Object.values(props.slots);
    return slotsArray.find(s => 
        s.teaching_assignment && 
        s.teaching_assignment.school_class_id == classId && 
        s.day_of_week == dayId && 
        s.period_id == periodId
    );
};

// Generate an array of periods per day for iterating (to support rowspans per day)
const timetableGrid = computed(() => {
    const grid = [];
    days.forEach(day => {
        props.periods.forEach((period, pIndex) => {
            grid.push({
                day: day,
                period: period,
                isFirstPeriodOfDay: pIndex === 0,
                rowspan: props.periods.length
            });
        });
    });
    return grid;
});

const getSubjectCode = (slot) => {
    if (!slot || !slot.teaching_assignment) return '';
    const subjectId = slot.teaching_assignment.subject_id;
    const teacherId = slot.teaching_assignment.teacher_id;
    const key = `${teacherId}_${subjectId}`;
    return props.teacherSubjectCodes[key] || slot.teaching_assignment.subject?.khmer_name || '';
};

const getTeacherName = (slot) => {
    if (!slot || !slot.teaching_assignment) return '';
    return slot.teaching_assignment.teacher?.khmer_name || '';
};

</script>

<template>
    <Head title="របាយការណ៍បោះពុម្ពកាលវិភាគរួម" />

    <div class="min-h-screen bg-gray-100 print:bg-white print:min-h-0">
        <!-- Controls (Hidden on Print) -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 print:hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-amber-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0 1 18 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0 1 18 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25m0-5.25C18 5.004 18.504 4.5 19.125 4.5H20.625M6 13.125v5.25m0-5.25C6 12.504 6.504 12 7.125 12h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0 1 18 14.625v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 13.125v5.25m0-5.25C18 12.504 18.504 12 19.125 12H20.625M6 18.375v-5.25m0 5.25C6 18.996 6.504 19.5 7.125 19.5h9.75c.621 0 1.125-.504 1.125-1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0 1 18 19.875v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 18.375v-5.25m0 5.25C18 18.996 18.504 19.5 19.125 19.5H20.625M4.875 13.5h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 13.5 6 13.996 6 14.625v1.5M4.875 13.5C5.496 13.5 6 12.996 6 12.375v-1.5m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 8.754 6 9.375v1.5" />
                        </svg>
                        ការកំណត់ទិន្នន័យសម្រាប់បោះពុម្ព
                    </h2>
                    
                    <div class="flex items-center gap-3">
                        <Link :href="route('reports.index')" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
                            &larr; ត្រឡប់ក្រោយ
                        </Link>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">កម្រិតថ្នាក់</label>
                        <select v-model="filterForm.level_id" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm sm:text-sm w-48">
                            <option value="lower">អនុវិទ្យាល័យ</option>
                            <option value="upper">វិទ្យាល័យ</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">វេនសិក្សា</label>
                        <select v-model="filterForm.shift_id" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm sm:text-sm w-48">
                            <option value="all">ទាំងអស់</option>
                            <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
                        </select>
                    </div>
                    
                    <button @click="printReport" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium shadow-sm transition-colors ml-auto flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        បោះពុម្ព
                    </button>
                </div>
            </div>
        </div>

        <!-- Print Area -->
        <PrintLayout title="កាលវិភាគបង្រៀនរួម">
            <div class="overflow-x-auto pb-4">
                <table class="w-full border-collapse border border-gray-800 text-[11px] print:text-[10px]">
                    <thead>
                        <tr>
                            <th class="border border-gray-800 p-1 bg-gray-100 font-bold w-12 print:bg-gray-100 print:text-black">ថ្ងៃ</th>
                            <th class="border border-gray-800 p-1 bg-gray-100 font-bold w-16 print:bg-gray-100 print:text-black">ម៉ោង</th>
                            <th v-for="schoolClass in classes" :key="schoolClass.id" class="border border-gray-800 p-1 bg-gray-100 font-bold print:bg-gray-100 print:text-black min-w-[60px]">
                                {{ schoolClass.class_code || schoolClass.name }}
                            </th>
                            <!-- Add empty column if few classes to fill space -->
                            <th v-if="classes.length < 5" class="border border-gray-800 p-1 bg-gray-100 print:bg-gray-100" style="width: auto;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, index) in timetableGrid" :key="index">
                            <td v-if="row.isFirstPeriodOfDay" :rowspan="row.rowspan" class="border border-gray-800 p-1 text-center font-bold bg-gray-50 print:bg-gray-50 align-middle whitespace-nowrap" style="writing-mode: vertical-rl; transform: rotate(180deg);">
                                {{ row.day.name }}
                            </td>
                            
                            <td class="border border-gray-800 p-1 text-center font-bold bg-gray-50 print:bg-gray-50 whitespace-nowrap">
                                <div class="text-[9px]">{{ row.period.start_time.substring(0,5) }}</div>
                                <div class="text-[9px]">-</div>
                                <div class="text-[9px]">{{ row.period.end_time.substring(0,5) }}</div>
                            </td>
                            
                            <td v-for="schoolClass in classes" :key="schoolClass.id" class="border border-gray-800 p-1 text-center h-[35px] align-middle" :class="getSlot(schoolClass.id, row.day.id, row.period.id) ? (getSlot(schoolClass.id, row.day.id, row.period.id).teaching_assignment?.subject?.color || 'bg-gray-100') : ''">
                                <div v-if="getSlot(schoolClass.id, row.day.id, row.period.id)" class="flex flex-col justify-center h-full leading-tight font-semibold">
                                    <span class="text-[11px] print:text-[10px]">{{ getSubjectCode(getSlot(schoolClass.id, row.day.id, row.period.id)) }}</span>
                                    <span class="text-[10px] print:text-[9px] mt-0.5 whitespace-nowrap text-gray-800">{{ getTeacherName(getSlot(schoolClass.id, row.day.id, row.period.id)) }}</span>
                                </div>
                            </td>
                            
                            <!-- Empty cells for spacing if few classes -->
                            <td v-if="classes.length < 5" class="border border-gray-800 p-1"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Signature Section -->
            <div class="mt-8 pt-4 pb-12 flex justify-between px-8 print:mt-6 print:px-4">
                <div class="text-center">
                    <p class="font-moul text-sm mb-16">បានឃើញ និងឯកភាព</p>
                    <p class="font-bold">នាយកសាលា</p>
                </div>
                <div class="text-center">
                    <p class="text-sm mb-2">ធ្វើនៅ {{ school?.province || '...................' }}, ថ្ងៃទី.......ខែ.........ឆ្នាំ..........</p>
                    <p class="font-bold text-sm mb-16">អ្នករៀបចំកាលវិភាគ</p>
                </div>
            </div>
        </PrintLayout>
    </div>
</template>

<style scoped>
@media print {
    @page {
        size: landscape;
        margin: 10mm;
    }
    
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
}
</style>
