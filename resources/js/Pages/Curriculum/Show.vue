<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    curriculum: Object,
    availableSubjects: Array,
});

const form = useForm({
    subject_id: '',
    weekly_hours: 2,
});

const submitAdd = () => {
    form.post(route('curricula.subjects.attach', props.curriculum.id), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const removeSubject = (subjectId) => {
    if (confirm('តើអ្នកពិតជាចង់ដកមុខវិជ្ជានេះចេញមែនទេ?')) {
        router.delete(route('curricula.subjects.detach', { curriculum: props.curriculum.id, subject: subjectId }), {
            preserveScroll: true,
        });
    }
};

const updateWeeklyHours = (subject) => {
    router.put(route('curricula.subjects.update', { curriculum: props.curriculum.id, subject: subject.id }), {
        weekly_hours: subject.pivot.weekly_hours
    }, {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            alert('បរាជ័យក្នុងការកែប្រែម៉ោង។ សូមពិនិត្យទិន្នន័យឡើងវិញ។');
            router.reload({ only: ['curriculum'] });
        }
    });
};

const totalWeeklyHours = computed(() => {
    return localSubjects.value.reduce((sum, subject) => sum + Number(subject.pivot.weekly_hours), 0);
});

const localSubjects = ref([...props.curriculum.subjects]);

watch(() => props.curriculum.subjects, (newVal) => {
    localSubjects.value = [...newVal];
}, { deep: true });

let draggedIndex = null;

const onDragStart = (index, event) => {
    draggedIndex = index;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', index);
    }
};

const onDrop = (index) => {
    if (draggedIndex === null || draggedIndex === index) return;
    
    // Move item
    const movedItem = localSubjects.value.splice(draggedIndex, 1)[0];
    localSubjects.value.splice(index, 0, movedItem);
    
    // Send request to backend
    const subjectIds = localSubjects.value.map(s => s.id);
    router.post(route('curricula.subjects.reorder', props.curriculum.id), { subject_ids: subjectIds }, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            alert('បរាជ័យក្នុងការរៀបចំទីតាំងថ្មី។');
            localSubjects.value = [...props.curriculum.subjects];
        }
    });
    
    draggedIndex = null;
};
</script>

<template>
    <Head :title="'រៀបចំមុខវិជ្ជា - ' + curriculum.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">រៀបចំមុខវិជ្ជា៖ {{ curriculum.name }}</h2>
                <Link :href="route('curricula.index')" class="text-sm text-gray-600 hover:text-gray-900">&larr; ត្រឡប់ទៅក្រោយ</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Add Subject Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">បន្ថែមមុខវិជ្ជាចូលកម្មវិធីសិក្សា</h3>
                    <form @submit.prevent="submitAdd" class="flex flex-wrap items-end gap-4">
                        <div class="w-full sm:w-1/3">
                            <label class="block text-sm font-medium text-gray-700">មុខវិជ្ជា</label>
                            <select v-model="form.subject_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                                <option value="" disabled>-- ជ្រើសរើសមុខវិជ្ជា --</option>
                                <option v-for="sub in availableSubjects" :key="sub.id" :value="sub.id">
                                    {{ sub.khmer_name }}
                                </option>
                            </select>
                        </div>
                        <div class="w-full sm:w-1/4">
                            <label class="block text-sm font-medium text-gray-700">ម៉ោង/សប្ដាហ៍</label>
                            <input type="number" v-model="form.weekly_hours" min="0" max="40" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required />
                        </div>
                        <button type="submit" :disabled="form.processing || !form.subject_id" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 disabled:opacity-50">
                            បន្ថែមមុខវិជ្ជា
                        </button>
                    </form>
                </div>

                <!-- Attached Subjects List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">បញ្ជីមុខវិជ្ជាដែលបានបញ្ជូល</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">លេខកូដ</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ឈ្មោះមុខវិជ្ជា</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ម៉ោង/សប្ដាហ៍ ៖ <span class="text-blue-600 font-bold">{{ totalWeeklyHours }} ម៉ោង</span></th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">សកម្មភាព</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(subject, index) in localSubjects" :key="subject.id"
                                    class="even:bg-gray-50 hover:bg-gray-100 transition-colors cursor-move"
                                    draggable="true"
                                    @dragstart="onDragStart(index, $event)"
                                    @dragover.prevent
                                    @dragenter.prevent
                                    @drop="onDrop(index)">
                                    <td class="px-6 py-4 whitespace-nowrap flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-400 cursor-move shrink-0">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                                        </svg>
                                        {{ subject.subject_code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ subject.khmer_name }}</td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <input type="number" v-model="subject.pivot.weekly_hours" @change="updateWeeklyHours(subject)" min="0" max="40" class="w-16 bg-transparent border-0 focus:ring-1 focus:ring-blue-500 rounded p-1 text-sm text-center">
                                            <span class="text-gray-500 text-sm">ម៉ោង</span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="removeSubject(subject.id)" class="text-red-500 hover:text-red-700 p-2 rounded hover:bg-red-50 transition-colors" title="លុប">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="localSubjects.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        មិនទាន់មានមុខវិជ្ជានៅឡើយទេ
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
