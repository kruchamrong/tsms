<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    curricula: Array,
});

const localCurricula = ref([...props.curricula]);

watch(() => props.curricula, (newVal) => {
    localCurricula.value = [...newVal];
}, { deep: true });

const importTemplates = () => {
    if (confirm('តើអ្នកពិតជាចង់នាំចូលកម្មវិធីសិក្សាគំរូទាំងអស់មែនទេ?')) {
        router.post(route('curricula.import-templates'));
    }
};

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
    
    // Send request to backend
    const curriculumIds = localCurricula.value.map(c => c.id);
    router.post(route('curricula.reorder'), { curriculum_ids: curriculumIds }, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            alert('បរាជ័យក្នុងការរៀបចំទីតាំងថ្មី។');
            localCurricula.value = [...props.curricula];
        }
    });
    
    draggedIndex = null;
};

const deleteCurriculum = (curriculum) => {
    if (confirm('តើអ្នកពិតជាចង់លុបកម្មវិធីសិក្សានេះមែនទេ?')) {
        router.delete(route('curricula.destroy', curriculum.id), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};
</script>

<template>
    <Head title="កម្មវិធីសិក្សា" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">បញ្ជីកម្មវិធីសិក្សា</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">គ្រប់គ្រងកម្មវិធីសិក្សា</h3>
                            <div class="flex gap-2">
                                <PrimaryButton @click="importTemplates" type="button" class="bg-purple-600 hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 border-transparent shadow-sm flex items-center px-4 py-2 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    នាំចូលកម្មវិធីសិក្សាគំរូ
                                </PrimaryButton>
                                <Link :href="route('curricula.create')" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition">
                                    បន្ថែមថ្មី
                                </Link>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-[800px] w-full divide-y divide-gray-200 table-fixed">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-64">ឈ្មោះកម្មវិធី</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ការពិពណ៌នា</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-40">ចំនួនមុខវិជ្ជា</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">ម៉ោងសរុប</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-36">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr v-for="(curriculum, index) in localCurricula" :key="curriculum.id"
                                        draggable="true" 
                                        @dragstart="onDragStart(index, $event)" 
                                        @dragover.prevent 
                                        @dragenter.prevent 
                                        @drop="onDrop(index)"
                                        class="hover:bg-gray-50 transition-colors cursor-move group">
                                        <td class="px-6 py-3 whitespace-nowrap font-medium text-gray-900 flex items-center gap-2 truncate">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-400 cursor-move shrink-0 hover:text-gray-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                                            </svg>
                                            <Link :href="route('curricula.show', curriculum.id)" class="hover:text-blue-600 transition-colors" title="ចូលទៅរៀបចំមុខវិជ្ជា">
                                                {{ curriculum.name }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-3 truncate text-sm" :title="curriculum.description">{{ curriculum.description }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ curriculum.subjects_count }} មុខវិជ្ជា</span>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ curriculum.total_hours || 0 }} ម៉ោង</span>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-center">
                                            <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <Link :href="route('curricula.show', curriculum.id)" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-full hover:bg-blue-50 transition-colors" title="រៀបចំមុខវិជ្ជា">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </Link>
                                                <Link :href="route('curricula.edit', curriculum.id)" class="text-indigo-600 hover:text-indigo-800 p-1.5 rounded-full hover:bg-indigo-50 transition-colors" title="កែប្រែ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </Link>
                                                <button @click="deleteCurriculum(curriculum)" class="text-red-600 hover:text-red-800 p-1.5 rounded-full hover:bg-red-50 transition-colors" title="លុប">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="localCurricula.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            មិនមានទិន្នន័យ
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
