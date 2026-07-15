<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    teachers: Object,
    filters: Object,
});

import debounce from 'lodash/debounce';

const search = ref(props.filters?.search || '');
const employment_type = ref(props.filters?.employment_type || '');
const gender = ref(props.filters?.gender || '');

watch([search, employment_type, gender], debounce(() => {
    router.get(route('teachers.index'), {
        search: search.value,
        employment_type: employment_type.value,
        gender: gender.value
    }, { preserveState: true, replace: true });
}, 300));

const fileInput = ref(null);

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const formData = new FormData();
        formData.append('file', file);
        
        router.post(route('teachers.import'), formData, {
            preserveScroll: true,
            onError: (errors) => {
                if (errors.file) {
                    alert('កំហុស៖ ' + errors.file);
                } else {
                    alert('មានបញ្ហាក្នុងការនាំចូល។ សូមពិនិត្យឯកសារអ្នកម្ដងទៀត។');
                }
            }
        });
    }
    event.target.value = null;
};

const showPasteModal = ref(false);
const pastedData = ref('');
const isSubmittingPaste = ref(false);

const openPasteModal = () => {
    pastedData.value = '';
    showPasteModal.value = true;
};

const closePasteModal = () => {
    showPasteModal.value = false;
    pastedData.value = '';
};

const submitPastedData = () => {
    if (!pastedData.value.trim()) {
        alert('សូមបញ្ចូលទិន្នន័យជាមុនសិន!');
        return;
    }
    
    isSubmittingPaste.value = true;
    router.post(route('teachers.import-paste'), { data: pastedData.value }, {
        preserveScroll: true,
        onSuccess: () => {
            closePasteModal();
            isSubmittingPaste.value = false;
        },
        onError: (errors) => {
            alert('មានបញ្ហាក្នុងការនាំចូល។ សូមពិនិត្យទិន្នន័យអ្នកម្ដងទៀត។');
            isSubmittingPaste.value = false;
        }
    });
};

const updateTeacher = (teacher) => {
    router.put(route('teachers.update', teacher.id), teacher, {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            console.error('Error updating teacher:', errors);
            alert('បរាជ័យក្នុងការកែប្រែ៖ \n' + Object.values(errors).join('\n'));
            router.reload({ only: ['teachers'] });
        }
    });
};

const photoInput = ref(null);
const selectedTeacherForPhoto = ref(null);

const triggerPhotoUpload = (teacher) => {
    selectedTeacherForPhoto.value = teacher;
    photoInput.value.click();
};

const handlePhotoUpload = (event) => {
    const file = event.target.files[0];
    if (!file || !selectedTeacherForPhoto.value) return;

    router.post(route('teachers.update', selectedTeacherForPhoto.value.id), {
        _method: 'put',
        teacher_code: selectedTeacherForPhoto.value.teacher_code,
        khmer_name: selectedTeacherForPhoto.value.khmer_name,
        english_name: selectedTeacherForPhoto.value.english_name,
        gender: selectedTeacherForPhoto.value.gender,
        employment_type: selectedTeacherForPhoto.value.employment_type,
        status: selectedTeacherForPhoto.value.status,
        photo: file,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            event.target.value = null;
        },
        onError: (errors) => {
            alert('បរាជ័យក្នុងការបញ្ជូលរូបថត៖ \n' + Object.values(errors).join('\n'));
            event.target.value = null;
        }
    });
};

