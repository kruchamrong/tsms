<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PrintLayout from '@/Layouts/PrintLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    school: Object,
    classes: Array,
    periods: Array
});

const printReport = () => {
    window.print();
};

const daysOfWeek = [
    { id: 1, name: 'ច័ន្ទ' },
    { id: 2, name: 'អង្គារ' },
    { id: 3, name: 'ពុធ' },
    { id: 4, name: 'ព្រហស្បតិ៍' },
    { id: 5, name: 'សុក្រ' },
    { id: 6, name: 'សៅរ៍' }
];

const morningPeriods = computed(() => props.periods.filter(p => p.shift_id === 1));
const afternoonPeriods = computed(() => props.periods.filter(p => p.shift_id === 2));

// Filter single class state (if we want to view one by one)
const selectedClassId = ref(null);

const formattedClasses = computed(() => {
    return props.classes.map(c => ({
        id: c.id,
        name: c.class_code
    }));
});

const filteredClasses = computed(() => {
    if (!selectedClassId.value || selectedClassId.value === 'all') return props.classes;
    return props.classes.filter(c => c.id === selectedClassId.value);
});

// Helper to format time (e.g. 07:00 -> 7:00h)
const formatTime = (timeStr) => {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    return `${parseInt(hours)}.${minutes}h`;
};

const getSlotInfo = (schoolClass, periodId, dayId) => {
    if (!schoolClass || !schoolClass.teaching_assignments) return null;
    // Look through all assignments of this class
    for (const assignment of schoolClass.teaching_assignments) {
        if (!assignment.timetable_slots) continue;
        const slot = assignment.timetable_slots.find(s => s.period_id == periodId && s.day_of_week == dayId);
        if (slot) {
            const subjectName = assignment.subject ? assignment.subject.khmer_name : '';
            const teacherName = assignment.teacher ? assignment.teacher.khmer_name : '';
            return { subjectName, teacherName };
        }
    }
    return null;
};

// Helper to calculate total hours
const getTotalHours = (schoolClass) => {
    let total = 0;
    if (!schoolClass || !schoolClass.teaching_assignments) return total;
    for (const assignment of schoolClass.teaching_assignments) {
        if (assignment.timetable_slots) {
            total += assignment.timetable_slots.length;
        }
    }
    return total;
};
</script>

