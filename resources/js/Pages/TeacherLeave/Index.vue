<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    leaves: Array,
    teachers: Array,
});

const formattedTeachers = computed(() => {
    return props.teachers
        .map(t => ({
            ...t,
            display_name: t.khmer_name
        }))
        .sort((a, b) => a.khmer_name.localeCompare(b.khmer_name, 'km'));
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const day = String(date.getDate()).padStart(2, '0');
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
};

const form = useForm({
    teacher_id: '',
    date_from: '',
    date_to: '',
    reason: '',
});

const submit = () => {
    form.post(route('teacher-leaves.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const deleteLeave = (id) => {
    if (confirm('Are you sure you want to delete this record?')) {
        useForm({}).delete(route('teacher-leaves.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="ការសុំច្បាប់ឈប់សម្រាក" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">សុំច្បាប់ឈប់សម្រាក</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Add Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100 relative z-20">
                    <h3 class="text-lg font-medium mb-4 text-blue-800">កត់ត្រាការសុំច្បាប់</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative z-50">
                                <label class="block text-sm font-medium text-gray-700">គ្រូបង្រៀន</label>
                                <SearchableSelect 
                                    class="mt-1"
                                    v-model="form.teacher_id" 
                                    :options="formattedTeachers" 
                                    valueKey="id" 
                                    labelKey="display_name" 
                                    placeholder="-- ជ្រើសរើស --"
                                />
                                <span v-if="form.errors.teacher_id" class="text-xs text-red-600 mt-1 block">{{ form.errors.teacher_id }}</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">មូលហេតុ</label>
                                <input v-model="form.reason" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="ឧ. ឈឺ, ធុរៈផ្ទាល់ខ្លួន...">
                                <span v-if="form.errors.reason" class="text-xs text-red-600 mt-1 block">{{ form.errors.reason }}</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">ចាប់ពីថ្ងៃ</label>
                                <input v-model="form.date_from" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <span v-if="form.errors.date_from" class="text-xs text-red-600 mt-1 block">{{ form.errors.date_from }}</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">ដល់ថ្ងៃ</label>
                                <input v-model="form.date_to" type="date" :min="form.date_from" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <span v-if="form.errors.date_to" class="text-xs text-red-600 mt-1 block">{{ form.errors.date_to }}</span>
                            </div>
                        </div>
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 font-medium transition shadow-sm text-sm disabled:opacity-50">រក្សាទុក</button>
                    </form>
                </div>

                <!-- List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200 border table-fixed">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase border tracking-wider w-64">គ្រូបង្រៀន</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase border tracking-wider w-64">កាលបរិច្ឆេទ</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase border tracking-wider">មូលហេតុ</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase border tracking-wider w-32">ស្ថានភាព</th>
                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase border tracking-wider w-32">សកម្មភាព</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="leaves.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                    គ្មានទិន្នន័យសុំច្បាប់ទេ
                                </td>
                            </tr>
                            <tr v-for="leave in leaves" :key="leave.id" class="even:bg-gray-50 hover:bg-gray-100 transition-colors">
                                <td class="px-6 py-4 border text-sm font-medium text-gray-900">{{ leave.teacher?.khmer_name || 'N/A' }}</td>
                                <td class="px-6 py-4 border text-sm text-gray-600">{{ formatDate(leave.date_from) }} ដល់ {{ formatDate(leave.date_to) }}</td>
                                <td class="px-6 py-4 border text-sm text-gray-600">{{ leave.reason || 'គ្មាន' }}</td>
                                <td class="px-6 py-4 border text-sm">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                        អនុម័តរួច
                                    </span>
                                </td>
                                <td class="px-6 py-4 border text-right text-sm font-medium">
                                    <button @click="deleteLeave(leave.id)" class="text-red-600 hover:text-red-900 bg-red-50 px-2 py-1 rounded border border-red-200">លុបចោល</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
