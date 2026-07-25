<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import axios from 'axios';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    assignments: Object, // Changed to Object for Pagination
    teachers: Array,
    subjects: Array,
    classes: Array,
    shifts: Array,
    grades: Array,
    selectedTeacherId: String,
    selectedSubjectId: String,
    curriculumSubjects: Array,
    takenAssignments: Array,
    summary: Object,
    filters: Object,
    has_filters: Boolean,
});

const form = useForm({
    teacher_id: props.selectedTeacherId || '',
    subject_id: props.selectedSubjectId || '',
    school_class_ids: [],
});

watch(() => form.subject_id, () => {
    form.school_class_ids = [];
});

const availableClasses = computed(() => {
    if (!form.subject_id) return [];
    
    const takenClassIds = props.takenAssignments
        .filter(a => a.subject_id == form.subject_id)
        .map(a => parseInt(a.school_class_id, 10));
        
    return props.classes.filter(c => !takenClassIds.includes(c.id));
});


const totalAssignedHours = computed(() => {
    if (props.selectedTeacherId) {
        return props.summary?.total_hours || 0;
    }
    return null;
});

const selectedTeacherName = computed(() => {
    if (!props.selectedTeacherId) return '';
    const teacher = props.teachers.find(t => t.id == props.selectedTeacherId);
    return teacher ? teacher.khmer_name : '';
});

const submit = () => {
    form.post(route('teaching-assignments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.teacher_id = '';
            form.school_class_ids = [];
            updateFilters();
        },
    });
};

const deleteAssignment = (id) => {
    if(confirm('តើអ្នកពិតជាចង់លុបការចាត់តាំងនេះមែនទេ?')) {
        useForm().delete(route('teaching-assignments.destroy', id), {
            preserveScroll: true,
        });
    }
};

const updateFilters = () => {
    router.get(route('teaching-assignments.index'), { 
        teacher_id: form.teacher_id || '', 
        subject_id: form.subject_id || '',
    }, { preserveState: true, preserveScroll: true });
};

const onLeftPanelChange = () => {
    updateFilters();
};

const onDragStart = (event, assignmentId) => {
    event.dataTransfer.setData('assignmentId', assignmentId);
    event.dataTransfer.effectAllowed = 'move';
};

