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
                <div class="bg-white overflow-visible shadow-sm sm:rounded-lg p-6 border border-gray-100 relative z-20">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-3 border-gray-100">កត់ត្រាការសុំច្បាប់</h3>
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
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-bold transition shadow-sm text-sm disabled:opacity-50 inline-flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            រក្សាទុក
                        </button>
                    </form>
                </div>

                <!-- List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100 table-fixed">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-64">គ្រូបង្រៀន</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-64">កាលបរិច្ឆេទ</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">មូលហេតុ</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">ស្ថានភាព</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">សកម្មភាព</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-if="leaves.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 italic">
                                    គ្មានទិន្នន័យសុំច្បាប់ទេ
                                </td>
                            </tr>
                            <tr v-for="leave in leaves" :key="leave.id" class="hover:bg-gray-50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ leave.teacher?.khmer_name || 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(leave.date_from) }} ដល់ {{ formatDate(leave.date_to) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 truncate">{{ leave.reason || 'គ្មាន' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <span class="px-2 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full bg-green-100 text-green-800 border border-green-200 shadow-sm">
                                        អនុម័តរួច
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" @click="deleteLeave(leave.id)" class="text-red-600 hover:text-red-800 p-1.5 rounded-full hover:bg-red-50 transition-colors" title="លុបចោល (Delete)">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
