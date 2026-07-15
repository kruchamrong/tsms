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

const props = defineProps({
    periods: Object,
    shifts: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');

const searchPeriod = () => {
    router.get(route('admin.template-periods.index'), { search: search.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingPeriod = ref(null);
const deletingPeriod = ref(null);

const form = useForm({
    start_time: '',
    end_time: '',
    shift_id: '',
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    showCreateModal.value = true;
};

const openEditModal = (period) => {
    editingPeriod.value = period;
    form.start_time = period.start_time;
    form.end_time = period.end_time;
    form.shift_id = period.shift_id;
    form.clearErrors();
    showEditModal.value = true;
};

const openDeleteModal = (period) => {
    deletingPeriod.value = period;
    showDeleteModal.value = true;
};

const createPeriod = () => {
    form.post(route('admin.template-periods.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
        },
    });
};

const updatePeriod = () => {
    form.put(route('admin.template-periods.update', editingPeriod.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

const deletePeriod = () => {
    router.delete(route('admin.template-periods.destroy', deletingPeriod.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const formatTime = (timeStr) => {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    return `${hours}:${minutes}`;
};
</script>

<template>
    <Head title="ម៉ោងសិក្សាគំរូ" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">គ្រប់គ្រងម៉ោងសិក្សាគំរូ</h2>
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
                                    @keyup.enter="searchPeriod"
                                    type="text"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pl-10"
                                    placeholder="ស្វែងរកតាមវេនសិក្សា..."
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
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ល.រ</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ម៉ោងចាប់ផ្ដើម</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ម៉ោងបញ្ចប់</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">វេនសិក្សា</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(period, index) in periods.data" :key="period.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ (periods.current_page - 1) * periods.per_page + index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ formatTime(period.start_time) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ formatTime(period.end_time) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {{ period.shift?.name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openEditModal(period)" class="text-blue-500 hover:text-blue-700 p-2 rounded hover:bg-blue-50 transition-colors" title="កែប្រែ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </button>
                                                <button @click="openDeleteModal(period)" class="text-red-500 hover:text-red-700 p-2 rounded hover:bg-red-50 transition-colors" title="លុប">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="periods.data.length === 0">
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            មិនមានទិន្នន័យ
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            <Pagination :links="periods.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">បន្ថែមម៉ោងសិក្សាគំរូថ្មី</h2>
                <form @submit.prevent="createPeriod">
                    <div class="mb-4">
                        <InputLabel for="shift_id" value="វេនសិក្សា" />
                        <select
                            id="shift_id"
                            v-model="form.shift_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="" disabled>-- ជ្រើសរើសវេន --</option>
                            <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.shift_id" />
                    </div>
                    <div class="mb-4 flex gap-4">
                        <div class="flex-1">
                            <InputLabel for="start_time" value="ម៉ោងចាប់ផ្ដើម" />
                            <TextInput
                                id="start_time"
                                type="time"
                                class="mt-1 block w-full"
                                v-model="form.start_time"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.start_time" />
                        </div>
                        <div class="flex-1">
                            <InputLabel for="end_time" value="ម៉ោងបញ្ចប់" />
                            <TextInput
                                id="end_time"
                                type="time"
                                class="mt-1 block w-full"
                                v-model="form.end_time"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.end_time" />
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
                <h2 class="text-lg font-medium text-gray-900 mb-4">កែប្រែម៉ោងសិក្សាគំរូ</h2>
                <form @submit.prevent="updatePeriod">
                    <div class="mb-4">
                        <InputLabel for="edit_shift_id" value="វេនសិក្សា" />
                        <select
                            id="edit_shift_id"
                            v-model="form.shift_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="" disabled>-- ជ្រើសរើសវេន --</option>
                            <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.shift_id" />
                    </div>
                    <div class="mb-4 flex gap-4">
                        <div class="flex-1">
                            <InputLabel for="edit_start_time" value="ម៉ោងចាប់ផ្ដើម" />
                            <TextInput
                                id="edit_start_time"
                                type="time"
                                class="mt-1 block w-full"
                                v-model="form.start_time"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.start_time" />
                        </div>
                        <div class="flex-1">
                            <InputLabel for="edit_end_time" value="ម៉ោងបញ្ចប់" />
                            <TextInput
                                id="edit_end_time"
                                type="time"
                                class="mt-1 block w-full"
                                v-model="form.end_time"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.end_time" />
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
                    តើអ្នកពិតជាចង់លុបម៉ោងសិក្សាគំរូពី "<b>{{ formatTime(deletingPeriod?.start_time) }} - {{ formatTime(deletingPeriod?.end_time) }}</b>" មែនទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានទេ។
                </p>
                <div class="flex justify-end">
                    <SecondaryButton @click="showDeleteModal = false" class="mr-3">បោះបង់</SecondaryButton>
                    <PrimaryButton
                        class="bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-900"
                        @click="deletePeriod"
                    >
                        លុប
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
