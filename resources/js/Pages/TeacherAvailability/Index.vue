<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    teachers: Array,
    shifts: Array,
    selectedTeacherId: String,
    availabilities: Array,
    remarks: Array,
    documents: Array,
});

const formattedTeachers = computed(() => {
    return props.teachers.map(t => ({
        id: t.id,
        label: t.khmer_name
    }));
});

const selectedTeacher = computed(() => {
    if (!form.teacher_id) return null;
    return props.teachers.find(t => String(t.id) === String(form.teacher_id));
});

const days = [
    { id: 1, name: 'ច័ន្ទ' },
    { id: 2, name: 'អង្គារ' },
    { id: 3, name: 'ពុធ' },
    { id: 4, name: 'ព្រហស្បតិ៍' },
    { id: 5, name: 'សុក្រ' },
    { id: 6, name: 'សៅរ៍' },
];

const form = useForm({
    teacher_id: props.selectedTeacherId || '',
    availabilities: [],
    remarks: [],
});

// Initialize grid based on existing availabilities
const initGrid = () => {
    let grid = [];
    let remarksGrid = [];
    days.forEach(day => {
        const existingRemark = props.remarks ? props.remarks.find(r => r.day_of_week === day.id) : null;
        remarksGrid.push({
            day_of_week: day.id,
            remarks: existingRemark ? existingRemark.remarks : '',
        });

        props.shifts.forEach(shift => {
            const record = props.availabilities.find(a => String(a.day_of_week) === String(day.id) && String(a.shift_id) === String(shift.id));
            let isAvailable = true; // Default
            if (props.availabilities.length > 0) {
                isAvailable = record ? record.is_available : false;
            }
            
            grid.push({
                day_of_week: day.id,
                shift_id: shift.id,
                is_available: isAvailable,
            });
        });
    });
    form.availabilities = grid;
    form.remarks = remarksGrid;
};

if (props.selectedTeacherId) {
    initGrid();
}

watch(() => form.teacher_id, (newVal) => {
    if (newVal !== props.selectedTeacherId) {
        const params = newVal ? { teacher_id: newVal } : {};
        router.get(route('teacher-availabilities.index'), params, {
            preserveState: true,
            preserveScroll: true
        });
    }
});

watch(() => props.selectedTeacherId, (newId) => {
    if (newId) {
        docForm.teacher_id = newId;
        initGrid();
    }
});

const formatPhone = (phone) => {
    if (!phone) return '-';
    let formatted = phone.replace(/(?<!^)\s*(Smart|Cellcard|Metfone|qb|Seatel|Excell)/gi, '\n$1');
    formatted = formatted.replace(/,/g, '\n');
    return formatted.trim();
};

const submit = () => {
    form.post(route('teacher-availabilities.store'), {
        preserveScroll: true,
        preserveState: true,
    });
};

const autoSave = () => {
    submit();
};

const isChecked = (dayId, shiftId) => {
    const item = form.availabilities.find(a => String(a.day_of_week) === String(dayId) && String(a.shift_id) === String(shiftId));
    return item ? item.is_available : false;
};

const toggleCheck = (dayId, shiftId) => {
    const item = form.availabilities.find(a => String(a.day_of_week) === String(dayId) && String(a.shift_id) === String(shiftId));
    if (item) {
        item.is_available = !item.is_available;
        autoSave();
    }
};

const docForm = useForm({
    teacher_id: props.selectedTeacherId || '',
    title: '',
    document: null,
});

const isCompressing = ref(false);

const compressImage = (file, maxSizeMB = 2) => {
    return new Promise((resolve) => {
        if (!file.type.startsWith('image/')) {
            resolve(file);
            return;
        }

        const maxSizeBytes = maxSizeMB * 1024 * 1024;
        if (file.size <= maxSizeBytes) {
            resolve(file);
            return;
        }

        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                const maxDim = 1920;
                
                if (width > height && width > maxDim) {
                    height = Math.round((height * maxDim) / width);
                    width = maxDim;
                } else if (height > maxDim) {
                    width = Math.round((width * maxDim) / height);
                    height = maxDim;
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    if (!blob) {
                        resolve(file);
                        return;
                    }
                    let newFileName = file.name;
                    if (file.type === 'image/png' || file.type === 'image/jpeg' || file.type === 'image/jpg') {
                        newFileName = newFileName.replace(/\.(png|jpeg|jpg)$/i, '.jpg');
                    }
                    const compressedFile = new File([blob], newFileName, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    resolve(compressedFile);
                }, 'image/jpeg', 0.8);
            };
            img.onerror = () => resolve(file);
        };
        reader.onerror = () => resolve(file);
    });
};

const handleFileUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) {
        docForm.document = null;
        return;
    }
    
    isCompressing.value = true;
    try {
        docForm.document = await compressImage(file);
    } finally {
        isCompressing.value = false;
    }
};

const submitDocument = () => {
    docForm.post(route('teacher-documents.store'), {
        preserveScroll: true,
        onSuccess: () => {
            docForm.reset('title', 'document');
            // reset file input visually
            const fileInput = document.getElementById('document_file');
            if (fileInput) fileInput.value = '';
        },
    });
};

const updateDocumentTitle = (doc) => {
    router.put(route('teacher-documents.update', doc.id), {
        title: doc.title
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const deleteDocument = (id) => {
    if (confirm('តើលោកអ្នកពិតជាចង់លុបឯកសារនេះមែនទេ?')) {
        router.delete(route('teacher-documents.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Teacher Availability" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">ម៉ោងទំនេររបស់គ្រូ</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row gap-6 items-stretch">
                    <!-- Left Column -->
                    <div class="w-full lg:w-1/3 space-y-6">
                        <!-- Select Teacher Card -->
                        <div class="bg-white overflow-visible shadow-sm sm:rounded-lg p-6 relative z-10 border border-gray-100">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-3 border-gray-100">ម៉ោងទំនេរគ្រូបង្រៀន</h3>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ជ្រើសរើសគ្រូបង្រៀន</label>
                            <SearchableSelect 
                                v-model="form.teacher_id" 
                                :options="formattedTeachers"
                                labelKey="label"
                                valueKey="id"
                                placeholder="-- ជ្រើសរើសគ្រូបង្រៀន --"
                            />
                        </div>
                        
                        <!-- Teacher Profile Card -->
                        <div v-if="selectedTeacher" class="bg-white shadow-sm sm:rounded-lg p-5 border border-gray-100">
                            <div class="flex items-center gap-5">
                                <!-- Left: Photo -->
                                <div class="shrink-0 h-20 w-20 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-3xl font-bold border-4 border-white shadow-sm">
                                    {{ selectedTeacher.khmer_name.substring(0, 1) }}
                                </div>
                                
                                <!-- Right: Info -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-lg font-bold text-gray-800 mb-2 truncate">{{ selectedTeacher.khmer_name }}</h4>
                                    <div class="space-y-1.5 text-sm text-gray-600">
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 mr-2 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span class="truncate">{{ selectedTeacher.gender === 'M' ? 'ប្រុស' : 'ស្រី' }}</span>
                                        </div>
                                        <div class="flex items-start">
                                            <svg class="h-4 w-4 mr-2 mt-0.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span class="whitespace-pre-line leading-tight">{{ formatPhone(selectedTeacher.phone) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Teacher Documents Card -->
                        <div v-if="selectedTeacher" class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-3 border-gray-100 flex items-center">
                                <svg class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                បញ្ចូលឯកសារថ្មី
                            </h3>
                            
                            <!-- Upload Form -->
                            <form @submit.prevent="submitDocument" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ចំណងជើងឯកសារ</label>
                                    <input type="text" v-model="docForm.title" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="ឧ. សំបុត្រសុំច្បាប់">
                                    <div v-if="docForm.errors.title" class="text-red-500 text-xs mt-1">{{ docForm.errors.title }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ជ្រើសរើសឯកសារ</label>
                                    <input type="file" id="document_file" @change="handleFileUpload" required class="w-full text-sm text-gray-500 file:cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" :disabled="isCompressing">
                                    <div v-if="isCompressing" class="text-blue-600 text-xs mt-1 animate-pulse">កំពុងបង្រួមទំហំរូបភាព...</div>
                                    <div v-if="docForm.errors.document" class="text-red-500 text-xs mt-1">{{ docForm.errors.document }}</div>
                                </div>
                                <div class="pt-2">
                                    <button type="submit" :disabled="docForm.processing || isCompressing" class="w-full bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out text-sm flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg v-if="!isCompressing" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        <svg v-else class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ isCompressing ? 'កំពុងដំណើរការ...' : 'រក្សាទុកឯកសារ' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="w-full lg:w-2/3">
                        <div v-if="form.teacher_id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-3 border-gray-100">សូមធីកក្នុងប្រអប់ដែលគ្រូអាចបង្រៀនបាន</h3>
                    <form @submit.prevent="submit">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border">ថ្ងៃ</th>
                                        <th v-for="shift in shifts" :key="shift.id" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase border">
                                            {{ shift.name.split(' (')[0] }}
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase border">ការពណ៌នា</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(day, index) in days" :key="day.id" class="even:bg-gray-50 hover:bg-gray-100 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium border">{{ day.name }}</td>
                                        <td v-for="shift in shifts" :key="shift.id" class="px-6 py-4 whitespace-nowrap text-center border">
                                            <input type="checkbox" 
                                                   :checked="isChecked(day.id, shift.id)"
                                                   @change="toggleCheck(day.id, shift.id)"
                                                   class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        </td>
                                        <td class="px-4 py-2 border align-middle min-w-[250px]">
                                            <input type="text" v-model="form.remarks[index].remarks" @input="autoSave()" spellcheck="false" class="w-full bg-transparent border-0 hover:bg-black/5 focus:ring-1 focus:ring-blue-500 rounded p-1 text-sm transition-colors" placeholder="ពណ៌នា...">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Auto Save enabled, removed save button -->
                    </form>
                    
                    <!-- Document List Table -->
                    <div class="mt-10 border-t border-gray-100 pt-8">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            ឯកសារដែលបាន Upload
                        </h3>
                        
                        <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200 table-fixed">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">កាលបរិច្ឆេទ</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ចំណងជើងឯកសារ</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">ប្រភេទឯកសារ</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-40">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="!documents || documents.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 italic">មិនទាន់មានឯកសារនៅឡើយទេ</td>
                                    </tr>
                                    <tr v-for="doc in documents" :key="doc.id" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-left">
                                            {{ new Date(doc.created_at).toLocaleDateString('en-GB') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <input type="text" v-model="doc.title" @change="updateDocumentTitle(doc)" spellcheck="false" class="w-full bg-transparent border-0 hover:bg-black/5 focus:ring-1 focus:ring-blue-500 rounded p-1 text-sm font-medium text-gray-900 transition-colors cursor-text" title="ចុចដើម្បីកែចំណងជើង">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center uppercase font-bold text-blue-600">
                                            {{ doc.file_type || 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="flex items-center justify-center gap-3">
                                                <a :href="route('teacher-documents.show', doc.id)" target="_blank" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-1.5 rounded transition" title="មើល (View)">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                                <a :href="route('teacher-documents.download', doc.id)" target="_blank" class="text-green-500 hover:text-green-700 bg-green-50 hover:bg-green-100 p-1.5 rounded transition" title="ទាញយក (Download)">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                </a>
                                                <button type="button" @click="deleteDocument(doc.id)" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-1.5 rounded transition" title="លុប (Delete)">
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
                
                <div v-else class="bg-white shadow-sm sm:rounded-lg p-12 flex flex-col items-center justify-center text-center border border-gray-100 h-full min-h-[400px]">
                    <div class="h-24 w-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 border border-gray-100 shadow-sm">
                        <svg class="h-12 w-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">ស្វែងរកម៉ោងទំនេរ</h3>
                    <p class="text-gray-500 max-w-sm leading-relaxed text-sm">សូមជ្រើសរើសឈ្មោះគ្រូបង្រៀននៅផ្នែកខាងឆ្វេង ដើម្បីមើលនិងរៀបចំម៉ោងទំនេររបស់គាត់។</p>
                </div>
            </div>
            </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
