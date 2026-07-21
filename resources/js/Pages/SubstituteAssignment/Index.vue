<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    selectedDate: String,
    missingSlots: Array,
    teachers: Array,
});

const selections = ref({});
watch(() => props.missingSlots, (slots) => {
    const newSelections = {};
    if (slots) {
        slots.forEach(slot => {
            newSelections[slot.id] = slot.substitute?.substitute_teacher_id || '';
        });
    }
    selections.value = newSelections;
}, { immediate: true });

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

const assignSub = (slotId) => {
    assignForm.timetable_slot_id = slotId;
    assignForm.substitute_teacher_id = selections.value[slotId];
    assignForm.post(route('substitute-assignments.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="ការគ្រប់គ្រងគ្រូជំនួស" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">គ្រប់គ្រងគ្រូជំនួស</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white overflow-visible shadow-sm sm:rounded-lg p-6 border border-gray-100 relative z-20">
                    <div class="flex flex-col md:flex-row items-end gap-4">
                        <div class="w-full md:w-1/3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">ជ្រើសរើសកាលបរិច្ឆេទ</label>
                            <input v-model="filterForm.date" type="date" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        <div class="w-full md:w-auto">
                            <button @click="fetchMissing" class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-bold transition shadow-sm text-sm border border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 h-[38px] inline-flex items-center justify-center">បង្ហាញទិន្នន័យ</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-visible shadow-sm sm:rounded-lg p-6 border border-gray-100 relative z-10">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-3 border-gray-100 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        បញ្ជីម៉ោងដែលខ្វះគ្រូបង្រៀន
                    </h3>
                    <div v-if="missingSlots && missingSlots.length > 0">
                        <table class="min-w-full divide-y divide-gray-100 table-fixed">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">គ្រូដើម</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">ម៉ោង និងបន្ទប់</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">មុខវិជ្ជា និងថ្នាក់</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">គ្រូជំនួស</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">សកម្មភាព</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100 relative z-10">
                                <tr v-for="slot in missingSlots" :key="slot.id" class="hover:bg-gray-50 transition-colors group">
                                    <td class="px-4 py-4 whitespace-nowrap text-red-600 font-bold text-sm">
                                        {{ slot.teaching_assignment.teacher.khmer_name }}
                                        <div class="text-xs text-red-400 font-normal mt-1 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            កំពុងសុំច្បាប់
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800">
                                        {{ slot.period.start_time.substring(0,5) }} - {{ slot.period.end_time.substring(0,5) }}
                                        <div class="text-xs text-gray-500 mt-1 font-mono">បន្ទប់: {{ slot.room?.room_name || slot.room?.name || 'គ្មាន' }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">
                                        {{ slot.teaching_assignment.subject.khmer_name || slot.teaching_assignment.subject.name }}
                                        <div class="text-xs text-blue-600 mt-1">ថ្នាក់: {{ slot.teaching_assignment.school_class?.class_code || slot.teaching_assignment.school_class?.name }}</div>
                                    </td>
                                    <td class="px-4 py-4 border-0 relative z-20">
                                        <SearchableSelect 
                                            v-model="selections[slot.id]"
                                            :options="teachers"
                                            label-key="khmer_name"
                                            value-key="id"
                                            placeholder="-- រើសគ្រូជំនួស --"
                                        />
                                        <div v-if="assignForm.errors.substitute_teacher_id && assignForm.timetable_slot_id === slot.id" class="text-sm text-red-600 mt-2">
                                            {{ assignForm.errors.substitute_teacher_id }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        <button @click="assignSub(slot.id)" class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-green-700 transition shadow-sm border border-transparent focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 w-full flex items-center justify-center">
                                            ចាត់តាំង
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-green-600 text-center py-10 flex flex-col items-center justify-center bg-green-50/50 rounded-lg border border-green-100 mt-4">
                        <svg class="w-12 h-12 text-green-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-lg font-medium">គ្មានម៉ោងដែលខ្វះគ្រូទេថ្ងៃនេះ!</span>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
