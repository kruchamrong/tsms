<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    subjects: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

watch(search, debounce(() => {
    router.get(route('subjects.index'), {
        search: search.value,
    }, { preserveState: true, replace: true });
}, 300));



const updateSubject = (subject) => {
    router.put(route('subjects.update', subject.id), subject, {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            console.error('Error updating subject:', errors);
            alert('បរាជ័យក្នុងការកែប្រែ៖ \n' + Object.values(errors).join('\n'));
            router.reload({ only: ['subjects'] });
        }
    });
};

const updateColor = (subject) => {
    router.patch(route('subjects.updateColor', subject.id), { color: subject.color }, {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            console.error('Error updating color:', errors);
            alert('បរាជ័យក្នុងការប្ដូរពណ៌៖ \n' + Object.values(errors).join('\n'));
            router.reload({ only: ['subjects'] });
        }
    });
};

const fileInput = ref(null);

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    router.post(route('subjects.import'), formData, {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.file) {
                alert('កំហុស៖ ' + errors.file);
            }
        },
        onFinish: () => {
            if (fileInput.value) fileInput.value.value = null;
        }
    });
};

const localSubjects = ref([...props.subjects]);

watch(() => props.subjects, (newVal) => {
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
    
    // Re-assign codes locally for instant feedback
    localSubjects.value.forEach((sub, i) => {
        sub.subject_code = 'S' + String(i + 1).padStart(2, '0');
    });
    
    // Send request to backend
    const subjectIds = localSubjects.value.map(s => s.id);
    router.post(route('subjects.reorder'), { subject_ids: subjectIds }, {
        preserveScroll: true,
        preserveState: true,
    });
    
    draggedIndex = null;
};

const importTemplates = () => {
    if (confirm('តើអ្នកពិតជាចង់នាំចូលមុខវិជ្ជាគំរូទាំងអស់មែនទេ?')) {
        router.post(route('subjects.import-templates'));
    }
};

const deleteSubject = (id) => {
    if (confirm('តើអ្នកពិតជាចង់លុបមុខវិជ្ជានេះមែនទេ?')) {
        router.delete(route('subjects.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="មុខវិជ្ជា" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">បញ្ជីមុខវិជ្ជា</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 space-y-4 sm:space-y-0">
                            <h3 class="text-lg font-medium">គ្រប់គ្រងមុខវិជ្ជា</h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <input type="text" v-model="search" placeholder="ស្វែងរកមុខវិជ្ជា..." class="border-gray-300 rounded-md shadow-sm text-sm" />



                                <PrimaryButton @click="importTemplates" type="button" class="bg-purple-600 hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 border-transparent shadow-sm flex items-center px-4 py-2 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    នាំចូលមុខវិជ្ជាគំរូ
                                </PrimaryButton>

                                <Link :href="route('subjects.create')" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition shadow-sm">
                                    បន្ថែមថ្មី
                                </Link>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-[800px] w-full divide-y divide-gray-100 table-fixed">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-36">លេខកូដ</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ឈ្មោះខ្មែរ</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[200px]">ឈ្មោះអង់គ្លេស</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">អក្សរកាត់</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">ពណ៌</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr v-for="(subject, index) in localSubjects" :key="subject.id" 
                                        class="hover:bg-gray-50 transition-colors cursor-move group"
                                        draggable="true"
                                        @dragstart="onDragStart(index, $event)"
                                        @dragover.prevent
                                        @dragenter.prevent
                                        @drop="onDrop(index)">
                                        <td class="px-6 py-3 whitespace-nowrap flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-400 cursor-move shrink-0 hover:text-gray-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                                            </svg>
                                            <input type="text" v-model="subject.subject_code" @change="updateSubject(subject)" spellcheck="false" class="w-full bg-transparent border border-transparent hover:bg-white hover:border-gray-300 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded px-2 py-1.5 text-sm font-medium text-gray-900 transition-all placeholder-gray-400">
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <input type="text" v-model="subject.khmer_name" @change="updateSubject(subject)" spellcheck="false" class="w-full bg-transparent border border-transparent hover:bg-white hover:border-gray-300 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded px-2 py-1.5 text-sm font-medium text-gray-900 transition-all placeholder-gray-400">
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <input type="text" v-model="subject.english_name" @change="updateSubject(subject)" spellcheck="false" class="w-full bg-transparent border border-transparent hover:bg-white hover:border-gray-300 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded px-2 py-1.5 text-sm text-gray-600 transition-all placeholder-gray-400">
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-center">
                                            <input type="text" v-model="subject.short_name" @change="updateSubject(subject)" spellcheck="false" placeholder="-" 
                                                class="w-16 text-center border rounded px-2 py-1 text-xs font-bold transition-colors uppercase cursor-text hover:brightness-95 focus:ring-2 focus:ring-offset-1"
                                                :style="`color: ${subject.color || '#6b7280'}; border-color: ${subject.color || '#d1d5db'}; background-color: ${(subject.color || '#f3f4f6')}1A;`">
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <div class="flex items-center justify-center border border-gray-200 p-0.5 rounded shadow-sm w-8 h-8 bg-white transition-colors mx-auto hover:border-gray-400">
                                                <input type="color" v-model="subject.color" @change="updateColor(subject)" class="w-full h-full border-0 p-0 bg-transparent block rounded-sm cursor-pointer">
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-center">
                                            <div class="flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="deleteSubject(subject.id)" class="text-red-600 hover:text-red-800 p-1.5 rounded-full hover:bg-red-50 transition-colors" title="លុប">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="localSubjects.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
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
