<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    teacher: Object,
});

const form = useForm({
    teacher_code: props.teacher.teacher_code,
    khmer_name: props.teacher.khmer_name,
    english_name: props.teacher.english_name,
    gender: props.teacher.gender,
    date_of_birth: props.teacher.date_of_birth || '',
    phone: props.teacher.phone || '',
    telegram_id: props.teacher.telegram_id || '',
    email: props.teacher.email || '',
    qualification: props.teacher.qualification || '',
    employment_type: props.teacher.employment_type,
    status: props.teacher.status,
});

const submit = () => {
    form.put(route('teachers.update', props.teacher.id));
};
</script>

<template>
    <Head title="កែប្រែគ្រូបង្រៀន (Edit Teacher)" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">កែប្រែគ្រូបង្រៀន (Edit Teacher)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="max-w-3xl mx-auto">
                            <div class="grid grid-cols-2 gap-6">
                                <!-- Code -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">អត្តលេខ (Code) *</label>
                                    <input v-model="form.teacher_code" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <div v-if="form.errors.teacher_code" class="text-red-500 text-xs mt-1">{{ form.errors.teacher_code }}</div>
                                </div>
                                
                                <!-- Khmer Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ឈ្មោះខ្មែរ (Khmer Name) *</label>
                                    <input v-model="form.khmer_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <div v-if="form.errors.khmer_name" class="text-red-500 text-xs mt-1">{{ form.errors.khmer_name }}</div>
                                </div>

                                <!-- English Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ឈ្មោះអង់គ្លេស (English Name) *</label>
                                    <input v-model="form.english_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <div v-if="form.errors.english_name" class="text-red-500 text-xs mt-1">{{ form.errors.english_name }}</div>
                                </div>

                                <!-- Gender -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ភេទ (Gender) *</label>
                                    <select v-model="form.gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="M">ប្រុស (Male)</option>
                                        <option value="F">ស្រី (Female)</option>
                                    </select>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">លេខទូរស័ព្ទ (Phone)</label>
                                    <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">អ៊ីមែល (Email)</label>
                                    <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                                </div>

                                <!-- Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ប្រភេទការងារ (Employment Type) *</label>
                                    <select v-model="form.employment_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="Full-Time">Full-Time</option>
                                        <option value="Part-Time">Part-Time</option>
                                        <option value="Visiting">Visiting</option>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ស្ថានភាព (Status) *</label>
                                    <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                        <option value="On-Leave">On-Leave</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mt-8 flex justify-end gap-4">
                                <Link :href="route('teachers.index')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded shadow">
                                    ត្រលប់ក្រោយ (Cancel)
                                </Link>
                                <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow disabled:opacity-50">
                                    រក្សាទុក (Update)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
