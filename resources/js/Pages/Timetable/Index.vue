<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    shifts: Array,
    subjects: Array,
    classes: Array,
    periods: Array,
    teachers: Array,
    slots: [Array, Object],
    assignments: Array,
    shiftId: [String, Number],
    teacherId: [String, Number],
    teacherSubjectCodes: Object,
    classAssignmentsDetail: Object,
    teacherWorkloads: Array,
    classStats: Object,
});

const isFullScreen = ref(false);
const toggleFullScreen = () => {
    isFullScreen.value = !isFullScreen.value;
};

const showAllPeriods = ref(false);
const filteredPeriods = computed(() => {
    if (showAllPeriods.value || !filterForm.shift_id || filterForm.shift_id === 'all') {
        return props.periods;
    }
    return props.periods.filter(p => p.shift_id == filterForm.shift_id);
});

const isClassModalOpen = ref(false);
const selectedClassForModal = ref(null);
const showAllSidebarSubjects = ref(false);

const isTeacherModalOpen = ref(false);
const showAllSidebarTeachers = ref(false);

const selectedSubjectId = ref(null);

const openClassModal = (schoolClass) => {
    selectedClassForModal.value = schoolClass;
    isClassModalOpen.value = true;
};

const selectTeacherFromModal = (teacherId) => {
    isClassModalOpen.value = false;
    isTeacherModalOpen.value = false;
    filterForm.teacher_id = teacherId;
    onFilterChange();
};

const filterForm = useForm({
    shift_id: props.shiftId || '',
    teacher_id: props.teacherId || '',
    level_id: new URLSearchParams(window.location.search).get('level_id') || '',
});

const subjectFilter = ref('');

const teacherOptions = computed(() => {
    let filteredTeachers = props.teachers;
    if (subjectFilter.value) {
        filteredTeachers = filteredTeachers.filter(t => 
            t.teaching_assignments && t.teaching_assignments.some(a => a.subject_id == subjectFilter.value)
        );
    }
    return filteredTeachers.map(t => ({
        id: t.id,
        name: t.khmer_name
    }));
});

const toggleForm = useForm({
    teacher_id: '',
    school_class_id: '',
    day_of_week: '',
    period_id: '',
    subject_id: '',
});

watch(() => filterForm.teacher_id, (newVal) => {
    if (newVal) {
        const tAssignments = (props.assignments || []).filter(a => a.teacher_id == newVal);
        if (tAssignments.length > 0) {
            let subjectHours = {};
            tAssignments.forEach(a => {
                subjectHours[a.subject_id] = (subjectHours[a.subject_id] || 0) + a.weekly_hours;
            });
            let maxHours = -1;
            let maxSubjectId = null;
            for (const [subjId, hours] of Object.entries(subjectHours)) {
                if (hours > maxHours) {
                    maxHours = hours;
                    maxSubjectId = subjId;
                }
            }
            selectedSubjectId.value = maxSubjectId;
        } else {
            selectedSubjectId.value = null;
        }
    } else {
        selectedSubjectId.value = null;
    }
}, { immediate: true });

const toastMessage = ref('');
const toastType = ref(''); // 'success' or 'error'
let toastTimeout = null;

const showToast = () => {
    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        toastMessage.value = '';
    }, 3000);
};

const clearAllTimetables = () => {
    if (confirm('តើអ្នកពិតជាចង់លុបកាលវិភាគទាំងអស់ដែលបានរៀបចំមែនទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានទេ។')) {
        router.delete(route('timetables.slots.truncate'), {
            preserveScroll: true,
            preserveState: false
        });
    }
};

const onFilterChange = () => {
    filterForm.get(route('timetables.index'), { preserveState: true, preserveScroll: true });
};

const printTimetable = () => {
    window.print();
};

const days = [
    { id: 1, name: 'ច័ន្ទ' },
    { id: 2, name: 'អង្គារ' },
    { id: 3, name: 'ពុធ' },
    { id: 4, name: 'ព្រហស្បតិ៍' },
    { id: 5, name: 'សុក្រ' },
    { id: 6, name: 'សៅរ៍' },
];

const dayColors = {
    1: { dayBg: 'bg-amber-500', dayText: 'text-white', periodBg: 'bg-amber-50', periodText: 'text-amber-900', timeText: 'text-amber-700' }, // Monday: លឿងចាស់
    2: { dayBg: 'bg-purple-600', dayText: 'text-white', periodBg: 'bg-purple-50', periodText: 'text-purple-900', timeText: 'text-purple-700' }, // Tuesday: ស្វាយ
    3: { dayBg: 'bg-lime-600', dayText: 'text-white', periodBg: 'bg-lime-50', periodText: 'text-lime-900', timeText: 'text-lime-700' }, // Wednesday: ស៊ីលាប
    4: { dayBg: 'bg-green-600', dayText: 'text-white', periodBg: 'bg-green-50', periodText: 'text-green-900', timeText: 'text-green-700' }, // Thursday: បៃតង
    5: { dayBg: 'bg-blue-600', dayText: 'text-white', periodBg: 'bg-blue-50', periodText: 'text-blue-900', timeText: 'text-blue-700' }, // Friday: ខៀវ
    6: { dayBg: 'bg-rose-900', dayText: 'text-white', periodBg: 'bg-rose-50', periodText: 'text-rose-900', timeText: 'text-rose-700' }, // Saturday: អំពិលទុំ
};

const getDayColors = (dayId) => {
    return dayColors[dayId] || { dayBg: 'bg-gray-100', dayText: 'text-gray-700', periodBg: 'bg-gray-50', periodText: 'text-gray-700', timeText: 'text-gray-500' };
};

