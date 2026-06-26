<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    selectedDate: String,
    missingSlots: Array,
    teachers: Array,
});

const filterForm = useForm({
    date: props.selectedDate
});

const fetchMissing = () => {
    filterForm.get(route('substitute-assignments.index'));
};

const assignForm = useForm({
    timetable_slot_id: '',
    substitute_teacher_id: '',
    date: props.selectedDate,
});

const assignSub = (slotId, teacherId) => {
    assignForm.timetable_slot_id = slotId;
    assignForm.substitute_teacher_id = teacherId;
    assignForm.post(route('substitute-assignments.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Substitute Teachers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Substitute Teachers (គ្រប់គ្រងគ្រូជំនួស)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-end space-x-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Select Date (ជ្រើសរើសកាលបរិច្ឆេទ)</label>
                            <input v-model="filterForm.date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <button @click="fetchMissing" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">View (មើល)</button>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4 text-red-600">Classes Needing Substitutes (ម៉ោងដែលខ្វះគ្រូបង្រៀន)</h3>
                    <div v-if="missingSlots && missingSlots.length > 0">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border">Original Teacher</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border">Time & Room</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border">Subject & Class</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border">Substitute Teacher</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="slot in missingSlots" :key="slot.id">
                                    <td class="px-4 py-4 border text-red-600 font-bold">
                                        {{ slot.teaching_assignment.teacher.khmer_name }}
                                        <div class="text-xs text-gray-500 font-normal">On Leave</div>
                                    </td>
                                    <td class="px-4 py-4 border">
                                        {{ slot.period.start_time.substring(0,5) }} - {{ slot.period.end_time.substring(0,5) }}
                                        <div class="text-xs text-gray-500">Room: {{ slot.room.name }}</div>
                                    </td>
                                    <td class="px-4 py-4 border">
                                        {{ slot.teaching_assignment.subject.name }}
                                        <div class="text-xs text-gray-500">Class: {{ slot.teaching_assignment.school_class.name }}</div>
                                    </td>
                                    <td class="px-4 py-4 border">
                                        <select :id="'sub_' + slot.id" class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                            <option value="">-- រើសគ្រូជំនួស --</option>
                                            <option v-for="t in teachers" :key="t.id" :value="t.id" :selected="slot.substitute?.substitute_teacher_id === t.id">{{ t.khmer_name }}</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-4 border text-center">
                                        <button @click="assignSub(slot.id, document.getElementById('sub_' + slot.id).value)" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                            Assign
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-green-600 text-center py-4">
                        All classes are fully covered today! (គ្មានម៉ោងដែលខ្វះគ្រូទេថ្ងៃនេះ)
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
