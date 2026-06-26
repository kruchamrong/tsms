$vueTemplate = @"
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classes: Array,
    teachers: Array,
    slots: Array,
    filterType: String,
    filterId: String,
});

const filterForm = useForm({
    type: props.filterType || 'class',
    id: props.filterId || '',
});

const days = [
    { id: 1, name: 'Monday (ច័ន្ទ)' },
    { id: 2, name: 'Tuesday (អង្គារ)' },
    { id: 3, name: 'Wednesday (ពុធ)' },
    { id: 4, name: 'Thursday (ព្រហស្បតិ៍)' },
    { id: 5, name: 'Friday (សុក្រ)' },
    { id: 6, name: 'Saturday (សៅរ៍)' },
];

const fetchTimetable = () => {
    filterForm.get(route('timetables.index'), { preserveState: true });
};

const generateForm = useForm({});
const generateTimetable = () => {
    if (confirm('តើអ្នកពិតជាចង់រៀបចំកាលវិភាគថ្មីមែនទេ? ទិន្នន័យចាស់នឹងត្រូវលុបចោល។')) {
        generateForm.post(route('timetables.generate'), {
            preserveScroll: true,
        });
    }
};

// Organize slots into a grid: day -> period -> slot
const timetableGrid = computed(() => {
    let grid = {};
    days.forEach(day => {
        grid[day.id] = {};
    });

    if (props.slots) {
        props.slots.forEach(slot => {
            if (!grid[slot.day_of_week][slot.period_id]) {
                grid[slot.day_of_week][slot.period_id] = [];
            }
            grid[slot.day_of_week][slot.period_id].push(slot);
        });
    }
    return grid;
});

// Get unique periods from slots to build columns
const activePeriods = computed(() => {
    let periodsMap = new Map();
    if (props.slots) {
        props.slots.forEach(slot => {
            if (slot.period) {
                periodsMap.set(slot.period.id, slot.period);
            }
        });
    }
    return Array.from(periodsMap.values()).sort((a, b) => a.start_time.localeCompare(b.start_time));
});

</script>

<template>
    <Head title="Timetable" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Timetable (កាលវិភាគ)</h2>
                <button @click="generateTimetable" :disabled="generateForm.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">
                    <span v-if="generateForm.processing">កំពុងចងក្រង...</span>
                    <span v-else>Generate Auto Timetable (ចងក្រងស្វ័យប្រវត្តិ)</span>
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                <div v-if="`$page.props.flash.success`" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">ជោគជ័យ!</strong>
                    <span class="block sm:inline">{{ `$page.props.flash.success` }}</span>
                </div>
                <div v-if="`$page.props.flash.warning`" class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">ចំណាំ:</strong>
                    <span class="block sm:inline">{{ `$page.props.flash.warning` }}</span>
                </div>
                <div v-if="`$page.props.flash.error`" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">បរាជ័យ:</strong>
                    <span class="block sm:inline">{{ `$page.props.flash.error` }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="flex space-x-4 items-end">
                        <div class="w-1/3">
                            <label class="block text-sm font-medium text-gray-700">Filter By</label>
                            <select v-model="filterForm.type" @change="filterForm.id = ''" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="class">Class (ថ្នាក់រៀន)</option>
                                <option value="teacher">Teacher (គ្រូបង្រៀន)</option>
                            </select>
                        </div>
                        <div class="w-1/3" v-if="filterForm.type === 'class'">
                            <label class="block text-sm font-medium text-gray-700">Select Class</label>
                            <select v-model="filterForm.id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- រើសថ្នាក់ --</option>
                                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div class="w-1/3" v-if="filterForm.type === 'teacher'">
                            <label class="block text-sm font-medium text-gray-700">Select Teacher</label>
                            <select v-model="filterForm.id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- រើសគ្រូ --</option>
                                <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.khmer_name }}</option>
                            </select>
                        </div>
                        <div>
                            <button @click="fetchTimetable" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">View (មើល)</button>
                        </div>
                    </div>
                </div>

                <div v-if="slots && slots.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border w-32">Day</th>
                                <th v-for="period in activePeriods" :key="period.id" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase border">
                                    {{ period.start_time.substring(0,5) }} - {{ period.end_time.substring(0,5) }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="day in days" :key="day.id">
                                <td class="px-6 py-4 whitespace-nowrap font-medium border bg-gray-50">{{ day.name }}</td>
                                <td v-for="period in activePeriods" :key="period.id" class="px-4 py-4 border align-top min-w-[150px]">
                                    <div v-if="timetableGrid[day.id][period.id]">
                                        <div v-for="slot in timetableGrid[day.id][period.id]" :key="slot.id" class="p-2 mb-2 bg-blue-50 border border-blue-200 rounded text-sm shadow-sm">
                                            <div class="font-bold text-blue-800">{{ slot.teaching_assignment?.subject?.name }}</div>
                                            <div class="text-gray-600 text-xs mt-1" v-if="filterForm.type === 'teacher'">ថ្នាក់: {{ slot.teaching_assignment?.school_class?.name }}</div>
                                            <div class="text-gray-600 text-xs mt-1" v-if="filterForm.type === 'class'">គ្រូ: {{ slot.teaching_assignment?.teacher?.khmer_name }}</div>
                                            <div class="text-gray-500 text-xs mt-1 font-mono">បន្ទប់: {{ slot.room?.name }}</div>
                                        </div>
                                    </div>
                                    <div v-else class="text-gray-300 text-center text-xs italic">-</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else-if="filterId" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    មិនមានទិន្នន័យកាលវិភាគទេ។ សូមចុច "ចងក្រងស្វ័យប្រវត្តិ" ខាងលើ។
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
"@

New-Item -ItemType Directory -Force -Path "resources\js\Pages\Timetable"
Set-Content "resources\js\Pages\Timetable\Index.vue" $vueTemplate