const toggleSlot = (classId, dayId, periodId) => {
    if (activeParkedSlot.value) {
        if (activeParkedSlot.value.classId !== classId) {
            toastMessage.value = `សូមជ្រើសរើសប្រអប់ក្នុងថ្នាក់ ${activeParkedSlot.value.className}`;
            toastType.value = 'error';
            showToast();
            return;
        }
        
        const slotsArray = Array.isArray(props.slots) ? props.slots : Object.values(props.slots || {});
        const isBusy = slotsArray.some(s => 
            s.day_of_week === dayId && 
            s.period_id === periodId && 
            s.teaching_assignment.teacher_id == activeParkedSlot.value.teacherId
        );
        
        if (isBusy) {
            toastMessage.value = 'គ្រូនេះជាប់បង្រៀននៅម៉ោងនេះហើយ!';
            toastType.value = 'error';
            showToast();
            return;
        }

        toggleForm.teacher_id = activeParkedSlot.value.teacherId;
        toggleForm.school_class_id = classId;
        toggleForm.day_of_week = dayId;
        toggleForm.period_id = periodId;
        toggleForm.subject_id = activeParkedSlot.value.subjectId;
        
        toggleForm.post(route('timetables.slots.toggle'), {
            preserveScroll: true,
            onSuccess: (page) => {
                activeParkedSlot.value = null;
            }
        });
        return;
    }

    if (!filterForm.teacher_id) {
        alert('សូមជ្រើសរើសគ្រូបង្រៀនជាមុនសិន ឬចុចម៉ោងក្នុងឃ្លាំងផ្អាក ដើម្បីបញ្ចូល។');
        return;
    }
    
    // Check if cell is empty or belongs to someone else, and teacher is busy elsewhere
    const slot = gridData.value[dayId][periodId][classId];
    if ((!slot || slot.teaching_assignment.teacher_id != filterForm.teacher_id) && teacherBusySlots.value.has(`${dayId}-${periodId}`)) {
        toastMessage.value = 'គ្រូនេះជាប់បង្រៀននៅម៉ោងនេះហើយ!';
        toastType.value = 'error';
        showToast();
        return;
    }

    if (!slot && isClassSubjectFulfilled(classId)) {
        return;
    }
    
    toggleForm.teacher_id = filterForm.teacher_id;
    toggleForm.school_class_id = classId;
    toggleForm.day_of_week = dayId;
    toggleForm.period_id = periodId;
    toggleForm.subject_id = selectedSubjectId.value;
    
    toggleForm.post(route('timetables.slots.toggle'), {
        preserveScroll: true
    });
};

// Organize slots into a 3D grid: day -> period -> class -> slot
const gridData = computed(() => {
    let grid = {};
    days.forEach(day => {
        grid[day.id] = {};
        props.periods.forEach(p => {
            grid[day.id][p.id] = {};
        });
    });

    const slotsArray = Array.isArray(props.slots) ? props.slots : Object.values(props.slots || {});
    slotsArray.forEach(slot => {
        if (grid[slot.day_of_week] && grid[slot.day_of_week][slot.period_id]) {
            grid[slot.day_of_week][slot.period_id][slot.teaching_assignment.school_class_id] = slot;
        }
    });

    return grid;
});

const teacherBusySlots = computed(() => {
    const busy = new Map();
    if (!filterForm.teacher_id) return busy;
    
    for (const dayId in gridData.value) {
        for (const periodId in gridData.value[dayId]) {
            for (const classId in gridData.value[dayId][periodId]) {
                const slot = gridData.value[dayId][periodId][classId];
                if (slot && slot.teaching_assignment && slot.teaching_assignment.teacher_id == filterForm.teacher_id) {
                    const subjectKey = props.teacherSubjectCodes[filterForm.teacher_id + '_' + slot.teaching_assignment.subject_id] || (slot.teaching_assignment.subject?.short_name || slot.teaching_assignment.subject?.khmer_name);
                    
                    let className = slot.teaching_assignment.school_class?.class_code || slot.teaching_assignment.school_class?.name;
                    if (!className) {
                        const c = props.classes?.find(cls => cls.id === slot.teaching_assignment.school_class_id);
                        className = c ? (c.class_code || c.name) : 'មិនស្គាល់';
                    }
                    
                    busy.set(`${dayId}-${periodId}`, {
                        subjectKey,
                        className,
                        color: slot.teaching_assignment.subject?.color || '#9CA3AF'
                    });
                }
            }
        }
    }
    return busy;
});

// Teacher assignment stats: how many hours assigned vs required
const teacherStatsGrouped = computed(() => {
    if (!filterForm.teacher_id) return { grandTotalAssigned: 0, grandTotalRequired: 0, groups: [] };
    const tAssignments = (props.assignments || []).filter(a => a.teacher_id == filterForm.teacher_id);
    
    let grandTotalAssigned = 0;
    let grandTotalRequired = 0;
    let grouped = {};
    
    tAssignments.forEach(a => {
        // Count how many slots are using this assignment across ALL shifts
        const assignedCount = a.assigned_hours || 0;
        
        grandTotalAssigned += assignedCount;
        grandTotalRequired += a.weekly_hours;
        
        const subjectId = a.subject_id;
        const subjectKey = props.teacherSubjectCodes[filterForm.teacher_id + '_' + a.subject_id] || (a.subject?.short_name || a.subject?.khmer_name);
        
        if (!grouped[subjectKey]) {
            grouped[subjectKey] = { subjectId: subjectId, subjectName: a.subject?.khmer_name, classes: [], totalAssigned: 0, totalRequired: 0, color: a.subject?.color || '#3B82F6' };
        }
        if (grouped[subjectKey].subjectName === undefined && a.subject?.khmer_name) {
            grouped[subjectKey].subjectName = a.subject?.khmer_name;
        }
        
        grouped[subjectKey].classes.push({
            id: a.id,
            classId: a.school_class_id,
            className: a.school_class?.class_code || a.school_class?.name,
            required: a.weekly_hours,
            assigned: assignedCount,
        });
        grouped[subjectKey].totalAssigned += assignedCount;
        grouped[subjectKey].totalRequired += a.weekly_hours;
    });
    
    const extractGrade = (className) => {
        if (!className) return 0;
        const match = className.match(/^(\d+)/);
        return match ? parseInt(match[1]) : 0;
    };
    
    return {
        grandTotalAssigned,
        grandTotalRequired,
        groups: Object.entries(grouped)
            .sort((a, b) => a[0].localeCompare(b[0]))
            .map(([subjectKey, data]) => {
                data.classes.sort((a, b) => {
                    const gradeA = extractGrade(a.className);
                    const gradeB = extractGrade(b.className);
                    if (gradeA !== gradeB) {
                        return gradeA - gradeB; // Ascending grade
                    }
                    return (a.className || '').localeCompare(b.className || ''); // Ascending letter
                });
                const subj = props.subjects.find(s => s.id === data.subjectId);
                const subjectName = subj ? subj.khmer_name : data.subjectName;
                return {
                    subjectId: data.subjectId,
                    subjectName: subjectName,
                    subjectKey,
                    color: data.color,
                    totalAssigned: data.totalAssigned,
                    totalRequired: data.totalRequired,
                    classes: data.classes
                };
            })
    };
});

const activeParkedSlot = ref(null);

