<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PrintLayout from '@/Layouts/PrintLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    school: Object,
    teachers: Array,
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

// Filter single teacher state (if we want to view one by one)
const selectedTeacherId = ref(null);

const formattedTeachers = computed(() => {
    return props.teachers.map(t => ({
        id: t.id,
        name: t.khmer_name
    }));
});

const filteredTeachers = computed(() => {
    if (!selectedTeacherId.value || selectedTeacherId.value === 'all') return props.teachers;
    return props.teachers.filter(t => t.id === selectedTeacherId.value);
});

// Helper to format time (e.g. 07:00 -> 7:00h)
const formatTime = (timeStr) => {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    return `${parseInt(hours)}.${minutes}h`;
};

// Helper to get slot for a teacher, period, and day
const getSlotClass = (teacher, periodId, dayId) => {
    // Look through all assignments of this teacher
    for (const assignment of teacher.teaching_assignments) {
        if (!assignment.timetable_slots) continue;
        const slot = assignment.timetable_slots.find(s => s.period_id == periodId && s.day_of_week == dayId);
        if (slot && assignment.school_class) {
            return assignment.school_class.class_code || assignment.school_class.name;
        }
    }
    return '';
};

// Helper to calculate total hours
const getTotalHours = (teacher) => {
    let total = 0;
    for (const assignment of teacher.teaching_assignments) {
        if (assignment.timetable_slots) {
            total += assignment.timetable_slots.length;
        }
    }
    return total;
};

// Helper to get list of subjects taught
const getSubjectsTaught = (teacher) => {
    const subjects = new Set();
    teacher.teaching_assignments.forEach(a => {
        if (a.subject) subjects.add(a.subject.khmer_name);
    });
    return Array.from(subjects).join(', ');
};

// Helper to get list of classes taught
const getClassesTaughtArray = (teacher) => {
    const classes = new Set();
    teacher.teaching_assignments.forEach(a => {
        if (a.school_class) classes.add(a.school_class.class_code);
    });
    return Array.from(classes);
};

// Helper to get subject hours
const getSubjectHours = (teacher) => {
    const subjectHours = {};
    teacher.teaching_assignments.forEach(a => {
        if (a.subject && a.timetable_slots) {
            const subjectName = a.subject.khmer_name;
            if (!subjectHours[subjectName]) {
                subjectHours[subjectName] = { hours: 0, classes: new Set() };
            }
            subjectHours[subjectName].hours += a.timetable_slots.length;
            if (a.school_class) {
                subjectHours[subjectName].classes.add(a.school_class.class_code);
            }
        }
    });
    return Object.entries(subjectHours).map(([name, data]) => ({ 
        name, 
        hours: data.hours, 
        classes: Array.from(data.classes).sort()
    }));
};
</script>

