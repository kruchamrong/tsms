<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Pagination from '@/Components/Pagination.vue';
import { router } from '@inertiajs/vue3';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    subjects: Object,
    groups: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');

const searchSubject = () => {
    router.get(route('admin.template-subjects.index'), { search: search.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingSubject = ref(null);
const deletingSubject = ref(null);

const form = useForm({
    subject_code: '',
    khmer_name: '',
    english_name: '',
    short_name: '',
    color: '#3B82F6',
    subject_group_id: null,
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    showCreateModal.value = true;
};

const openEditModal = (subject) => {
    editingSubject.value = subject;
    form.subject_code = subject.subject_code;
    form.khmer_name = subject.khmer_name;
    form.english_name = subject.english_name;
    form.short_name = subject.short_name;
    form.color = subject.color || '#3B82F6';
    form.subject_group_id = subject.subject_group_id;
    form.clearErrors();
    showEditModal.value = true;
};

const openDeleteModal = (subject) => {
    deletingSubject.value = subject;
    showDeleteModal.value = true;
};

const createSubject = () => {
    form.post(route('admin.template-subjects.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
        },
    });
};

const updateSubject = () => {
    form.put(route('admin.template-subjects.update', editingSubject.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

const deleteSubject = () => {
    router.delete(route('admin.template-subjects.destroy', deletingSubject.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const groupOptions = props.groups.map(group => ({
    value: group.id,
    label: group.name
}));
</script>

<template>
    <Head title="មុខវិជ្ជាគំរូ" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">គ្រប់គ្រងមុខវិជ្ជាគំរូ</h2>
                <div class="flex space-x-2">
                    <PrimaryButton @click="openCreateModal">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        បន្ថែមមុខវិជ្ជាគំរូថ្មី
                    </PrimaryButton>
                </div>
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
                                    @keyup.enter="searchSubject"
                                    type="text"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pl-10"
                                    placeholder="ស្វែងរកមុខវិជ្ជាគំរូ..."
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
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">កូដ</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ឈ្មោះមុខវិជ្ជា (ខ្មែរ)</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ឈ្មោះកាត់</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">កម្រង</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ពណ៌</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(subject, index) in subjects.data" :key="subject.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ subject.subject_code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ subject.khmer_name }}</div>
                                            <div class="text-sm text-gray-500">{{ subject.english_name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ subject.short_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-if="subject.subject_group" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ subject.subject_group.name }}
                                            </span>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-6 h-6 rounded-full border border-gray-200" :style="{ backgroundColor: subject.color }"></div>
                                                <span class="ml-2 text-sm text-gray-500">{{ subject.color }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openEditModal(subject)" class="text-blue-500 hover:text-blue-700 p-2 rounded hover:bg-blue-50 transition-colors" title="កែប្រែ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </button>
                                                <button @click="openDeleteModal(subject)" class="text-red-500 hover:text-red-700 p-2 rounded hover:bg-red-50 transition-colors" title="លុប">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="subjects.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            មិនមានទិន្នន័យ
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            <Pagination :links="subjects.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">បន្ថែមមុខវិជ្ជាគំរូថ្មី</h2>
                <form @submit.prevent="createSubject">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="subject_code" value="កូដមុខវិជ្ជា" />
                            <TextInput id="subject_code" type="text" class="mt-1 block w-full" v-model="form.subject_code" required />
                            <InputError class="mt-2" :message="form.errors.subject_code" />
                        </div>
                        <div>
                            <InputLabel for="short_name" value="ឈ្មោះកាត់" />
                            <TextInput id="short_name" type="text" class="mt-1 block w-full" v-model="form.short_name" required />
                            <InputError class="mt-2" :message="form.errors.short_name" />
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="khmer_name" value="ឈ្មោះមុខវិជ្ជា (ខ្មែរ)" />
                            <TextInput id="khmer_name" type="text" class="mt-1 block w-full" v-model="form.khmer_name" required />
                            <InputError class="mt-2" :message="form.errors.khmer_name" />
                        </div>
                        <div>
                            <InputLabel for="english_name" value="ឈ្មោះមុខវិជ្ជា (អង់គ្លេស)" />
                            <TextInput id="english_name" type="text" class="mt-1 block w-full" v-model="form.english_name" />
                            <InputError class="mt-2" :message="form.errors.english_name" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="subject_group_id" value="កម្រងមុខវិជ្ជា" />
                            <SearchableSelect 
                                v-model="form.subject_group_id" 
                                :options="groupOptions" 
                                placeholder="ជ្រើសរើសកម្រងមុខវិជ្ជា"
                            />
                            <InputError class="mt-2" :message="form.errors.subject_group_id" />
                        </div>
                        <div>
                            <InputLabel for="color" value="ពណ៌សម្គាល់" />
                            <div class="flex items-center mt-1">
                                <input id="color" type="color" class="h-10 w-14 border border-gray-300 rounded-md shadow-sm cursor-pointer" v-model="form.color" />
                                <span class="ml-3 text-sm text-gray-500">{{ form.color }}</span>
                            </div>
                            <InputError class="mt-2" :message="form.errors.color" />
                        </div>
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
                <h2 class="text-lg font-medium text-gray-900 mb-4">កែប្រែមុខវិជ្ជាគំរូ</h2>
                <form @submit.prevent="updateSubject">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="edit_subject_code" value="កូដមុខវិជ្ជា" />
                            <TextInput id="edit_subject_code" type="text" class="mt-1 block w-full" v-model="form.subject_code" required />
                            <InputError class="mt-2" :message="form.errors.subject_code" />
                        </div>
                        <div>
                            <InputLabel for="edit_short_name" value="ឈ្មោះកាត់" />
                            <TextInput id="edit_short_name" type="text" class="mt-1 block w-full" v-model="form.short_name" required />
                            <InputError class="mt-2" :message="form.errors.short_name" />
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="edit_khmer_name" value="ឈ្មោះមុខវិជ្ជា (ខ្មែរ)" />
                            <TextInput id="edit_khmer_name" type="text" class="mt-1 block w-full" v-model="form.khmer_name" required />
                            <InputError class="mt-2" :message="form.errors.khmer_name" />
                        </div>
                        <div>
                            <InputLabel for="edit_english_name" value="ឈ្មោះមុខវិជ្ជា (អង់គ្លេស)" />
                            <TextInput id="edit_english_name" type="text" class="mt-1 block w-full" v-model="form.english_name" />
                            <InputError class="mt-2" :message="form.errors.english_name" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="edit_subject_group_id" value="កម្រងមុខវិជ្ជា" />
                            <SearchableSelect 
                                v-model="form.subject_group_id" 
                                :options="groupOptions" 
                                placeholder="ជ្រើសរើសកម្រងមុខវិជ្ជា"
                            />
                            <InputError class="mt-2" :message="form.errors.subject_group_id" />
                        </div>
                        <div>
                            <InputLabel for="edit_color" value="ពណ៌សម្គាល់" />
                            <div class="flex items-center mt-1">
                                <input id="edit_color" type="color" class="h-10 w-14 border border-gray-300 rounded-md shadow-sm cursor-pointer" v-model="form.color" />
                                <span class="ml-3 text-sm text-gray-500">{{ form.color }}</span>
                            </div>
                            <InputError class="mt-2" :message="form.errors.color" />
                        </div>
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

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">បញ្ជាក់ការលុប</h2>
                <p class="text-sm text-gray-600 mb-6">
                    តើអ្នកពិតជាចង់លុបមុខវិជ្ជាគំរូ "<b>{{ deletingSubject?.khmer_name }}</b>" មែនទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានទេ។
                </p>
                <div class="flex justify-end">
                    <SecondaryButton @click="showDeleteModal = false" class="mr-3">បោះបង់</SecondaryButton>
                    <PrimaryButton class="bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-900" @click="deleteSubject">
                        លុប
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
