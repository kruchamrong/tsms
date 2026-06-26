<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    teachers: Object,
});
</script>

<template>
    <Head title="គ្រូបង្រៀន (Teachers)" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">បញ្ជីគ្រូបង្រៀន (Teacher List)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">គ្រប់គ្រងគ្រូបង្រៀន</h3>
                            <Link :href="route('teachers.create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                                បន្ថែមគ្រូបង្រៀនថ្មី (Add New)
                            </Link>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">អត្តលេខ (Code)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ឈ្មោះខ្មែរ (Khmer Name)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ឈ្មោះអង់គ្លេស (English Name)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ភេទ (Gender)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ប្រភេទការងារ (Type)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">សកម្មភាព (Actions)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="teachers.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">ពុំមានទិន្នន័យ (No data available)</td>
                                    </tr>
                                    <tr v-for="teacher in teachers.data" :key="teacher.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ teacher.teacher_code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ teacher.khmer_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ teacher.english_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ teacher.gender === 'M' ? 'ប្រុស (M)' : 'ស្រី (F)' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ teacher.employment_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('teachers.edit', teacher.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">កែប្រែ (Edit)</Link>
                                            <Link :href="route('teachers.destroy', teacher.id)" method="delete" as="button" class="text-red-600 hover:text-red-900">លុប (Delete)</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
