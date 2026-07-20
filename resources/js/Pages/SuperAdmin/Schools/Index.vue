<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { ref } from 'vue';

const props = defineProps({
    schools: Array,
});

const showingValidityModal = ref(false);
const selectedSchool = ref(null);

const form = useForm({
    days: 30,
});

const openValidityModal = (school) => {
    selectedSchool.value = school;
    form.days = 30;
    showingValidityModal.value = true;
};

const closeValidityModal = () => {
    showingValidityModal.value = false;
    setTimeout(() => {
        selectedSchool.value = null;
        form.reset();
    }, 300);
};

const extendValidity = () => {
    if (!selectedSchool.value) return;
    
    form.post(route('admin.schools.extend-validity', selectedSchool.value.id), {
        preserveScroll: true,
        onSuccess: () => closeValidityModal(),
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
};

</script>

<template>
    <Head title="បញ្ជីសាលារៀន" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                បញ្ជីសាលារៀន
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    
                    <!-- Flash Messages (if any) -->
                    <div v-if="$page.props.flash.success" class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-t-lg font-medium">
                        {{ $page.props.flash.success }}
                    </div>

                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ឈ្មោះសាលារៀន
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            កាលបរិច្ឆេទបង្កើត
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ថ្ងៃផុតសុពលភាព
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ចំនួនអ្នកប្រើប្រាស់
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ស្ថានភាព
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">សកម្មភាព</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="school in schools" :key="school.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ school.name }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                {{ school.contact_name }}
                                                <span class="mx-1 text-gray-300">•</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                {{ school.phone || 'មិនមានលេខ' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(school.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(school.trial_ends_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ school.users_count }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="school.is_active"
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                មានសុពលភាព
                                            </span>
                                            <span v-else
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                ផុតកំណត់
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="openValidityModal(school)" class="text-indigo-600 hover:text-indigo-900 flex items-center justify-end gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                បន្តសុពលភាព
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="schools.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 text-sm">
                                            មិនទាន់មានទិន្នន័យនៅឡើយទេ
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extend Validity Modal -->
        <Modal :show="showingValidityModal" @close="closeValidityModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    បន្តសុពលភាពសម្រាប់សាលា «{{ selectedSchool?.name }}»
                </h2>
                
                <div class="mb-4 text-sm text-gray-600">
                    <p>ថ្ងៃផុតកំណត់បច្ចុប្បន្ន៖ <span class="font-semibold">{{ formatDate(selectedSchool?.trial_ends_at) }}</span></p>
                </div>

                <div class="mb-6">
                    <InputLabel for="days" value="ចំនួនថ្ងៃដែលចង់បន្ថែម" />
                    <select 
                        id="days"
                        v-model="form.days"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                        <option :value="7">១ សប្តាហ៍ (7 ថ្ងៃ)</option>
                        <option :value="14">២ សប្តាហ៍ (14 ថ្ងៃ)</option>
                        <option :value="30">១ ខែ (30 ថ្ងៃ)</option>
                        <option :value="180">កន្លះឆ្នាំ (180 ថ្ងៃ)</option>
                        <option :value="365">១ ឆ្នាំ (365 ថ្ងៃ)</option>
                    </select>
                    <div v-if="form.errors.days" class="text-sm text-red-600 mt-2">{{ form.errors.days }}</div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <SecondaryButton @click="closeValidityModal">បោះបង់</SecondaryButton>
                    <PrimaryButton 
                        @click="extendValidity" 
                        :class="{ 'opacity-25': form.processing }" 
                        :disabled="form.processing"
                    >
                        យល់ព្រមបន្តសុពលភាព
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