const parkedSlots = computed(() => {
    let parked = [];
    
    props.classes.forEach(c => {
        const assignments = props.classAssignmentsDetail[c.id] || [];
        assignments.forEach(a => {
            if (filterForm.teacher_id && a.teacher_id != filterForm.teacher_id) {
                return; // Filter by teacher if selected
            }
            
            const currentAssigned = a.assigned_hours || 0;
            
            if (currentAssigned < a.weekly_hours) {
                for (let i = 0; i < (a.weekly_hours - currentAssigned); i++) {
                    parked.push({
                        assignmentId: a.id,
                        classId: c.id,
                        className: c.class_code || c.name,
                        subjectKey: a.subject_code,
                        subjectName: a.subject_name,
                        subjectId: props.assignments.find(ass => ass.id === a.id)?.subject_id,
                        teacherId: a.teacher_id,
                        teacherName: a.teacher_name,
                        color: a.subject_color,
                        uid: `parked-${a.id}-${i}`
                    });
                }
            }
        });
    });
    
    return parked;
});

const toggleParkedSlotSelection = (slot) => {
    if (activeParkedSlot.value && activeParkedSlot.value.uid === slot.uid) {
        activeParkedSlot.value = null; // deselect
    } else {
        activeParkedSlot.value = slot;
    }
};

const selectSidebarSlot = (assignment) => {
    if (assignment.assigned_hours >= assignment.weekly_hours) return;
    
    const uid = `parked-${assignment.id}-0`;
    
    if (activeParkedSlot.value && activeParkedSlot.value.uid === uid) {
        activeParkedSlot.value = null; // deselect
    } else {
        activeParkedSlot.value = {
            assignmentId: assignment.id,
            classId: selectedClassForModal.value.id,
            className: selectedClassForModal.value.class_code || selectedClassForModal.value.name,
            subjectKey: assignment.subject_code,
            subjectName: assignment.subject_name || props.assignments.find(a => a.id === assignment.id)?.subject?.khmer_name || assignment.subject_code,
            subjectId: assignment.subject_id || props.assignments.find(a => a.id === assignment.id)?.subject_id,
            teacherId: assignment.teacher_id,
            teacherName: assignment.teacher_name,
            color: assignment.subject_color,
            uid: uid
        };

        // Auto-select subject and teacher in global filters to speed up assignment
        filterForm.teacher_id = assignment.teacher_id;
        subjectFilter.value = assignment.subject_id || props.assignments.find(a => a.id === assignment.id)?.subject_id || '';
        onFilterChange();

        isClassModalOpen.value = false;
    }
};


const swapForm = useForm({
    source_slot_id: null,
    target_class_id: null,
    target_day_id: null,
    target_period_id: null,
});

const isEditingCell = ref(false);

const formatPhone = (phone) => {
    if (!phone) return 'មិនមានលេខទូរសព្ទ';
    let formatted = phone.replace(/,\s*/g, ' | ');
    formatted = formatted.replace(/(\d)\s+([A-Za-z])/g, '$1 | $2');
    return formatted;
};

const isDraggingSlot = ref(false);

const onDragStart = (event, slotId, classId, isFromHolding = false, subjectId = null, teacherId = null) => {
    isDraggingSlot.value = true;
    const payload = JSON.stringify({ slotId, classId, isFromHolding, subjectId, teacherId });
    event.dataTransfer.setData('text/plain', payload);
    event.dataTransfer.effectAllowed = 'move';
};

const onDragEnd = () => {
    isDraggingSlot.value = false;
};

const onDropSlot = (event, targetClassId, targetDayId, targetPeriodId) => {
    let data;
    try {
        data = JSON.parse(event.dataTransfer.getData('text/plain'));
    } catch(e) {
        toastMessage.value = 'មិនអាចអានទិន្នន័យបានទេ សូមសាកល្បងម្ដងទៀត។';
        toastType.value = 'error';
        showToast();
        return;
    }
    
    if (!data) return;

    if (data.isFromHolding) {
        if (data.classId != targetClassId) {
            toastMessage.value = 'មិនអាចទាញដូរទីតាំងឆ្លងថ្នាក់បានទេ!';
            toastType.value = 'error';
            showToast();
            return;
        }
        
        toggleForm.teacher_id = data.teacherId || filterForm.teacher_id;
        toggleForm.school_class_id = targetClassId;
        toggleForm.day_of_week = targetDayId;
        toggleForm.period_id = targetPeriodId;
        toggleForm.subject_id = data.subjectId;
        
        toggleForm.post(route('timetables.slots.toggle'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.flash.error) {
                    toastMessage.value = page.props.flash.error;
                    toastType.value = 'error';
                    showToast();
                } else if (page.props.flash.success) {
                    toastMessage.value = 'បានបន្ថែមម៉ោងបង្រៀនដោយជោគជ័យ។';
                    toastType.value = 'success';
                    showToast();
                }
            }
        });
        return;
    }

    if (!data.slotId) return;

    const sourceSlotId = data.slotId;
    const sourceClassId = data.classId;
    
    if (sourceClassId != targetClassId) {
        toastMessage.value = 'មិនអាចទាញដូរទីតាំងឆ្លងថ្នាក់បានទេ!';
        toastType.value = 'error';
        showToast();
        return;
    }
    
    swapForm.source_slot_id = sourceSlotId;
    swapForm.target_class_id = targetClassId;
    swapForm.target_day_id = targetDayId;
    swapForm.target_period_id = targetPeriodId;
    
    swapForm.post(route('timetables.slots.swap'), {
        preserveScroll: true,
        onError: (errors) => {
            toastMessage.value = 'បរាជ័យ៖ ' + Object.values(errors).join(', ');
            toastType.value = 'error';
            showToast();
        }
    });
};

const parkSlot = (slotId) => {
    if (confirm('តើអ្នកពិតជាចង់ដកម៉ោងនេះទៅទុកក្នុង "ឃ្លាំងផ្អាក" សិនមែនទេ?')) {
        router.delete(route('timetables.slots.destroy', slotId), {
            preserveScroll: true,
            onError: () => {
                toastMessage.value = 'បរាជ័យក្នុងការដកម៉ោងទុកសិន។';
                toastType.value = 'error';
                showToast();
            }
        });
    }
};

const onDropHolding = (event) => {
    let data;
    try {
        data = JSON.parse(event.dataTransfer.getData('text/plain'));
    } catch(e) {
        return;
    }
    if (!data || data.isFromHolding || !data.slotId) return;
    
    parkSlot(data.slotId);
};

const isClassSubjectFulfilled = (classId) => {
    if (!filterForm.teacher_id || !selectedSubjectId.value) return false;
    const group = teacherStatsGrouped.value.groups.find(g => g.subjectId == selectedSubjectId.value);
    if (!group) return true; 
    const cls = group.classes.find(c => c.classId == classId);
    if (!cls) return true; 
    return cls.assigned >= cls.required;
};

