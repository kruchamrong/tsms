<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    teachers: Array,
    shifts: Array,
    selectedTeacherId: String,
    availabilities: Array,
});

const days = [
    { id: 1, name: 'Monday (ច័ន្ទ)' },
    { id: 2, name: 'Tuesday (អង្គារ)' },
    { id: 3, name: 'Wednesday (ពុធ)' },
    { id: 4, name: 'Thursday (ព្រហស្បតិ៍)' },
    { id: 5, name: 'Friday (សុក្រ)' },
    { id: 6, name: 'Saturday (សៅរ៍)' },
];

const form = useForm({
    teacher_id: props.selectedTeacherId || '',
    availabilities: [],
});

// Initialize grid based on existing availabilities
const initGrid = () => {
    let grid = [];
    days.forEach(day => {
        props.shifts.forEach(shift => {
            const isAvailable = props.availabilities.some(a => a.day_of_week === day.id && a.shift_id === shift.id);
            grid.push({
                day_of_week: day.id,
                shift_id: shift.id,
                is_available: isAvailable || props.availabilities.length === 0, // Default to true if no data
            });
        });
    });
    form.availabilities = grid;
};

if (props.selectedTeacherId) {
    initGrid();
}

const filterByTeacher = (event) => {
    window.location.href = route('teacher-availabilities.index', { teacher_id: event.target.value });
};

const submit = () => {
    form.post(route('teacher-availabilities.store'), {
        preserveScroll: true,
    });
};

const isChecked = (dayId, shiftId) => {
    const item = form.availabilities.find(a => a.day_of_week === dayId && a.shift_id === shiftId);
    return item ? item.is_available : false;
};

const toggleCheck = (dayId, shiftId) => {
    const item = form.availabilities.find(a => a.day_of_week === dayId && a.shift_id === shiftId);
    if (item) {
        item.is_available = !item.is_available;
    }
};
</script>

<template>
    <Head title="Teacher Availability" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Teacher Availability (ម៉ោងទំនេររបស់គ្រូ)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Select Teacher (ជ្រើសរើសគ្រូបង្រៀន)</label>
                        <select v-model="form.teacher_id" @change="filterByTeacher" class="mt-1 block w-full md:w-1/3 border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Select Teacher --</option>
                            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                {{ teacher.khmer_name }} ({{ teacher.english_name }})
                            </option>
                        </select>
                    </div>
                </div>

                <div v-if="form.teacher_id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Availability Grid (សូមធីកក្នុងប្រអប់ដែលគ្រូអាចបង្រៀនបាន)</h3>
                    <form @submit.prevent="submit">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border">Day</th>
                                        <th v-for="shift in shifts" :key="shift.id" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase border">
                                            {{ shift.name }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="day in days" :key="day.id">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium border">{{ day.name }}</td>
                                        <td v-for="shift in shifts" :key="shift.id" class="px-6 py-4 whitespace-nowrap text-center border">
                                            <input type="checkbox" 
                                                   :checked="isChecked(day.id, shift.id)"
                                                   @change="toggleCheck(day.id, shift.id)"
                                                   class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">
                            <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white py-2 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-blue-700">
                                Save Availability
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