<template>
    <Head title="កាលវិភាគសិក្សាប្រចាំសប្ដាហ៍" />

    <PrintLayout>
        <!-- Print Actions (Hidden when printing) -->
        <div class="no-print p-4 bg-gray-50 border-b flex justify-between items-center mb-8 max-w-5xl mx-auto flex-wrap gap-4 z-50 relative">
            <div class="flex items-center gap-4">
                <h2 class="text-lg font-bold text-gray-800">កាលវិភាគសិក្សាប្រចាំសប្ដាហ៍</h2>
                <div class="w-72">
                    <SearchableSelect 
                        v-model="selectedClassId" 
                        :options="formattedClasses" 
                        valueKey="id" 
                        labelKey="name"
                        placeholder="បង្ហាញថ្នាក់ទាំងអស់"
                    />
                </div>
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
                    បោះពុម្ព (Print)
                </button>
            </div>
        </div>

        <div v-for="schoolClass in filteredClasses" :key="schoolClass.id" class="max-w-5xl mx-auto p-4 md:p-8 print:p-2 font-khmer teacher-page">
            
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
            <div class="text-center mb-6 print:mb-4">
                <h2 class="text-2xl print:text-xl font-bold font-moul tracking-wide">កាលវិភាគសិក្សាប្រចាំសប្ដាហ៍</h2>
            </div>

            <!-- Class Info -->
            <div class="flex justify-between mb-2 print:mb-1 text-md print:text-sm">
                <div class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-1 content-start">
                    <div>ថ្នាក់ទី៖</div>
                    <div class="font-bold">{{ schoolClass.class_code }}</div>
                    <div>បន្ទប់៖</div>
                    <div class="font-bold">{{ schoolClass.room ? schoolClass.room.room_name : '................' }}</div>
                    <div>ចំនួនសិស្ស៖</div>
                    <div><span class="font-bold">{{ schoolClass.student_count || '.......' }}</span> នាក់ ស្រី.......នាក់</div>
                </div>
                <div class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-1 content-start mr-8">
                    <div>គ្រូបន្ទុកថ្នាក់៖</div>
                    <div class="font-bold font-moul text-gray-900">
                        {{ schoolClass.homeroom_teacher ? schoolClass.homeroom_teacher.khmer_name : '.........................................' }}
                    </div>
                    <div>ឆមាសទី៖</div>
                    <div>
                        ១ <span class="ml-8">ឆ្នាំសិក្សា៖</span> <span class="font-bold">{{ school?.academic_year || '...................' }}</span>
                    </div>
                </div>
            </div>

            <!-- Timetable Table -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-800 text-center text-sm">
                    <!-- Morning Section -->
                    <thead class="bg-gray-200">
                        <tr>
                            <th colspan="7" class="border border-gray-900 py-1.5 px-2 font-bold font-moul text-sm print:text-xs">
                                <div class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-1 inline-block text-orange-500 print:text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    ពេលព្រឹក
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th class="border border-gray-900 py-1.5 px-2 w-28">
                                <div class="flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1 inline-block text-gray-700 print:text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    ម៉ោង
                                </div>
                            </th>
                            <th v-for="day in daysOfWeek" :key="day.id" class="border border-gray-900 py-1.5 px-2 w-24">
                                {{ day.name }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(period, index) in morningPeriods" :key="period.id">
                            <td class="border border-gray-800 py-1 px-2 text-xs">
                                {{ formatTime(period.start_time) }}-{{ formatTime(period.end_time) }}
                            </td>
                            <td v-for="day in daysOfWeek" :key="day.id" class="border border-gray-800 p-1 font-bold text-blue-800 text-xs">
                                <template v-if="getSlotInfo(schoolClass, period.id, day.id)">
                                    <div class="text-gray-900 font-bold text-sm leading-tight">{{ getSlotInfo(schoolClass, period.id, day.id).subjectName }}</div>
                                    <div class="font-normal text-gray-700 mt-0.5">{{ getSlotInfo(schoolClass, period.id, day.id).subjectShortName }}</div>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                    
                    <!-- Afternoon Section -->
                    <thead class="bg-gray-200">
                        <tr>
                            <th colspan="7" class="border border-gray-900 py-1.5 px-2 font-bold font-moul text-sm print:text-xs">
                                <div class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-1 inline-block text-orange-600 print:text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                                    ពេលរសៀល
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th class="border border-gray-900 py-1.5 px-2 w-28">
                                <div class="flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1 inline-block text-gray-700 print:text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    ម៉ោង
                                </div>
                            </th>
                            <th v-for="day in daysOfWeek" :key="day.id" class="border border-gray-900 py-1.5 px-2">
                                {{ day.name }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(period, index) in afternoonPeriods" :key="period.id">
                            <td class="border border-gray-800 py-1 px-2 text-xs">
                                {{ formatTime(period.start_time) }}-{{ formatTime(period.end_time) }}
                            </td>
                            <td v-for="day in daysOfWeek" :key="day.id" class="border border-gray-800 p-1 font-bold text-blue-800 text-xs">
                                <template v-if="getSlotInfo(schoolClass, period.id, day.id)">
                                    <div class="text-gray-900 font-bold text-sm leading-tight">{{ getSlotInfo(schoolClass, period.id, day.id).subjectName }}</div>
                                    <div class="font-normal text-gray-700 mt-0.5">{{ getSlotInfo(schoolClass, period.id, day.id).subjectShortName }}</div>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Teachers List -->
            <div class="mt-6 print:mt-2 mb-4 print:mb-1 px-4 print:px-0">
                <h3 class="font-bold mb-3 print:mb-1.5 font-moul text-sm print:text-xs">បញ្ជីរាយនាមគ្រូបង្រៀន៖</h3>
                <div class="grid grid-cols-2 gap-y-1.5 print:gap-y-1 gap-x-8 text-sm print:text-[11px]">
                    <template v-if="schoolClass.teaching_assignments">
                        <div v-for="assignment in schoolClass.teaching_assignments" :key="assignment.id" class="flex justify-between items-end border-b border-gray-100 pb-1 print:pb-0.5 print:border-transparent">
                            <div class="font-bold text-gray-800 whitespace-nowrap">{{ assignment.subject?.khmer_name }}</div>
                            <div class="flex-grow border-b border-dotted border-gray-400 mx-2 mb-1.5 print:mb-1"></div>
                            <div class="font-bold whitespace-nowrap flex items-center">
                                <span>{{ assignment.teacher?.khmer_name }} <span v-if="assignment.teacher?.gender" class="font-normal text-gray-600">({{ assignment.teacher.gender === 'F' ? 'ស្រី' : 'ប្រុស' }})</span></span>
                                <span v-if="assignment.teacher?.phone" class="flex items-center text-gray-500 font-normal ml-2 text-[10px] print:text-[9px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-0.5 print:h-2.5 print:w-2.5" viewBox="0 0 20 20" fill="currentColor">
                                      <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                    </svg>
                                    {{ assignment.teacher.phone.split(/(\r\n|\n|\r)/)[0] }}
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer Signatures -->
            <div class="mt-4 print:mt-1 flex justify-between px-4 print:px-0">
                <div class="text-center ml-[2cm]">
                    <p class="font-bold mb-1 print:mb-0.5 print:text-sm">បានឃើញ និងឯកភាព</p>
                    <p class="font-bold font-moul mb-12 print:mb-6 print:text-sm">នាយកសាលា</p>
                </div>
                <div>
                    <!-- Empty space in middle if needed -->
                </div>
                <div class="text-left w-80">
                    <p class="mb-1 print:mb-0.5 text-center print:text-sm">រាជធានី.....................ថ្ងៃទី...... ខែ...... ឆ្នាំ......</p>
                    <p class="font-bold mb-10 print:mb-6 text-center print:text-sm">ហត្ថលេខា និងឈ្មោះគ្រូបន្ទុកថ្នាក់</p>
                    <p class="font-bold text-center border-b border-dashed border-gray-400 mb-1">&nbsp;</p>
                    <div class="text-sm print:text-xs">
                        <p>ម៉ោងសិក្សា៖ {{ getTotalHours(schoolClass) }}h</p>
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

/* Ensure each teacher prints on a new page */
@media print {
    @page {
        margin: 1.5cm;
    }
    .no-print {
        display: none !important;
    }
    .teacher-page {
        page-break-after: always;
        break-after: page;
    }
    /* Don't break after the last teacher */
    .teacher-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }
}
</style>