<template>
    <Head title="សេវាប្រចាំសប្ដាហ៍" />

    <PrintLayout>
        <!-- Print Actions (Hidden when printing) -->
        <div class="no-print p-4 bg-gray-50 border-b flex justify-between items-center mb-8 max-w-5xl mx-auto flex-wrap gap-4 z-50 relative">
            <div class="flex items-center gap-4">
                <h2 class="text-lg font-bold text-gray-800">សេវាប្រចាំសប្ដាហ៍របស់គ្រូ</h2>
                <div class="w-72">
                    <SearchableSelect 
                        v-model="selectedTeacherId" 
                        :options="formattedTeachers" 
                        valueKey="id" 
                        labelKey="name"
                        placeholder="បង្ហាញគ្រូទាំងអស់"
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

        <div v-for="teacher in filteredTeachers" :key="teacher.id" class="max-w-5xl mx-auto p-4 md:p-8 print:p-2 font-khmer teacher-page">
            
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
                <h2 class="text-2xl print:text-xl font-bold font-moul tracking-wide">សេវាប្រចាំសប្ដាហ៍</h2>
            </div>

            <!-- Teacher Info -->
            <div class="flex justify-between mb-4 print:mb-2 text-md print:text-sm px-4 print:px-0">
                <div class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-2 content-start">
                    <div>ឈ្មោះគ្រូបង្រៀន៖</div>
                    <div class="font-bold font-moul text-gray-900">{{ teacher.khmer_name }}</div>
                    
                    <div class="mt-0.5">ទទួលបន្ទុកថ្នាក់ទី៖</div>
                    <div v-if="!teacher.homeroom_classes || teacher.homeroom_classes.length === 0" class="mt-0.5">........................</div>
                    <div v-else class="flex flex-wrap items-center gap-1.5 mt-0.5">
                        <span v-for="hClass in teacher.homeroom_classes" :key="hClass.id" class="inline-block border border-green-400 bg-green-50 text-green-800 text-xs px-2 py-0.5 rounded font-bold shadow-sm">
                            {{ hClass.class_code }}
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-2 content-start mr-8">
                    <div>ឆមាសទី៖</div>
                    <div>
                        ១ <span class="ml-8">ឆ្នាំសិក្សា៖</span> <span class="font-bold">{{ school?.academic_year || '...................' }}</span>
                    </div>
                </div>
            </div>

            <!-- Timetable Table -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-900 text-center text-sm">
                    <!-- Morning Section -->
                    <thead class="bg-gray-200">
                        <tr>
                            <th colspan="7" class="border border-gray-900 py-1.5 px-2 font-bold font-moul text-sm print:text-xs">ពេលព្រឹក</th>
                        </tr>
                        <tr>
                            <th class="border border-gray-900 py-1.5 px-2 w-28">ម៉ោង</th>
                            <th v-for="day in daysOfWeek" :key="day.id" class="border border-gray-900 py-1.5 px-2 w-24">
                                {{ day.name }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(period, index) in morningPeriods" :key="period.id">
                            <td class="border border-gray-900 py-1 px-2 text-xs">
                                {{ formatTime(period.start_time) }}-{{ formatTime(period.end_time) }}
                            </td>
                            <td v-for="day in daysOfWeek" :key="day.id" class="border border-gray-900 p-1 font-bold text-blue-800">
                                {{ getSlotClass(teacher, period.id, day.id) }}
                            </td>
                        </tr>
                    </tbody>
                    
                    <!-- Afternoon Section -->
                    <thead class="bg-gray-200">
                        <tr>
                            <th colspan="7" class="border border-gray-900 py-1.5 px-2 font-bold font-moul text-sm print:text-xs">ពេលរសៀល</th>
                        </tr>
                        <tr>
                            <th class="border border-gray-900 py-1.5 px-2">ម៉ោង</th>
                            <th v-for="day in daysOfWeek" :key="day.id" class="border border-gray-900 py-1.5 px-2">
                                {{ day.name }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(period, index) in afternoonPeriods" :key="period.id">
                            <td class="border border-gray-900 py-1 px-2 text-xs">
                                {{ formatTime(period.start_time) }}-{{ formatTime(period.end_time) }}
                            </td>
                            <td v-for="day in daysOfWeek" :key="day.id" class="border border-gray-900 p-1 font-bold text-blue-800">
                                {{ getSlotClass(teacher, period.id, day.id) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary Information below table -->
            <div class="mt-5 print:mt-4 px-4 print:px-0">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="mb-2 text-sm font-bold font-moul print:text-xs">មុខវិជ្ជា និងថ្នាក់បង្រៀន៖</div>
                        <div class="flex flex-wrap gap-3">
                            <div v-for="subj in getSubjectHours(teacher)" :key="subj.name" class="flex flex-col bg-blue-50/50 border border-blue-100 text-blue-800 px-2 py-1.5 rounded shadow-sm w-44">
                                <div class="flex justify-between items-start">
                                    <span class="font-bold block mb-1.5 text-xs">{{ subj.name }}</span>
                                    <span class="ml-2 px-1.5 py-0.5 bg-blue-100 rounded-full font-bold text-[10px] whitespace-nowrap">{{ subj.hours }}h</span>
                                </div>
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="cls in subj.classes" :key="cls" class="inline-block bg-white border border-blue-100 text-blue-700 text-[10px] px-1 py-0.5 rounded shadow-sm font-medium leading-none">
                                        {{ cls }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-sm print:text-[11px] border border-gray-200 bg-gray-50/50 rounded p-3 min-w-[180px]">
                        <div class="mb-2 flex justify-between">
                            <span>ម៉ោងបង្រៀនសរុប៖</span> <span class="font-bold text-base print:text-sm">{{ getTotalHours(teacher) }}h</span>
                        </div>
                        <div class="flex justify-between mb-1.5 text-xs print:text-[10px] text-gray-700">
                            <span>ម៉ោងកម្រិត៖</span> <span>........h</span>
                        </div>
                        <div class="flex justify-between text-xs print:text-[10px] text-gray-700">
                            <span>ម៉ោងបន្ថែម៖</span> <span>........h</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Signatures -->
            <div class="mt-8 print:mt-6 flex justify-between px-4 print:px-0">
                <div class="text-center ml-[2cm]">
                    <p class="font-bold mb-1 print:mb-0.5 print:text-sm">បានឃើញ និងឯកភាព</p>
                    <p class="font-bold font-moul mb-12 print:mb-6 print:text-sm">នាយកសាលា</p>
                </div>
                <div class="text-center w-80 mr-8">
                    <p class="mb-1 print:mb-0.5 text-center print:text-sm">រាជធានី.....................ថ្ងៃទី...... ខែ...... ឆ្នាំ......</p>
                    <p class="font-bold mb-10 print:mb-6 text-center print:text-sm">ហត្ថលេខា និងឈ្មោះគ្រូបង្រៀន</p>
                    <p class="font-bold text-center border-b border-dashed border-gray-400 mb-1">&nbsp;</p>
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
