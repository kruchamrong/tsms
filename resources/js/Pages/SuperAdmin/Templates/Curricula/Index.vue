<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Pagination from '@/Components/Pagination.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    curricula: Object,
    subjects: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');

const searchCurriculum = () => {
    router.get(route('admin.template-curricula.index'), { search: search.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showSubjectsModal = ref(false);

const editingCurriculum = ref(null);
const deletingCurriculum = ref(null);
const activeCurriculum = ref(null);

const form = useForm({
    name: '',
    description: '',
    sort_order: 0,
});

const subjectsForm = useForm({
    subjects: []
});

const totalWeeklyHours = computed(() => {
    return subjectsForm.subjects
        .filter(s => s.selected)
        .reduce((sum, s) => sum + (Number(s.weekly_hours) || 0), 0);
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    showCreateModal.value = true;
};

const openEditModal = (curriculum) => {
    editingCurriculum.value = curriculum;
    form.name = curriculum.name;
    form.description = curriculum.description;
    form.sort_order = curriculum.sort_order;
    form.clearErrors();
    showEditModal.value = true;
};

const openDeleteModal = (curriculum) => {
    deletingCurriculum.value = curriculum;
    showDeleteModal.value = true;
};

const openSubjectsModal = (curriculum) => {
    activeCurriculum.value = curriculum;
    
    // Initialize subjectsForm with existing pivot data
    const existingSubjects = curriculum.subjects || [];
    
    subjectsForm.subjects = props.subjects.map(subject => {
        const existing = existingSubjects.find(s => s.id === subject.id);
        return {
            subject_id: subject.id,
            name: subject.khmer_name,
            selected: !!existing,
            weekly_hours: existing ? existing.pivot.weekly_hours : 1
        };
    });
    
    showSubjectsModal.value = true;
};

const createCurriculum = () => {
    form.post(route('admin.template-curricula.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
        },
    });
};

const updateCurriculum = () => {
    form.put(route('admin.template-curricula.update', editingCurriculum.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

const deleteCurriculum = () => {
    router.delete(route('admin.template-curricula.destroy', deletingCurriculum.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const syncSubjects = () => {
    // Filter out only selected subjects and send required fields
    const selectedSubjects = subjectsForm.subjects
        .filter(s => s.selected)
        .map(s => ({
            subject_id: s.subject_id,
            weekly_hours: s.weekly_hours
        }));

    subjectsForm.transform((data) => ({
        subjects: selectedSubjects
    })).post(route('admin.template-curricula.sync', activeCurriculum.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSubjectsModal.value = false;
        },
    });
};

const getCurriculumTotalHours = (curriculum) => {
    if (!curriculum.subjects) return 0;
    return curriculum.subjects.reduce((sum, subject) => sum + (Number(subject.pivot?.weekly_hours) || 0), 0);
};

const localCurricula = ref([...props.curricula.data]);

watch(() => props.curricula.data, (newVal) => {
    localCurricula.value = [...newVal];
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
    const movedItem = localCurricula.value.splice(draggedIndex, 1)[0];
    localCurricula.value.splice(index, 0, movedItem);
    
    // Re-assign sort_order
    localCurricula.value.forEach((curr, i) => {
        curr.sort_order = i + 1;
    });
    
    // Send request to backend
    const curriculaData = localCurricula.value.map(c => ({
        id: c.id,
        sort_order: c.sort_order
    }));
    
    router.post(route('admin.template-curricula.reorder'), { curricula: curriculaData }, {
        preserveScroll: true,
        preserveState: true,
    });
    
    draggedIndex = null;
};
</script>

<template>
    <Head title="កម្មវិធីសិក្សាគំរូ" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">គ្រប់គ្រងកម្មវិធីសិក្សាគំរូ</h2>
                <PrimaryButton @click="openCreateModal">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    បន្ថែមថ្មី
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Search Bar -->
                        <div class="mb-6 flex justify-between items-center">
                            <div class="w-1/3 relative">
                                <input
                                    v-model="search"
                                    @keyup.enter="searchCurriculum"
                                    type="text"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pl-10"
                                    placeholder="ស្វែងរកកម្មវិធីសិក្សាគំរូ..."
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ល.រ (តម្រៀប)</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ឈ្មោះកម្មវិធីសិក្សា</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ការពិពណ៌នា</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ចំនួនមុខវិជ្ជា</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ម៉ោងសរុប/សប្ដាហ៍</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(curriculum, index) in localCurricula" :key="curriculum.id" 
                                        class="hover:bg-gray-50 cursor-move"
                                        draggable="true"
                                        @dragstart="onDragStart(index, $event)"
                                        @dragover.prevent
                                        @dragenter.prevent
                                        @drop="onDrop(index)">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-400 cursor-move shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                                            </svg>
                                            {{ curricula.from ? curricula.from + index : index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ curriculum.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ curriculum.description || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ curriculum.subjects ? curriculum.subjects.length : 0 }} មុខវិជ្ជា
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-100 text-blue-800">
                                                {{ getCurriculumTotalHours(curriculum) }} ម៉ោង
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openSubjectsModal(curriculum)" class="text-emerald-600 hover:text-emerald-800 p-2 rounded hover:bg-emerald-50 transition-colors" title="ចងមុខវិជ្ជា">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                    </svg>
                                                </button>
                                                <button @click="openEditModal(curriculum)" class="text-blue-500 hover:text-blue-700 p-2 rounded hover:bg-blue-50 transition-colors" title="កែប្រែ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </button>
                                                <button @click="openDeleteModal(curriculum)" class="text-red-500 hover:text-red-700 p-2 rounded hover:bg-red-50 transition-colors" title="លុប">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="curricula.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            មិនមានទិន្នន័យ
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            <Pagination :links="curricula.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">បន្ថែមកម្មវិធីសិក្សាគំរូថ្មី</h2>
                <form @submit.prevent="createCurriculum">
                    <div class="mb-4">
                        <InputLabel for="name" value="ឈ្មោះកម្មវិធីសិក្សា" />
                        <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div class="mb-4">
                        <InputLabel for="description" value="ការពិពណ៌នា" />
                        <TextInput id="description" type="text" class="mt-1 block w-full" v-model="form.description" />
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>
                    <div class="mb-4">
                        <InputLabel for="sort_order" value="លំដាប់តម្រៀប" />
                        <TextInput id="sort_order" type="number" class="mt-1 block w-full" v-model="form.sort_order" />
                        <InputError class="mt-2" :message="form.errors.sort_order" />
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="showCreateModal = false" class="mr-3">បោះបង់</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            រក្សាទុក
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">កែប្រែកម្មវិធីសិក្សាគំរូ</h2>
                <form @submit.prevent="updateCurriculum">
                    <div class="mb-4">
                        <InputLabel for="edit_name" value="ឈ្មោះកម្មវិធីសិក្សា" />
                        <TextInput id="edit_name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div class="mb-4">
                        <InputLabel for="edit_description" value="ការពិពណ៌នា" />
                        <TextInput id="edit_description" type="text" class="mt-1 block w-full" v-model="form.description" />
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>
                    <div class="mb-4">
                        <InputLabel for="edit_sort_order" value="លំដាប់តម្រៀប" />
                        <TextInput id="edit_sort_order" type="number" class="mt-1 block w-full" v-model="form.sort_order" />
                        <InputError class="mt-2" :message="form.errors.sort_order" />
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="showEditModal = false" class="mr-3">បោះបង់</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            រក្សាទុក
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Manage Subjects Modal -->
        <Modal :show="showSubjectsModal" @close="showSubjectsModal = false" maxWidth="2xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    ចងមុខវិជ្ជាទៅកម្មវិធីសិក្សា <b>{{ activeCurriculum?.name }}</b>
                </h2>
                
                <form @submit.prevent="syncSubjects">
                    <div class="max-h-96 overflow-y-auto mb-4 border border-gray-200 rounded-md p-4 bg-gray-50">
                        <div v-for="(subject, index) in subjectsForm.subjects" :key="subject.subject_id" class="flex items-center justify-between py-2 border-b border-gray-200 last:border-0">
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    :id="'subject_' + subject.subject_id" 
                                    v-model="subject.selected"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                >
                                <label :for="'subject_' + subject.subject_id" class="ml-2 text-sm font-medium text-gray-900">
                                    {{ subject.name }}
                                </label>
                            </div>
                            <div v-if="subject.selected" class="flex items-center">
                                <label class="mr-2 text-xs text-gray-500">ម៉ោងក្នុងសប្ដាហ៍ ៖ </label>
                                <input 
                                    type="number" 
                                    v-model="subject.weekly_hours" 
                                    min="1" 
                                    class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center bg-blue-50 p-4 rounded-md mb-6 border border-blue-100">
                        <span class="font-medium text-blue-900">ម៉ោងសិក្សាសរុបប្រចាំសប្ដាហ៍៖</span>
                        <span class="text-lg font-bold text-blue-700">{{ totalWeeklyHours }} ម៉ោង</span>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="showSubjectsModal = false" class="mr-3">បោះបង់</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': subjectsForm.processing }" :disabled="subjectsForm.processing">
                            រក្សាទុកការចងមុខវិជ្ជា
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">បញ្ជាក់ការលុប</h2>
                <p class="text-sm text-gray-600 mb-6">
                    តើអ្នកពិតជាចង់លុបកម្មវិធីសិក្សាគំរូ "<b>{{ deletingCurriculum?.name }}</b>" មែនទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានទេ។
                </p>
                <div class="flex justify-end">
                    <SecondaryButton @click="showDeleteModal = false" class="mr-3">បោះបង់</SecondaryButton>
                    <PrimaryButton class="bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-900" @click="deleteCurriculum">
                        លុប
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
