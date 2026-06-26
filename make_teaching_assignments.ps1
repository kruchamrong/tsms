$controllerTemplate = @"
<?php

namespace App\Http\Controllers;

use App\Models\TeachingAssignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeachingAssignmentController extends Controller
{
    public function index(Request `$request)
    {
        `$teacherId = `$request->query('teacher_id');
        `$assignments = TeachingAssignment::with(['teacher', 'subject', 'schoolClass.grade', 'shift'])
            ->when(`$teacherId, function (`$query, `$teacherId) {
                return `$query->where('teacher_id', `$teacherId);
            })
            ->get();
            
        return Inertia::render('TeachingAssignment/Index', [
            'assignments' => `$assignments,
            'teachers' => Teacher::all(),
            'subjects' => Subject::all(),
            'classes' => SchoolClass::with('grade')->get(),
            'shifts' => Shift::all(),
            'selectedTeacherId' => `$teacherId,
        ]);
    }

    public function store(Request `$request)
    {
        `$validated = `$request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'shift_id' => 'required|exists:shifts,id',
            'weekly_hours' => 'required|integer|min:1',
        ]);

        TeachingAssignment::create(`$validated);

        return redirect()->back()->with('success', 'Assignment created successfully.');
    }

    public function destroy(TeachingAssignment `$teachingAssignment)
    {
        `$teachingAssignment->delete();
        return redirect()->back()->with('success', 'Assignment deleted successfully.');
    }
}
"@

Set-Content "app\Http\Controllers\TeachingAssignmentController.php" $controllerTemplate

$vueTemplate = @"
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    assignments: Array,
    teachers: Array,
    subjects: Array,
    classes: Array,
    shifts: Array,
    selectedTeacherId: String,
});

const form = useForm({
    teacher_id: props.selectedTeacherId || '',
    subject_id: '',
    school_class_id: '',
    shift_id: '',
    weekly_hours: 2,
});

const submit = () => {
    form.post(route('teaching-assignments.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('subject_id', 'school_class_id', 'shift_id', 'weekly_hours'),
    });
};

const deleteAssignment = (id) => {
    if(confirm('Are you sure you want to delete this assignment?')) {
        useForm().delete(route('teaching-assignments.destroy', id), {
            preserveScroll: true,
        });
    }
};

const filterByTeacher = (event) => {
    window.location.href = route('teaching-assignments.index', { teacher_id: event.target.value });
};
</script>

<template>
    <Head title="Teaching Assignments" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Teaching Assignments (ម៉ោងបង្រៀន)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
                <!-- Add Assignment Form -->
                <div class="w-full md:w-1/3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-4">Assign Teacher</h3>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Teacher</label>
                                <select v-model="form.teacher_id" @change="filterByTeacher" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select Teacher</option>
                                    <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                        {{ teacher.khmer_name }} ({{ teacher.english_name }})
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Subject</label>
                                <select v-model="form.subject_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select Subject</option>
                                    <option v-for="sub in subjects" :key="sub.id" :value="sub.id">
                                        {{ sub.khmer_name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Class</label>
                                <select v-model="form.school_class_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select Class</option>
                                    <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                        {{ cls.class_code }} ({{ cls.grade?.name }})
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Shift</label>
                                <select v-model="form.shift_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select Shift</option>
                                    <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                        {{ shift.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Weekly Hours</label>
                                <input type="number" v-model="form.weekly_hours" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>

                            <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Save Assignment
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Assignment List -->
                <div class="w-full md:w-2/3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-4">Current Assignments</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="assignment in assignments" :key="assignment.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ assignment.teacher?.khmer_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ assignment.subject?.khmer_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ assignment.school_class?.class_code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ assignment.shift?.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap font-bold">{{ assignment.weekly_hours }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button @click="deleteAssignment(assignment.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="assignments.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No assignments found for this teacher.</td>
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
"@

New-Item -ItemType Directory -Force -Path "resources\js\Pages\TeachingAssignment"
Set-Content "resources\js\Pages\TeachingAssignment\Index.vue" $vueTemplate