// Helper for cell styling
const getCellClass = (slot, dayId, periodId, classId) => {
    let baseClass = "border p-1 text-center transition-colors relative h-[52px] print:h-[42px] align-top ";
    
    if (activeParkedSlot.value) {
        if (activeParkedSlot.value.classId === classId && !slot) {
            const slotsArray = Array.isArray(props.slots) ? props.slots : Object.values(props.slots || {});
            const isBusy = slotsArray.some(s => 
                s.day_of_week === dayId && 
                s.period_id === periodId && 
                s.teaching_assignment.teacher_id == activeParkedSlot.value.teacherId
            );
            if (isBusy) {
                baseClass += "bg-gray-200 cursor-not-allowed opacity-60 ";
            } else {
                baseClass += "bg-green-100 hover:bg-green-200 border-green-400 cursor-pointer animate-pulse ring-2 ring-inset ring-green-500 shadow-inner ";
            }
        } else {
            if (slot && slot.teaching_assignment.teacher_id == activeParkedSlot.value.teacherId) {
                baseClass += "opacity-70 pointer-events-none "; // active teacher's subject, keep color
            } else {
                baseClass += "opacity-30 grayscale pointer-events-none "; // other teacher's subject or empty
            }
        }
        return baseClass;
    }

    if (slot) {
        if (filterForm.teacher_id && slot.teaching_assignment.teacher_id != filterForm.teacher_id) {
            if (teacherBusySlots.value.has(`${dayId}-${periodId}`)) {
                baseClass += "opacity-50 grayscale cursor-not-allowed bg-gray-200 ";
            } else {
                baseClass += "opacity-50 grayscale cursor-pointer hover:bg-black/5 "; // other teacher's subject
            }
        } else {
            baseClass += "cursor-pointer hover:bg-black/5 ";
        }
    } else {
        if (filterForm.teacher_id) {
            if (teacherBusySlots.value.has(`${dayId}-${periodId}`)) {
                baseClass += "bg-gray-200 cursor-not-allowed opacity-60 ";
            } else if (isClassSubjectFulfilled(classId)) {
                baseClass += "bg-gray-100 cursor-not-allowed opacity-40 ";
            } else {
                baseClass += "bg-green-100 hover:bg-green-200 border-green-300 cursor-pointer ";
            }
        } else {
            baseClass += "cursor-pointer hover:bg-black/5 ";
        }
    }
    return baseClass;
};
</script>

