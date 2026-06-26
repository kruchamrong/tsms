$controllerTemplate = @"
<?php

namespace App\Http\Controllers;

use App\Models\SubstituteAssignment;
use App\Models\TimetableSlot;
use App\Models\TeacherLeave;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SubstituteAssignmentController extends Controller
{
    public function index(Request `$request)
    {
        `$dateStr = `$request->query('date', now()->format('Y-m-d'));
        `$date = Carbon::parse(`$dateStr);
        `$dayOfWeek = `$date->dayOfWeekIso; // 1 = Mon, 7 = Sun

        // Find teachers on leave today
        `$teachersOnLeave = TeacherLeave::where('date_from', '<=', `$dateStr)
            ->where('date_to', '>=', `$dateStr)
            ->pluck('teacher_id');

        // Find timetable slots for those teachers today
        `$missingSlots = [];
        if (`$teachersOnLeave->isNotEmpty() && `$dayOfWeek <= 6) {
            `$missingSlots = TimetableSlot::with(['teachingAssignment.teacher', 'teachingAssignment.subject', 'teachingAssignment.schoolClass', 'period', 'room'])
                ->where('day_of_week', `$dayOfWeek)
                ->whereHas('teachingAssignment', function (`$q) use (`$teachersOnLeave) {
                    `$q->whereIn('teacher_id', `$teachersOnLeave);
                })
                ->get()
                ->map(function (`$slot) use (`$dateStr) {
                    // Check if substitute already assigned
                    `$sub = SubstituteAssignment::with('substituteTeacher')
                        ->where('timetable_slot_id', `$slot->id)
                        ->where('date', `$dateStr)
                        ->first();
                    
                    `$slot->substitute = `$sub;
                    return `$slot;
                });
        }

        return Inertia::render('SubstituteAssignment/Index', [
            'selectedDate' => `$dateStr,
            'missingSlots' => `$missingSlots,
            'teachers' => Teacher::all(),
        ]);
    }

    public function store(Request `$request)
    {
        `$validated = `$request->validate([
            'timetable_slot_id' => 'required|exists:timetable_slots,id',
            'substitute_teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
        ]);

        // Check if exists
        `$existing = SubstituteAssignment::where('timetable_slot_id', `$validated['timetable_slot_id'])
            ->where('date', `$validated['date'])
            ->first();

        if (`$existing) {
            `$existing->update(['substitute_teacher_id' => `$validated['substitute_teacher_id']]);
        } else {
            `$validated['id'] = (string) Str::uuid();
            SubstituteAssignment::create(`$validated);
        }

        return redirect()->back()->with('success', 'Substitute assigned successfully.');
    }
}
"@

Set-Content "app\Http\Controllers\SubstituteAssignmentController.php" $controllerTemplate

$vueTemplate = @"
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    selectedDate: String,
    missingSlots: Array,
    teachers: Array,
});

const filterForm = useForm({
    date: props.selectedDate
});

const fetchMissing = () => {
    filterForm.get(route('substitute-assignments.index'));
};

const assignForm = useForm({
    timetable_slot_id: '',
    substitute_teacher_id: '',
    date: props.selectedDate,
});

const assignSub = (slotId, teacherId) => {
    assignForm.timetable_slot_id = slotId;
    assignForm.substitute_teacher_id = teacherId;
    assignForm.post(route('substitute-assignments.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Substitute Teachers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Substitute Teachers (គ្រប់គ្រងគ្រូជំនួស)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-end space-x-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Select Date (ជ្រើសរើសកាលបរិច្ឆេទ)</label>
                            <input v-model="filterForm.date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <button @click="fetchMissing" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">View (មើល)</button>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4 text-red-600">Classes Needing Substitutes (ម៉ោងដែលខ្វះគ្រូបង្រៀន)</h3>
                    <div v-if="missingSlots && missingSlots.length > 0">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border">Original Teacher</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border">Time & Room</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border">Subject & Class</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border">Substitute Teacher</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="slot in missingSlots" :key="slot.id">
                                    <td class="px-4 py-4 border text-red-600 font-bold">
                                        {{ slot.teaching_assignment.teacher.khmer_name }}
                                        <div class="text-xs text-gray-500 font-normal">On Leave</div>
                                    </td>
                                    <td class="px-4 py-4 border">
                                        {{ slot.period.start_time.substring(0,5) }} - {{ slot.period.end_time.substring(0,5) }}
                                        <div class="text-xs text-gray-500">Room: {{ slot.room.name }}</div>
                                    </td>
                                    <td class="px-4 py-4 border">
                                        {{ slot.teaching_assignment.subject.name }}
                                        <div class="text-xs text-gray-500">Class: {{ slot.teaching_assignment.school_class.name }}</div>
                                    </td>
                                    <td class="px-4 py-4 border">
                                        <select :id="'sub_' + slot.id" class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                            <option value="">-- រើសគ្រូជំនួស --</option>
                                            <option v-for="t in teachers" :key="t.id" :value="t.id" :selected="slot.substitute?.substitute_teacher_id === t.id">{{ t.khmer_name }}</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-4 border text-center">
                                        <button @click="assignSub(slot.id, document.getElementById('sub_' + slot.id).value)" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                            Assign
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-green-600 text-center py-4">
                        All classes are fully covered today! (គ្មានម៉ោងដែលខ្វះគ្រូទេថ្ងៃនេះ)
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
"@

New-Item -ItemType Directory -Force -Path "resources\js\Pages\SubstituteAssignment"
Set-Content "resources\js\Pages\SubstituteAssignment\Index.vue" $vueTemplate