const onDrop = (event, newTeacherId, newSubjectId) => {
    const assignmentId = event.dataTransfer.getData('assignmentId');
    if (assignmentId) {
        useForm({
            assignment_id: assignmentId,
            new_teacher_id: newTeacherId,
            new_subject_id: newSubjectId
        }).post(route('teaching-assignments.reassign'), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const formattedTeachers = computed(() => {
    return props.teachers.map(t => ({
        ...t,
        display_name: t.khmer_name
    }));
});

const getBadgeStyle = (classCode) => {
    if (classCode.endsWith('1')) {
        // Morning shift
        return {
            wrapper: 'border-blue-200 hover:border-blue-400 bg-blue-50/50',
            text: 'text-blue-700'
        };
    } else if (classCode.endsWith('2')) {
        // Afternoon shift
        return {
            wrapper: 'border-orange-200 hover:border-orange-400 bg-orange-50/50',
            text: 'text-orange-700'
        };
    }
    // Default
    return {
        wrapper: 'border-gray-200 hover:border-gray-400 bg-gray-50',
        text: 'text-gray-700'
    };
};

const deleteGroup = (teacherId, subjectId) => {
    if (confirm('តើអ្នកពិតជាចង់លុបម៉ោងបង្រៀនមុខវិជ្ជានេះរបស់គ្រូនេះមែនទេ?')) {
        router.delete(route('teaching-assignments.group.destroy', { teacher: teacherId, subject: subjectId }), {
            preserveScroll: true,
        });
    }
};

const confirmWord = ref('');
const showTruncateModal = ref(false);

const truncateData = () => {
    if (confirmWord.value === 'លុប' || confirmWord.value.toUpperCase() === 'DELETE') {
        router.delete(route('teaching-assignments.truncate'), {
            preserveScroll: true,
            onSuccess: () => {
                showTruncateModal.value = false;
                confirmWord.value = '';
            }
        });
    } else {
        alert('ពាក្យបញ្ជាក់មិនត្រឹមត្រូវទេ! សូមវាយពាក្យថា "លុប" ឬ "DELETE" ។');
    }
};

const showClassCheckModal = ref(false);
const checkClassId = ref('');
const checkClassResult = ref(null);
const loadingClassCheck = ref(false);

const checkClassStatus = async () => {
    if (!checkClassId.value) return;
    
    loadingClassCheck.value = true;
    checkClassResult.value = null;
    
    try {
        const response = await axios.get(route('teaching-assignments.check-class', { class_id: checkClassId.value }));
        checkClassResult.value = response.data;
    } catch (error) {
        if (error.response && error.response.data && error.response.data.error) {
            alert(error.response.data.error);
        } else {
            alert('មានបញ្ហាក្នុងការទាញយកទិន្នន័យ។');
        }
    } finally {
        loadingClassCheck.value = false;
    }
};

watch(checkClassId, (newVal) => {
    if (newVal) {
        showClassCheckModal.value = true;
        checkClassStatus();
    } else {
        showClassCheckModal.value = false;
        checkClassResult.value = null;
    }
});

const classProgress = computed(() => {
    if (!checkClassResult.value || !checkClassResult.value.subjects) return { required: 0, assigned: 0, percent: 0 };
    
    let required = 0;
    let assigned = 0;
    
    checkClassResult.value.subjects.forEach(subj => {
        if (subj.status !== 'extra') {
            required += subj.required_hours;
            assigned += Math.min(subj.assigned_hours, subj.required_hours);
        }
    });
    
    const percent = required > 0 ? Math.round((assigned / required) * 100) : (checkClassResult.value.subjects.length > 0 ? 100 : 0);
    
    return { required, assigned, percent };
});

const quickAssign = (subjectId) => {
    const cId = checkClassId.value;
    checkClassId.value = '';
    
    form.subject_id = subjectId;
    if (cId) {
        form.school_class_ids = [cId];
    }
};

</script>

<template>
    <Head title="Teaching Assignments" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">ការបែងចែកភារកិច្ចបង្រៀន</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
                <!-- Add Assignment Form -->
                <div class="w-full md:w-5/12 lg:w-4/12">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-4">ចាត់តាំងគ្រូបង្រៀន</h3>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="relative z-50">
                                <label class="block text-sm font-medium text-gray-700 mb-1">គ្រូបង្រៀន</label>
                                <SearchableSelect 
                                    v-model="form.teacher_id" 
                                    :options="formattedTeachers" 
                                    valueKey="id" 
                                    labelKey="display_name" 
                                    placeholder="-- ជ្រើសរើសគ្រូបង្រៀន --"
                                    @update:modelValue="onLeftPanelChange"
                                />
                                <div v-if="form.errors.teacher_id" class="text-red-500 text-xs mt-1">{{ form.errors.teacher_id }}</div>
                            </div>
                            
                            <div class="relative z-40">
                                <label class="block text-sm font-medium text-gray-700 mb-1">មុខវិជ្ជា</label>
                                <SearchableSelect 
                                    v-model="form.subject_id" 
                                    :options="subjects" 
                                    valueKey="id" 
                                    labelKey="khmer_name" 
                                    placeholder="-- ជ្រើសរើសមុខវិជ្ជា --"
                                    @update:modelValue="onLeftPanelChange"
                                />
                                <div v-if="form.errors.subject_id" class="text-red-500 text-xs mt-1">{{ form.errors.subject_id }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ថ្នាក់រៀន</label>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 border border-gray-200 rounded-lg p-3 max-h-56 overflow-y-auto bg-gray-50 shadow-inner">
                                    <template v-if="availableClasses.length > 0">
                                        <label v-for="cls in availableClasses" :key="cls.id" 
                                               class="relative flex items-center justify-center py-2 px-1 border rounded-md cursor-pointer transition-all duration-200"
                                               :class="form.school_class_ids.includes(cls.id) ? 'bg-blue-50 border-blue-500 text-blue-700 ring-1 ring-blue-500 shadow-sm' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-100 hover:border-gray-300'">
                                            <input type="checkbox" :value="cls.id" v-model="form.school_class_ids" class="sr-only">
                                            <span class="text-sm font-medium">{{ cls.class_code }}</span>
                                        </label>
                                    </template>
                                    <div v-else class="col-span-3 sm:col-span-4 text-center py-4 text-sm text-gray-500">
                                        {{ form.subject_id ? 'គ្មានថ្នាក់រៀនទំនេរសម្រាប់មុខវិជ្ជានេះទេ' : 'សូមជ្រើសរើសមុខវិជ្ជាសិន' }}
                                    </div>
                                </div>
                                <div v-if="form.errors.school_class_ids" class="text-red-500 text-xs mt-1">{{ form.errors.school_class_ids }}</div>
                            </div>





                            <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 text-white py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition mt-4">
                                រក្សាទុកការចាត់តាំង
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Assignment List -->
                <div class="w-full md:w-7/12 lg:w-8/12 space-y-6">

                    <div class="bg-white overflow-visible shadow-sm sm:rounded-lg">
                        <!-- Toolbar -->
                        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 p-6 border-b border-gray-100">
                            <h3 class="font-medium text-gray-800 break-words w-full lg:w-auto flex-1 pr-4">
                                <template v-if="selectedTeacherName">
                                    <span class="block text-sm text-gray-500 mb-0.5">ម៉ោងបង្រៀនរបស់</span>
                                    <span class="block text-xl font-bold text-blue-700">{{ selectedTeacherName }}</span>
                                </template>
                                <template v-else>
                                    <span class="text-lg">ម៉ោងបង្រៀនបច្ចុប្បន្ន</span>
                                </template>
                            </h3>
                            <div class="flex items-center gap-3 w-full lg:w-auto flex-wrap">
                                <div class="w-40 relative z-40">
                                    <SearchableSelect 
                                        v-model="checkClassId" 
                                        :options="classes" 
                                        valueKey="id" 
                                        labelKey="class_code" 
                                        placeholder="-- ពិនិត្យតាមថ្នាក់ --"
                                    />
                                </div>
                                <button @click="showTruncateModal = true" class="flex-shrink-0 bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded-md transition-colors border border-red-200 shadow-sm" title="លុបទិន្នន័យម៉ោងបង្រៀនទាំងអស់">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div v-if="!has_filters" class="flex flex-col items-center justify-center py-20 px-4 border-t border-gray-200">
                            <div class="bg-gray-50 rounded-full p-6 mb-4 border border-gray-100">
                                <svg class="w-16 h-16 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14v.01M10 10a2 2 0 100-4 2 2 0 000 4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">ស្វែងរកម៉ោងបង្រៀន</h3>
                            <p class="text-gray-500 text-center max-w-md">
                                ដើម្បីរក្សាផ្ទាំងទំព័រឲ្យមានភាពរហ័ស ម៉ោងបង្រៀនទាំងអស់ត្រូវបានលាក់ជាបណ្ដោះអាសន្ន។ <br/>
                                សូមបញ្ចូល <b>ឈ្មោះគ្រូ មុខវិជ្ជា</b> ឬជ្រើសរើស <b>កម្រិតថ្នាក់</b> ដើម្បីបង្ហាញទិន្នន័យ។
                            </p>
                        </div>

                        <div v-else class="overflow-hidden border-t border-gray-200">
                            <table class="w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/4">មុខវិជ្ជា</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/4">ឈ្មោះគ្រូបង្រៀន</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-auto">ថ្នាក់</th>
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr v-for="group in assignments.data" :key="group.teacher_id + '-' + group.subject_id" 
                                        class="hover:bg-gray-50 transition-colors group"
                                        @dragover.prevent
                                        @dragenter.prevent
                                        @drop="onDrop($event, group.teacher_id, group.subject_id)">
                                        
                                        <td class="px-4 py-4 align-top">
                                            <div class="flex items-center gap-2">
                                                <span 
                                                    :style="{ 
                                                        backgroundColor: group.subject_color ? (group.subject_color + '1A') : '#F3F4F6', 
                                                        color: group.subject_color || '#4B5563', 
                                                        borderColor: group.subject_color ? (group.subject_color + '4D') : '#E5E7EB' 
                                                    }" 
                                                    class="px-2.5 py-1 rounded text-sm font-bold border inline-block text-center min-w-[3rem]"
                                                >
                                                    {{ group.subject_code || group.subject_name }}
                                                </span>
                                                <span class="text-sm font-medium text-gray-700">{{ group.subject_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 align-top">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shadow-sm"
                                                     :class="group.teacher_gender === 'M' ? 'bg-blue-400' : 'bg-pink-400'">
                                                    {{ group.teacher_name.charAt(0) }}
                                                </div>
                                                <div class="font-bold text-gray-900">
                                                    {{ group.teacher_name }}
                                                    <div class="text-xs text-gray-500 font-normal mt-0.5">{{ group.teacher_english }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <div class="flex flex-wrap gap-1.5">
                                                <div v-for="assignment in group.assignments" :key="assignment.id" 
                                                     class="inline-flex items-center bg-white border shadow-sm px-2 py-1 rounded text-xs font-medium cursor-move hover:shadow transition-all group/badge"
                                                     :class="getBadgeStyle(assignment.class_code).wrapper"
                                                     draggable="true"
                                                     @dragstart="onDragStart($event, assignment.id)"
                                                     title="ទាញទម្លាក់ដើម្បីប្ដូរគ្រូ">
                                                    <span class="font-bold" :class="getBadgeStyle(assignment.class_code).text">
                                                        {{ assignment.class_code }}
                                                    </span>
                                                    <button @click.prevent="deleteAssignment(assignment.id)" class="ml-1 text-gray-400 hover:text-red-500 focus:outline-none transition-colors opacity-0 group-hover/badge:opacity-100" title="លុបម៉ោងនេះ">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </div>

                                                <div v-if="group.assignments.length === 0" class="text-gray-400 text-xs italic py-1">
                                                    គ្មានថ្នាក់
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 text-center align-top">
                                            <div class="flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="deleteGroup(group.teacher_id, group.subject_id)" class="text-red-600 hover:text-red-800 p-1.5 rounded-full hover:bg-red-50 transition-colors" title="លុបម៉ោងមុខវិជ្ជានេះ">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="assignments.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">មិនទាន់មានម៉ោងបង្រៀនសម្រាប់គ្រូនេះទេ</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div v-if="has_filters" class="p-4 border-t border-gray-200 flex justify-center">
                            <Pagination :links="assignments.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Truncate Modal -->
        <div v-if="showTruncateModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showTruncateModal = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    លុបទិន្នន័យម៉ោងបង្រៀនទាំងអស់
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        តើអ្នកពិតជាចង់លុបទិន្នន័យម៉ោងបង្រៀនទាំងអស់មែនទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានទេ។ សូមវាយពាក្យថា <span class="font-bold text-red-600">លុប</span> ឬ <span class="font-bold text-red-600">DELETE</span> ខាងក្រោមដើម្បីបញ្ជាក់ការលុប។
                                    </p>
                                    <input type="text" v-model="confirmWord" class="mt-3 shadow-sm focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="វាយពាក្យបញ្ជាក់នៅទីនេះ...">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="truncateData" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm" :disabled="confirmWord !== 'លុប' && confirmWord.toUpperCase() !== 'DELETE'" :class="{'opacity-50 cursor-not-allowed': confirmWord !== 'លុប' && confirmWord.toUpperCase() !== 'DELETE'}">
                            លុបទាំងអស់
                        </button>
                        <button type="button" @click="showTruncateModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            បោះបង់
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Class Check Modal -->
        <div v-if="showClassCheckModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="checkClassId = ''" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-blue-600 px-4 py-3 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            លទ្ធផលរៀបចំបង្រៀនសម្រាប់ថ្នាក់ {{ checkClassResult ? checkClassResult.class_name : '...' }}
                        </h3>
                        <button @click="checkClassId = ''" class="text-white hover:text-blue-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <div v-if="loadingClassCheck" class="text-blue-600 flex flex-col items-center justify-center py-12">
                            <svg class="animate-spin mb-4 h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-lg font-medium">កំពុងទាញយកទិន្នន័យ...</span>
                        </div>

                        <div v-else-if="checkClassResult" class="mt-2">
                            <!-- Progress Bar & Summary -->
                            <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <div class="flex justify-between items-end mb-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 mb-1">វឌ្ឍនភាពនៃការរៀបចំម៉ោង</p>
                                        <p class="text-2xl font-bold text-gray-800">
                                            {{ classProgress.assigned }} <span class="text-base font-normal text-gray-500">/ {{ classProgress.required }} ម៉ោង</span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-2xl font-bold" :class="classProgress.percent === 100 ? 'text-green-600' : 'text-blue-600'">
                                            {{ classProgress.percent }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-2.5 rounded-full transition-all duration-500" 
                                         :class="classProgress.percent === 100 ? 'bg-green-500' : 'bg-blue-600'"
                                         :style="`width: ${classProgress.percent}%`"></div>
                                </div>
                            </div>

                            <div class="flex justify-end items-center mb-4">
                                <div class="flex gap-4 text-xs font-medium">
                                    <span class="flex items-center gap-1 text-green-700"><span class="w-3 h-3 rounded-full bg-green-500"></span> គ្រប់ម៉ោង</span>
                                    <span class="flex items-center gap-1 text-yellow-700"><span class="w-3 h-3 rounded-full bg-yellow-400"></span> មិនទាន់គ្រប់</span>
                                    <span class="flex items-center gap-1 text-red-700"><span class="w-3 h-3 rounded-full bg-red-500"></span> អត់មានគ្រូ</span>
                                </div>
                            </div>

                            <div class="overflow-x-auto border rounded-lg border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">មុខវិជ្ជា</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">គ្រូបង្រៀន</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">ម៉ោងតម្រូវការ</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">ម៉ោងបានរៀបចំ</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">ស្ថានភាព</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="subj in checkClassResult.subjects" :key="subj.subject_id" 
                                            :class="{
                                                'bg-green-50/50': subj.status === 'full',
                                                'bg-yellow-50/50': subj.status === 'partial',
                                                'bg-red-50/50': subj.status === 'missing',
                                                'bg-purple-50/50': subj.status === 'extra'
                                            }">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ subj.subject_name }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <div v-if="subj.teachers && subj.teachers.length > 0" class="flex flex-wrap gap-1">
                                                    <span v-for="(t, i) in subj.teachers" :key="i" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                        {{ t }}
                                                    </span>
                                                </div>
                                                <span v-else class="text-gray-400 italic">មិនទាន់មាន</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{ subj.required_hours }} ម៉ោង</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold"
                                                :class="{
                                                    'text-green-600': subj.status === 'full',
                                                    'text-yellow-600': subj.status === 'partial',
                                                    'text-red-600': subj.status === 'missing',
                                                    'text-purple-600': subj.status === 'extra'
                                                }">
                                                {{ subj.assigned_hours }} ម៉ោង
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <div class="flex flex-col items-center gap-2">
                                                    <span v-if="subj.status === 'full'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        គ្រប់គ្រាន់
                                                    </span>
                                                    <span v-else-if="subj.status === 'partial'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        មិនទាន់គ្រប់
                                                    </span>
                                                    <span v-else-if="subj.status === 'missing'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        អត់មានគ្រូ
                                                    </span>
                                                    <span v-else-if="subj.status === 'extra'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                        លើសកម្មវិធី
                                                    </span>
                                                    
                                                    <button v-if="['missing', 'partial'].includes(subj.status)" 
                                                            @click="quickAssign(subj.subject_id)"
                                                            class="inline-flex items-center px-2 py-1 border border-transparent shadow-sm text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                                        <svg class="-ml-0.5 mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                                        រៀបចំឥឡូវនេះ
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div v-else-if="!loadingClassCheck && checkClassId" class="mt-8 text-center text-gray-500 py-8">
                            សូមរង់ចាំ...
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
