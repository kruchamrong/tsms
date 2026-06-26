$controllerTemplate = @"
<?php

namespace App\Http\Controllers;

use App\Models\TeacherLeave;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TeacherLeaveController extends Controller
{
    public function index()
    {
        return Inertia::render('TeacherLeave/Index', [
            'leaves' => TeacherLeave::with('teacher')->orderBy('date_from', 'desc')->get(),
            'teachers' => Teacher::all(),
        ]);
    }

    public function store(Request `$request)
    {
        `$validated = `$request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'reason' => 'nullable|string|max:255',
        ]);

        `$validated['id'] = (string) Str::uuid();
        `$validated['status'] = 'Approved';

        TeacherLeave::create(`$validated);

        return redirect()->back()->with('success', 'Leave recorded successfully.');
    }

    public function destroy(TeacherLeave `$teacherLeave)
    {
        `$teacherLeave->delete();
        return redirect()->back()->with('success', 'Leave record deleted.');
    }
}
"@

Set-Content "app\Http\Controllers\TeacherLeaveController.php" $controllerTemplate

$vueTemplate = @"
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    leaves: Array,
    teachers: Array,
});

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
    <Head title="Teacher Leaves" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Teacher Leaves (ការសុំច្បាប់ឈប់សម្រាក)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Add Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Record Leave (កត់ត្រាការសុំច្បាប់)</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Teacher (គ្រូ)</label>
                                <select v-model="form.teacher_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">-- ជ្រើសរើស --</option>
                                    <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.khmer_name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Reason (មូលហេតុ)</label>
                                <input v-model="form.reason" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">From Date (ចាប់ពីថ្ងៃ)</label>
                                <input v-model="form.date_from" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">To Date (ដល់ថ្ងៃ)</label>
                                <input v-model="form.date_to" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                        </div>
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save (រក្សាទុក)</button>
                    </form>
                </div>

                <!-- List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="leave in leaves" :key="leave.id">
                                <td class="px-6 py-4">{{ leave.teacher.khmer_name }}</td>
                                <td class="px-6 py-4">{{ leave.date_from }} ដល់ {{ leave.date_to }}</td>
                                <td class="px-6 py-4">{{ leave.reason }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ leave.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <button @click="deleteLeave(leave.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
"@

New-Item -ItemType Directory -Force -Path "resources\js\Pages\TeacherLeave"
Set-Content "resources\js\Pages\TeacherLeave\Index.vue" $vueTemplate