<template>
    <Head title="រៀបចំកាលវិភាគ" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">រៀបចំកាលវិភាគ</h2>
                <button @click="clearAllTimetables" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    លុបកាលវិភាគទាំងអស់
                </button>
            </div>
        </template>

        <!-- Floating Toast Notification -->
        <Transition 
            enter-active-class="transition ease-out duration-300 transform" 
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
            enter-to-class="opacity-100 translate-y-0 sm:scale-100" 
            leave-active-class="transition ease-in duration-200 transform" 
            leave-from-class="opacity-100 translate-y-0 sm:scale-100" 
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <div v-if="toastMessage" class="fixed bottom-4 right-4 z-[9999]">
                <div :class="['px-4 py-3 rounded shadow-lg flex items-center gap-3 text-white max-w-sm', 
                            toastType === 'success' ? 'bg-gray-800' : 'bg-red-600']">
                    <svg v-if="toastType === 'success'" class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">{{ toastMessage }}</span>
                </div>
            </div>
        </Transition>

        <div :class="[
            'flex flex-col print:h-auto print:block',
            isFullScreen ? 'fixed inset-0 z-50 bg-gray-100 py-4 h-screen' : 'py-6 h-[calc(100vh-140px)]'
        ]">
            <div :class="[
                'mx-auto w-full flex flex-col h-full gap-4 print:h-auto print:block print:max-w-none print:px-0',
                isFullScreen ? 'px-4 max-w-full' : 'max-w-[95%] sm:px-6 lg:px-8'
            ]">
                <!-- Controls -->
                <div class="shrink-0 bg-white shadow-sm sm:rounded-lg p-2.5 border border-gray-100 flex flex-col gap-2 print:hidden">
                    <!-- Top Row: Filters + Full screen button -->
                    <div class="flex flex-wrap items-end gap-3 justify-between">
                        <div class="flex flex-wrap items-end gap-3 flex-grow">
                            <div class="w-full sm:w-[150px]">
                                <label class="block text-xs font-medium text-gray-700 mb-1">រើសភូមិសិក្សា</label>
                                <select v-model="filterForm.level_id" @change="onFilterChange" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">-- ទាំងអស់ --</option>
                                    <option value="lower">មធ្យមសិក្សាបឋមភូមិ</option>
                                    <option value="upper">មធ្យមសិក្សាទុតិយភូមិ</option>
                                </select>
                            </div>
                            <div class="w-full sm:w-[150px]">
                                <label class="block text-xs font-medium text-gray-700 mb-1">រើសវេនសិក្សា</label>
                                <select v-model="filterForm.shift_id" @change="onFilterChange" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option v-for="s in shifts" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                            <div class="w-full sm:w-[180px]">
                                <label class="block text-xs font-medium text-gray-700 mb-1">រើសមុខវិជ្ជា</label>
                                <div class="relative z-50">
                                    <SearchableSelect 
                                        v-model="subjectFilter" 
                                        :options="subjects" 
                                        valueKey="id" 
                                        labelKey="khmer_name" 
                                        placeholder="-- ទាំងអស់ --"
                                        @update:modelValue="filterForm.teacher_id=''; onFilterChange()"
                                    />
                                </div>
                            </div>
                            <div class="w-full sm:w-[250px]">
                                <label class="block text-xs font-medium text-gray-700 mb-1">ជ្រើសរើសគ្រូបង្រៀន (ចុចលើតារាង)</label>
                                <div class="flex gap-2 relative z-40">
                                    <SearchableSelect
                                        class="flex-grow"
                                        v-model="filterForm.teacher_id"
                                        :options="teacherOptions"
                                        placeholder="-- សូមជ្រើសរើសគ្រូ --"
                                        @update:modelValue="onFilterChange"
                                    />
                                    <button @click="isTeacherModalOpen = true" type="button" class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-md border border-gray-300 text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition shadow-sm" title="បញ្ជីគ្រូបង្រៀន">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Full screen & period toggle buttons -->
                        <div class="ml-auto flex items-center shrink-0 gap-2">
                            <button @click="printTimetable" type="button" class="inline-flex items-center px-2 py-1 border border-gray-300 text-[11px] leading-4 font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                បោះពុម្ព
                            </button>
                            <button @click="showAllPeriods = !showAllPeriods" type="button" :class="['inline-flex items-center px-2 py-1 border text-[11px] leading-4 font-medium rounded transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500', showAllPeriods ? 'border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50']">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ showAllPeriods ? 'លាក់ម៉ោងក្រៅវេន' : 'បង្ហាញម៉ោងក្រៅវេន' }}
                            </button>
                            <button @click="toggleFullScreen" type="button" class="inline-flex items-center px-2 py-1 border border-gray-300 text-[11px] leading-4 font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition shadow-sm">
                                <svg v-if="!isFullScreen" class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                                <svg v-else class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                {{ isFullScreen ? 'បង្រួមវិញ' : 'ពង្រីក' }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Bottom Row: Teacher stats directly inline, flex-grow -->
                    <div v-if="filterForm.teacher_id" class="flex-grow bg-gray-50 px-2.5 py-1.5 rounded border border-gray-200 overflow-x-auto flex items-center gap-3 whitespace-nowrap">
                        <div class="text-[11px] text-gray-700 font-bold shrink-0">ម៉ោងបង្រៀន:</div>

                        <div v-if="teacherStatsGrouped.groups.length > 0" class="flex items-center gap-3 py-0.5">
                            <div v-for="group in teacherStatsGrouped.groups" :key="group.subjectKey" 
                                 @click="selectedSubjectId = group.subjectId"
                                 :class="['flex items-center gap-1.5 p-1 rounded-md transition-all cursor-pointer border shadow-sm shrink-0', selectedSubjectId === group.subjectId ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-gray-300 bg-white hover:bg-gray-50 hover:border-gray-400']">
                                <div class="flex items-center px-1.5 py-0.5 rounded border shadow-sm" :style="`background-color: ${group.color}20; border-color: ${group.color}40;`">
                                    <span class="font-bold text-[10px]" :style="`color: ${group.color};`">
                                        {{ group.subjectName && group.subjectName !== group.subjectKey ? group.subjectName + ' - ' + group.subjectKey : group.subjectKey }}
                                    </span>
                                    <span class="text-[9px] ml-1 font-bold px-1 rounded" :style="`background-color: ${group.color}30; color: ${group.color};`">{{ group.totalAssigned }}/{{ group.totalRequired }}</span>
                                </div>
                                <div v-for="cls in group.classes" :key="cls.id" 
                                     :class="['text-[10px] px-1.5 py-0.5 rounded font-medium border', 
                                              cls.assigned === cls.required ? 'bg-green-100 text-green-800 border-green-200' : 
                                              (cls.assigned > cls.required ? 'bg-red-100 text-red-800 border-red-200' : 'bg-yellow-100 text-yellow-800 border-yellow-200')]">
                                    {{ cls.className }}: {{ cls.assigned }}/{{ cls.required }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-[11px] text-red-500 font-medium shrink-0">
                            គ្រូនេះមិនមានម៉ោងត្រូវបង្រៀនក្នុងវេននេះទេ។
                        </div>
                    </div>
                </div>

                <!-- Master Grid -->
                <div class="flex-grow bg-white shadow-sm sm:rounded-lg overflow-auto print:overflow-visible print:shadow-none print:border-none border border-gray-200 min-h-0">
                    <table class="min-w-full divide-y divide-gray-200 border-collapse table-fixed w-full text-xs print:w-full">
                        <thead class="bg-gray-50 sticky print:static top-0 z-10 shadow-sm border-b border-gray-200">
                            <tr>
                                <th class="px-2 py-2 print:py-0.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border w-12 sticky print:static left-0 z-20 bg-gray-50 shadow-[1px_0_0_0_#e5e7eb] print:shadow-none">
                                    <div class="flex flex-col items-center justify-center">
                                        <span>ថ្ងៃ</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="px-2 py-2 print:py-0.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border w-20 sticky print:static left-12 z-20 bg-gray-50 shadow-[1px_0_0_0_#e5e7eb] print:shadow-none">
                                    <div class="flex flex-col items-center justify-center">
                                        <span>ម៉ោង</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </th>
                                <th v-for="c in classes" :key="c.id" @click="openClassModal(c)" class="px-2 py-2 print:py-0.5 text-center border min-w-[80px] align-top cursor-pointer hover:bg-gray-100 transition-colors bg-gray-50">
                                    <div class="text-xs font-bold text-gray-800 print:text-[11px]">{{ c.class_code || c.name }}</div>
                                    <div v-if="props.classStats && props.classStats[c.id] && props.classStats[c.id].required > 0" 
                                         :class="['text-[10px] px-1.5 py-0.5 rounded inline-block font-medium mt-1 shadow-sm print:shadow-none', (props.classStats[c.id].assigned >= props.classStats[c.id].required ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-orange-100 text-orange-800 border border-orange-200')]"
                                         title="ម៉ោងដែលបានបញ្ចូល / ម៉ោងសរុប">
                                        {{ props.classStats[c.id].assigned }}/{{ props.classStats[c.id].required }}
                                    </div>
                                </th>
                                <th class="px-2 py-2 print:py-0.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border w-20 sticky print:static right-12 z-20 bg-gray-50 shadow-[-1px_0_0_0_#e5e7eb] print:shadow-none">
                                    <div class="flex flex-col items-center justify-center">
                                        <span>ម៉ោង</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="px-2 py-2 print:py-0.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border w-12 sticky print:static right-0 z-20 bg-gray-50 shadow-[-1px_0_0_0_#e5e7eb] print:shadow-none">
                                    <div class="flex flex-col items-center justify-center">
                                        <span>ថ្ងៃ</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template v-for="(day, dayIndex) in days" :key="day.id">
                                <tr v-for="(period, index) in filteredPeriods" :key="period.id" :class="[getDayColors(day.id).periodBg, 'hover:brightness-95 transition-all duration-200', (filteredPeriods.length > 4 && dayIndex === 2 && index === filteredPeriods.length - 1) ? 'print:break-after-page' : '']">
                                    <td v-if="index === 0" :rowspan="filteredPeriods.length" :class="['px-2 py-2 print:py-0 print:px-0 text-center font-bold border writing-vertical transform -rotate-180 sticky print:static left-0 z-10 print:text-[10px] print:leading-none print:tracking-tighter', getDayColors(day.id).dayBg, getDayColors(day.id).dayText]" style="writing-mode: vertical-rl;">
                                        {{ day.name }}
                                    </td>
                                    <td :class="['px-2 py-2 print:py-0 print:px-1 text-center border sticky print:static left-12 z-10 shadow-[1px_0_0_0_#e5e7eb] print:shadow-none', getDayColors(day.id).periodBg]">
                                        <div :class="['font-bold flex items-center justify-center gap-1 print:text-[11px]', getDayColors(day.id).periodText]">
                                            <span v-if="period.shift_id === 1" title="ព្រឹក">☀️</span>
                                            <span v-else-if="period.shift_id === 2" title="ល្ងាច">⛅</span>
                                            <span v-else-if="period.shift_id === 3" title="យប់">🌙</span>
                                            <span>{{ index + 1 }}</span>
                                        </div>
                                        <div :class="['text-[10px] print:text-[9px] leading-tight', getDayColors(day.id).timeText]">{{ parseInt(period.start_time.split(':')[0]) }}:{{ period.start_time.split(':')[1] }} - {{ parseInt(period.end_time.split(':')[0]) }}:{{ period.end_time.split(':')[1] }}</div>
                                    </td>
                                    <td v-for="c in classes" :key="c.id" 
                                        @click.self="toggleSlot(c.id, day.id, period.id)"
                                        @dragenter.prevent
                                        @dragover.prevent
                                        @drop.stop.prevent="onDropSlot($event, c.id, day.id, period.id)"
                                        :class="getCellClass(gridData[day.id][period.id][c.id], day.id, period.id, c.id)">
                                        
                                        <!-- Show busy icon for empty cell -->
                                        <div v-if="!gridData[day.id][period.id][c.id] && filterForm.teacher_id && teacherBusySlots.has(`${day.id}-${period.id}`)" 
                                             class="absolute inset-0 flex items-center justify-center text-gray-400 pointer-events-auto cursor-help"
                                             :title="`កំពុងបង្រៀន ${teacherBusySlots.get(`${day.id}-${period.id}`).subjectKey} នៅថ្នាក់ ${teacherBusySlots.get(`${day.id}-${period.id}`).className}`">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </div>

                                        <div v-if="gridData[day.id][period.id][c.id]" 
                                             draggable="true"
                                             @click="toggleSlot(c.id, day.id, period.id)"
                                             @dragstart.stop="onDragStart($event, gridData[day.id][period.id][c.id].id, c.id)"
                                             @dragend="onDragEnd"
                                             @dragenter.prevent
                                             @dragover.prevent
                                             @drop.stop.prevent="onDropSlot($event, c.id, day.id, period.id)"
                                             :class="['group w-full h-full flex flex-col items-center justify-center rounded-md shadow-sm border cursor-move pointer-events-auto relative print:border-gray-300 print:bg-white', filterForm.teacher_id && gridData[day.id][period.id][c.id].teaching_assignment.teacher_id == filterForm.teacher_id ? 'ring-2 ring-blue-500 border-blue-500' : 'border-transparent']"
                                             :style="`background-color: ${gridData[day.id][period.id][c.id].teaching_assignment.subject.color || '#9ca3af'};`">
                                             
                                            <button type="button" @click.stop="parkSlot(gridData[day.id][period.id][c.id].id)" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 bg-black/30 hover:bg-black/50 text-white rounded w-6 h-5 flex items-center justify-center transition-opacity shadow-sm z-10 pointer-events-auto cursor-pointer print:hidden" title="ដកទៅឃ្លាំងផ្អាក">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                            </button>

                                            <div class="font-black text-[15px] print:text-[12px] whitespace-nowrap leading-tight text-white">
                                                {{ props.teacherSubjectCodes[gridData[day.id][period.id][c.id].teaching_assignment.teacher_id + '_' + gridData[day.id][period.id][c.id].teaching_assignment.subject_id] || gridData[day.id][period.id][c.id].teaching_assignment.subject.short_name }}
                                            </div>
                                            <div class="text-[10px] print:text-[9px] text-white/90 whitespace-nowrap truncate w-full px-1 font-medium mt-0.5 print:mt-0 leading-tight">
                                                {{ gridData[day.id][period.id][c.id].teaching_assignment.teacher.khmer_name.split(' ').pop() }}
                                            </div>
                                        </div>
                                    </td>
                                    <td :class="['px-2 py-2 print:py-0 print:px-1 text-center border sticky print:static right-12 z-10 shadow-[-1px_0_0_0_#e5e7eb] print:shadow-none', getDayColors(day.id).periodBg]">
                                        <div :class="['font-bold flex items-center justify-center gap-1 print:text-[11px]', getDayColors(day.id).periodText]">
                                            <span v-if="period.shift_id === 1" title="ព្រឹក">☀️</span>
                                            <span v-else-if="period.shift_id === 2" title="ល្ងាច">⛅</span>
                                            <span v-else-if="period.shift_id === 3" title="យប់">🌙</span>
                                            <span>{{ index + 1 }}</span>
                                        </div>
                                        <div :class="['text-[10px] print:text-[9px] leading-tight', getDayColors(day.id).timeText]">{{ parseInt(period.start_time.split(':')[0]) }}:{{ period.start_time.split(':')[1] }} - {{ parseInt(period.end_time.split(':')[0]) }}:{{ period.end_time.split(':')[1] }}</div>
                                    </td>
                                    <td v-if="index === 0" :rowspan="filteredPeriods.length" :class="['px-2 py-2 text-center font-bold border writing-vertical transform -rotate-180 sticky right-0 z-10 shadow-[-1px_0_0_0_#e5e7eb] print:shadow-none', getDayColors(day.id).dayBg, getDayColors(day.id).dayText]" style="writing-mode: vertical-rl;">
                                        {{ day.name }}
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Holding Area Panel -->
                <div v-if="parkedSlots.length > 0" 
                     class="shrink-0 bg-white border-2 border-dashed border-gray-300 shadow-sm sm:rounded-lg p-1.5 z-[20]"
                     @dragenter.prevent
                     @dragover.prevent
                     @drop.prevent="onDropHolding($event)">
                    <div class="max-w-full mx-auto flex items-center gap-3">
                        <div class="shrink-0 flex items-center justify-center text-gray-400 border-r-2 border-dashed pr-3 min-w-[100px]">
                            <svg class="w-5 h-5 mr-1.5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span class="text-[10px] font-bold uppercase tracking-wider">ឃ្លាំងផ្អាក</span>
                        </div>
                        <div class="flex-1 overflow-x-auto flex gap-2 items-center min-h-[44px] custom-scrollbar pb-2 pt-1 px-1">
                            <div v-for="slot in parkedSlots" :key="slot.uid"
                                 draggable="true"
                                 @click="toggleParkedSlotSelection(slot)"
                                 @dragstart="onDragStart($event, slot.uid, slot.classId, true, slot.subjectId, slot.teacherId)"
                                 @dragend="onDragEnd"
                                 :class="['shrink-0 flex flex-col justify-center items-center gap-0.5 rounded-lg shadow-sm border cursor-pointer transition-all hover:scale-105 hover:shadow-md bg-white select-none px-2 py-1', activeParkedSlot && activeParkedSlot.uid === slot.uid ? 'ring-2 ring-green-500 scale-105 border-solid z-10' : 'border-dashed']"
                                 :style="`border-color: ${slot.color || '#9ca3af'};`">
                                 <!-- Colored Dot + Subject Name -->
                                 <div class="flex items-center gap-1.5">
                                     <div class="w-3.5 h-3.5 rounded-full flex items-center justify-center font-bold text-[8px] text-white" :style="`background-color: ${slot.color || '#9ca3af'};`">
                                         {{ slot.subjectKey.substring(0,1) }}
                                     </div>
                                     <div class="font-bold text-[10px]" :style="`color: ${slot.color || '#374151'};`">{{ slot.subjectName || slot.subjectKey }}</div>
                                 </div>
                                 <!-- Class & Teacher -->
                                 <div class="flex items-center gap-1 text-[9px]">
                                     <span class="font-bold text-gray-700 bg-gray-100 px-1 rounded">{{ slot.className }}</span>
                                     <span v-if="!filterForm.teacher_id" class="text-gray-500 font-medium">{{ slot.teacherName }}</span>
                                 </div>
                            </div>
                        </div>
                        <div v-if="activeParkedSlot" class="shrink-0 flex items-center bg-green-50 text-green-700 px-3 py-1.5 rounded border border-green-200 shadow-inner ml-auto text-sm font-bold animate-pulse">
                            ចុចលើប្រអប់ពណ៌បៃតងក្នុងតារាងដើម្បីបញ្ចូល
                        </div>
                    </div>
                </div>
                
                <div v-else-if="isDraggingSlot"
                     class="shrink-0 bg-gray-50 border border-gray-200 border-dashed sm:rounded-lg p-2 z-[20] text-center text-gray-400 text-sm font-medium transition-transform duration-300"
                     @dragenter.prevent
                     @dragover.prevent
                     @drop.prevent="onDropHolding($event)">
                     ទាញម៉ោងបង្រៀនទម្លាក់ទីនេះដើម្បី <span class="font-bold text-gray-600">"ផ្អាក"</span> បណ្តោះអាសន្ន
                </div>

            </div>
        </div>

        <!-- Smart Sidebar Clipboard (replaces Class Details Modal) -->
        <div :class="['fixed top-0 right-0 h-full w-96 bg-white shadow-[-10px_0_20px_-5px_rgba(0,0,0,0.1)] z-[70] transform transition-transform duration-300 ease-in-out flex flex-col', isClassModalOpen ? 'translate-x-0' : 'translate-x-full']">
            <!-- Sidebar Header -->
            <div class="h-16 flex-none flex justify-between items-center px-5 border-b bg-gray-50/90 backdrop-blur-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <h3 class="text-base font-bold text-gray-800">
                        ថ្នាក់ {{ selectedClassForModal?.class_code || selectedClassForModal?.name }}
                    </h3>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="isClassModalOpen = false" class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition-colors" title="បិទ">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="flex-1 overflow-y-auto p-3 bg-gray-50/50">
                <div v-if="selectedClassForModal && props.classAssignmentsDetail && props.classAssignmentsDetail[selectedClassForModal.id]" class="space-y-2">
                    
                    <div class="flex justify-end mb-2">
                        <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer hover:text-gray-700">
                            <input type="checkbox" v-model="showAllSidebarSubjects" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 h-3 w-3">
                            បង្ហាញមុខវិជ្ជាដែលគ្រប់ចំនួនហើយ
                        </label>
                    </div>

                    <template v-for="assignment in props.classAssignmentsDetail[selectedClassForModal.id]" :key="assignment.id">
                        <div v-if="showAllSidebarSubjects || assignment.assigned_hours < assignment.weekly_hours"
                             @click="selectSidebarSlot(assignment)"
                             :class="['p-2 rounded border flex flex-col gap-1 transition-all group', 
                                    assignment.assigned_hours >= assignment.weekly_hours ? 'bg-gray-50/50 border-gray-200 opacity-50' : 'bg-white border-gray-200 hover:border-blue-400 hover:shadow-sm cursor-pointer']">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 shrink-0 rounded flex items-center justify-center font-bold text-white text-[11px] shadow-sm" :style="`background-color: ${assignment.subject_color || '#4F46E5'};`">
                                        {{ assignment.subject_code }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-[13px] leading-tight">{{ assignment.subject_name }}</div>
                                        <div class="text-[10px] text-gray-500 flex items-center gap-1 group-hover:text-blue-600 transition-colors" @click.stop="selectTeacherFromModal(assignment.teacher_id)" title="ចុចដើម្បីមើលតែគ្រូនេះ">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            {{ assignment.teacher_name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div :class="['text-xs font-black', assignment.assigned_hours >= assignment.weekly_hours ? 'text-gray-400' : 'text-blue-600']">
                                        {{ assignment.assigned_hours }}/{{ assignment.weekly_hours }} ម៉ោង
                                    </div>
                                </div>
                            </div>
                            <div v-if="assignment.assigned_hours < assignment.weekly_hours" class="mt-1 flex items-center justify-between border-t border-dashed pt-1">
                                <div class="text-[10px] text-orange-600 font-medium">
                                    នៅខ្វះ {{ assignment.weekly_hours - assignment.assigned_hours }} ម៉ោង
                                </div>
                                <div class="text-[9px] font-bold text-blue-500 bg-blue-50 px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                    ចុចដើម្បីបញ្ចូល
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div v-else class="text-center py-10 flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <span>មិនទាន់មានការបែងចែក<br>គ្រូបង្រៀនសម្រាប់ថ្នាក់នេះទេ</span>
                </div>
            </div>
        </div>
        
        <!-- Smart Sidebar for Teacher Workloads -->
        <div :class="['fixed top-0 right-0 h-full w-[400px] bg-white shadow-[-10px_0_20px_-5px_rgba(0,0,0,0.1)] z-[70] transform transition-transform duration-300 ease-in-out flex flex-col', isTeacherModalOpen ? 'translate-x-0' : 'translate-x-full']">
            <!-- Sidebar Header -->
            <div class="h-16 flex-none flex justify-between items-center px-5 border-b bg-gray-50/90 backdrop-blur-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <h3 class="text-base font-bold text-gray-800">
                        បញ្ជីម៉ោងគ្រូបង្រៀន
                    </h3>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="isTeacherModalOpen = false" class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition-colors" title="បិទ">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="flex-1 overflow-y-auto p-3 bg-gray-50/50">
                <div v-if="props.teacherWorkloads && props.teacherWorkloads.length > 0" class="space-y-2">
                    
                    <div class="flex justify-between items-center mb-2 px-1">
                        <div class="text-[11px] text-gray-500 font-medium">
                            គ្រូបង្រៀនសរុប៖ {{ props.teacherWorkloads.length }} នាក់
                        </div>
                        <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer hover:text-gray-700">
                            <input type="checkbox" v-model="showAllSidebarTeachers" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 h-3 w-3">
                            បង្ហាញអ្នករៀបចំរួចរាល់
                        </label>
                    </div>

                    <template v-for="t in props.teacherWorkloads" :key="t.id">
                        <div v-if="showAllSidebarTeachers || t.assigned < t.required"
                             @click="selectTeacherFromModal(t.id)"
                             :class="['p-2.5 rounded border flex flex-col gap-2 transition-all group', 
                                    t.assigned >= t.required ? 'bg-gray-50/50 border-gray-200 opacity-60' : 'bg-white border-gray-200 hover:border-blue-400 hover:shadow-sm cursor-pointer']">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div v-if="t.photo" class="w-9 h-9 shrink-0 rounded-full overflow-hidden border-2 shadow-sm" :class="t.assigned >= t.required ? 'border-green-500' : 'border-indigo-500'">
                                        <img :src="'/storage/' + t.photo" alt="" class="w-full h-full object-cover">
                                    </div>
                                    <div v-else :class="['w-9 h-9 shrink-0 rounded-full flex items-center justify-center font-bold text-white text-[11px] shadow-sm', t.assigned >= t.required ? 'bg-green-500' : 'bg-indigo-500']">
                                        {{ t.code || 'T' }}
                                    </div>
                                    <div class="flex flex-col justify-center">
                                        <div class="font-bold text-gray-900 text-[14px] leading-tight flex items-center gap-1.5 flex-wrap">
                                            {{ t.name }}
                                            <span v-if="t.subjects && t.subjects.length > 0" class="text-gray-300 font-normal">|</span>
                                            <div v-if="t.subjects && t.subjects.length > 0" class="flex flex-wrap gap-1">
                                                <span v-for="(subject, idx) in t.subjects" :key="idx" class="px-1.5 py-[1px] text-[10px] rounded font-bold border shadow-sm" :style="`color: ${subject.color}; background-color: ${subject.color}15; border-color: ${subject.color}30;`">
                                                    {{ subject.name }}
                                                </span>
                                            </div>
                                            <svg v-if="t.assigned >= t.required" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div class="text-[11px] text-gray-500 flex items-start gap-1 mt-0.5">
                                            <svg class="w-3 h-3 shrink-0 mt-[2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            <span class="leading-tight max-w-[200px] break-words">{{ formatPhone(t.phone) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div :class="['text-sm font-black', t.assigned >= t.required ? 'text-green-600' : 'text-blue-600']">
                                        {{ t.assigned }}/{{ t.required }} <span class="text-[10px] font-normal text-gray-500">ម៉ោង</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Class details per subject -->
                            <div class="mt-1 mb-1 text-[11px] text-gray-600 bg-gray-50/50 p-2 rounded border border-gray-100 flex flex-col gap-1.5" v-if="t.subjects && t.subjects.length > 0">
                                <div v-for="(subj, sIdx) in t.subjects" :key="'det-'+sIdx" class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full" :style="`background-color: ${subj.color}`"></span>
                                        <span class="font-bold" :style="{ color: subj.color }">{{ subj.name }}</span>
                                        <span class="text-gray-400">|</span>
                                        <span class="text-gray-500 font-medium">សរុប៖ {{ subj.classes.reduce((sum, c) => sum + (typeof c === 'string' ? 0 : c.hours), 0) }} ម៉ោង</span>
                                    </div>
                                    <div class="flex flex-wrap gap-x-1.5 gap-y-1 pl-2.5">
                                        <span v-for="(cls, cIdx) in subj.classes" :key="cIdx" class="bg-white px-1.5 py-0.5 rounded shadow-sm border border-gray-200 flex items-center gap-1 hover:border-blue-300 transition-colors">
                                            <span class="font-bold text-gray-700">{{ typeof cls === 'string' ? cls : cls.name }}</span>
                                            <span class="text-[9px] text-gray-500 bg-gray-100 px-1 rounded-sm">{{ typeof cls === 'string' ? '?' : cls.hours }}ម៉</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                <div :class="['h-1.5 rounded-full transition-all duration-500', t.assigned >= t.required ? 'bg-green-500' : 'bg-blue-500']" :style="{ width: Math.min(100, Math.round((t.assigned / (t.required || 1)) * 100)) + '%' }"></div>
                            </div>
                            

                            
                            <div v-if="t.assigned < t.required" class="mt-1 flex items-center justify-between border-t border-dashed border-gray-200 pt-1.5">
                                <div class="text-[11px] text-orange-600 font-medium">
                                    នៅខ្វះ {{ t.required - t.assigned }} ម៉ោង
                                </div>
                                <div class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                                    រៀបចំកាលវិភាគ
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div v-else class="text-center py-10 flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>មិនមានទិន្នន័យគ្រូបង្រៀនទេ</span>
                </div>
            </div>
        </div>

        <!-- Overlay for Sidebars -->
        <div v-if="isClassModalOpen || isTeacherModalOpen" 
             @click="isClassModalOpen = false; isTeacherModalOpen = false"
             class="fixed inset-0 bg-gray-900/20 backdrop-blur-sm z-[65] transition-opacity duration-300">
        </div>

    </AuthenticatedLayout>
</template>

<style>
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    /* Hide the application navigation and header during print */
    nav, header {
        display: none !important;
    }
    
    /* Remove padding/margins from body/main wrappers for cleaner print */
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    /* Ensure the page breaks properly */
    table { page-break-inside:auto; }
    tr    { page-break-inside:avoid; page-break-after:auto; }
    thead { display:table-header-group; }
    tfoot { display:table-footer-group; }
    
    @page {
        size: portrait;
        margin: 5mm;
    }
    
    /* Optimize scaling for print */
    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    /* Adjust table headers/cells to fill the Portrait page beautifully */
    th, td {
        padding: 0px 1px !important;
        height: 41px !important;
    }
    
    /* Force smaller font sizes to fit 14 columns in Portrait width */
    .print\:text-\[11px\] { font-size: 10px !important; line-height: 1 !important; }
    .print\:text-\[10px\] { font-size: 9px !important; line-height: 1 !important; }
    .print\:text-\[9px\] { font-size: 8px !important; line-height: 1 !important; }
    .print\:leading-tight { line-height: 1.1 !important; }
}
</style>

<style scoped>
/* Add a custom top border for assigned cells to show subject color */
td[style*="border-top-color"] {
    border-top-width: 3px !important;
}
</style>