const removePhoto = (teacher, event) => {
    event.stopPropagation();
    if (!confirm('តើអ្នកពិតជាចង់លុបរូបថតនេះមែនទេ?')) return;
    
    router.put(route('teachers.update', teacher.id), {
        ...teacher,
        remove_photo: true
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Head title="គ្រូបង្រៀន" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">បញ្ជីគ្រូបង្រៀន</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 space-y-4 sm:space-y-0">
                            <h3 class="text-lg font-medium">គ្រប់គ្រងគ្រូបង្រៀន</h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <input type="text" v-model="search" placeholder="ស្វែងរកគ្រូ..." class="border-gray-300 rounded-md shadow-sm text-sm" />
                                <select v-model="employment_type" class="border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="">គ្រប់ប្រភេទការងារ</option>
                                    <option value="Full-Time">ពេញម៉ោង</option>
                                    <option value="Part-Time">ក្រៅម៉ោង</option>
                                    <option value="Visiting">គ្រូអញ្ជើញ</option>
                                </select>
                                <select v-model="gender" class="border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="">គ្រប់ភេទ</option>
                                    <option value="M">ប្រុស</option>
                                    <option value="F">ស្រី</option>
                                </select>

                                <a :href="route('teachers.template')" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-medium hover:bg-emerald-700 transition shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    ទាញយកគំរូ CSV
                                </a>
                                <button @click="triggerFileInput" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-md text-sm font-medium hover:bg-amber-600 transition shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    CSV
                                </button>
                                <button @click="openPasteModal" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-md text-sm font-medium hover:bg-purple-700 transition shadow-sm" title="Copy ពី Excel មក Paste បញ្ចូលទីនេះផ្ទាល់">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                    Paste ពី Excel
                                </button>
                                <input type="file" ref="fileInput" @change="handleFileUpload" accept=".csv" class="hidden" />
                                <input type="file" ref="photoInput" @change="handlePhotoUpload" accept="image/*" class="hidden" />

                                <Link :href="route('teachers.create')" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                                    បន្ថែមថ្មី
                                </Link>
                                <Link v-if="teachers.data.length > 0" :href="route('teachers.truncate')" method="delete" as="button" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 transition shadow-sm" preserve-scroll @click="(e) => { if(!confirm('តើអ្នកពិតជាចង់លុបទិន្នន័យគ្រូបង្រៀនទាំងអស់មែនទេ? ទិន្នន័យដែលលុបហើយមិនអាចទាញមកវិញបានទេ។')) e.preventDefault(); }">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    លុបទាំងអស់
                                </Link>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">អត្តលេខ</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">គោត្តនាម និងនាមខ្លួន</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">អក្សរឡាតាំង</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ភេទ</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ប្រភេទការងារ</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">លេខទូរសព្ទ</th>
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="teachers.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">ពុំមានទិន្នន័យ</td>
                                    </tr>
                                    <tr v-for="teacher in teachers.data" :key="teacher.id" class="even:bg-gray-50 hover:bg-gray-100 transition-colors">
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <input type="text" v-model="teacher.teacher_code" @change="updateTeacher(teacher)" spellcheck="false" class="w-full min-w-[50px] border-0 rounded p-1 text-sm font-medium transition-colors bg-transparent hover:bg-black/5 focus:ring-1 focus:ring-blue-500 text-blue-600">
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 relative group cursor-pointer" @click="triggerPhotoUpload(teacher)">
                                                    <img v-if="teacher.photo" :src="`/storage/${teacher.photo}`" @error="teacher.photo = null" alt="" class="h-10 w-10 rounded-full object-cover">
                                                    <div v-else class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 font-bold text-lg">
                                                        {{ teacher.english_name ? teacher.english_name.charAt(0).toUpperCase() : (teacher.khmer_name ? teacher.khmer_name.charAt(0) : '?') }}
                                                    </div>
                                                    <!-- Hover overlay -->
                                                    <div class="absolute inset-0 bg-black bg-opacity-40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                    </div>
                                                    
                                                    <!-- Remove photo icon -->
                                                    <button v-if="teacher.photo" @click.stop="removePhoto(teacher, $event)" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600 shadow-sm" title="លុបរូបថត">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="ml-3 w-full">
                                                    <input type="text" v-model="teacher.khmer_name" @change="updateTeacher(teacher)" spellcheck="false" class="w-full min-w-[120px] border-0 rounded p-1 text-sm font-medium transition-colors bg-transparent hover:bg-black/5 focus:ring-1 focus:ring-blue-500 text-gray-900">
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <input type="text" v-model="teacher.english_name" @change="updateTeacher(teacher)" spellcheck="false" class="w-full min-w-[120px] border-0 rounded p-1 text-sm transition-colors bg-transparent hover:bg-black/5 focus:ring-1 focus:ring-blue-500 text-gray-900">
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <select v-model="teacher.gender" @change="updateTeacher(teacher)" class="bg-transparent border-0 rounded p-1 text-sm w-full min-w-[60px] transition-colors cursor-pointer hover:bg-black/5 focus:ring-1 focus:ring-blue-500 text-gray-900">
                                                <option value="M">ប្រុស</option>
                                                <option value="F">ស្រី</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <select v-model="teacher.employment_type" @change="updateTeacher(teacher)" class="bg-transparent border-0 rounded p-1 text-sm w-full min-w-[90px] transition-colors cursor-pointer hover:bg-black/5 focus:ring-1 focus:ring-blue-500 text-gray-900">
                                                <option value="Full-Time">ពេញម៉ោង</option>
                                                <option value="Part-Time">ក្រៅម៉ោង</option>
                                                <option value="Visiting">គ្រូអញ្ជើញ</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <textarea v-model="teacher.phone" @change="updateTeacher(teacher)" spellcheck="false" placeholder="បញ្ចូលលេខទូរសព្ទ" rows="1" class="w-full border-0 rounded p-1 text-sm min-w-[100px] resize-none leading-snug transition-colors bg-transparent hover:bg-black/5 focus:ring-1 focus:ring-blue-500 text-gray-900"></textarea>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center justify-end gap-1">
                                                <a :href="route('teacher-availabilities.index', { teacher_id: teacher.id })" class="text-emerald-600 hover:text-emerald-800 p-1.5 rounded hover:bg-emerald-50 transition-colors" title="ឯកសារយោង និងម៉ោងទំនេរ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                    </svg>
                                                </a>
                                                    <Link :href="route('teachers.edit', teacher.id)" class="text-blue-500 hover:text-blue-700 p-1.5 rounded hover:bg-blue-50 transition-colors" title="កែតម្រូវលម្អិត">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                          <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                        </svg>
                                                    </Link>

                                                    <Link :href="route('teachers.destroy', teacher.id)" method="delete" as="button" class="text-red-500 hover:text-red-700 p-1.5 rounded hover:bg-red-50 transition-colors" title="លុប">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                          <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                        </svg>
                                                    </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6 flex justify-between items-center" v-if="teachers.links && teachers.links.length > 3">
                            <div class="text-sm text-gray-500">
                                បង្ហាញពី {{ teachers.from }} ដល់ {{ teachers.to }} នៃ {{ teachers.total }} គ្រូបង្រៀនសរុប
                            </div>
                            <div class="flex flex-wrap shadow-sm rounded-md">
                                <template v-for="(link, p) in teachers.links" :key="p">
                                    <div v-if="link.url === null" class="px-4 py-2 text-sm text-gray-400 bg-white border border-gray-300 first:rounded-l-md last:rounded-r-md" v-html="link.label" />
                                    <Link v-else :href="link.url" class="px-4 py-2 text-sm border focus:outline-none transition-colors first:rounded-l-md last:rounded-r-md" :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'" v-html="link.label" preserve-scroll />
                                </template>
                            </div>
                        </div>

                        <!-- Paste Modal -->
                        <Modal :show="showPasteModal" @close="closePasteModal" maxWidth="3xl">
                            <div class="p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">ទាញទិន្នន័យចូលដោយផ្ទាល់ពី Excel (Copy-Paste)</h2>
                                <p class="text-sm text-gray-600 mb-4">
                                    សូមធ្វើការ <b>Copy</b> ទិន្នន័យពី Excel រួច <b>Paste</b> ចូលក្នុងប្រអប់ខាងក្រោមនេះ៖ <br/>
                                    <span class="text-xs text-gray-500">(តម្រូវឲ្យមានជួរឈរតាមលំដាប់៖ អត្តលេខ, ឈ្មោះខ្មែរ, ឈ្មោះឡាតាំង, ភេទ, ប្រភេទការងារ, លេខទូរសព្ទ)</span>
                                </p>
                                
                                <textarea 
                                    v-model="pastedData" 
                                    rows="10" 
                                    class="w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm text-sm font-mono whitespace-pre"
                                    placeholder="Paste ទិន្នន័យនៅទីនេះ... (Ctrl+V)"></textarea>

                                <div class="mt-6 flex justify-end gap-3">
                                    <button @click="closePasteModal" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200 transition">
                                        បោះបង់
                                    </button>
                                    <button @click="submitPastedData" :disabled="isSubmittingPaste" class="px-4 py-2 bg-purple-600 text-white rounded-md text-sm font-medium hover:bg-purple-700 transition disabled:opacity-50">
                                        <span v-if="isSubmittingPaste">កំពុងរក្សាទុក...</span>
                                        <span v-else>ទាញទិន្នន័យចូល</span>
                                    </button>
                                </div>
                            </div>
                        </Modal>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
