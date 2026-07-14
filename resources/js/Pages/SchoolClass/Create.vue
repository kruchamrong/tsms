<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

defineProps({
    grades: Array,
    curricula: Array,
    rooms: Array,
    shifts: Array,
    teachers: Array,
});

const form = useForm({
    class_code: '',
    grade_id: '',
    curriculum_id: '',
    homeroom_teacher_id: null,
    room_id: null,
    shift_id: null,
    student_count: 0,
});

const submit = () => {
    form.post(route('classes.store'));
};
</script>

<template>
    <Head title="បន្ថែមថ្នាក់រៀនថ្មី" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">បន្ថែមថ្នាក់រៀនថ្មី</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="max-w-xl">
                            <div class="mb-4">
                                <InputLabel for="class_code" value="ឈ្មោះថ្នាក់ ឬលេខកូដ" />
                                <TextInput id="class_code" type="text" class="mt-1 block w-full" v-model="form.class_code" required autofocus placeholder="ឧ. ១១ក" />
                                <InputError class="mt-2" :message="form.errors.class_code" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="grade_id" value="កម្រិតថ្នាក់" />
                                <select id="grade_id" v-model="form.grade_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                                    <option value="" disabled>-- ជ្រើសរើសកម្រិតថ្នាក់ --</option>
                                    <option v-for="grade in grades" :key="grade.id" :value="grade.id">
                                        {{ grade.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.grade_id" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="homeroom_teacher_id" value="គ្រូបន្ទុកថ្នាក់" />
                                <SearchableSelect 
                                    v-model="form.homeroom_teacher_id" 
                                    :options="teachers" 
                                    valueKey="id" 
                                    labelKey="khmer_name" 
                                    placeholder="-- មិនទាន់កំណត់ --"
                                />
                                <InputError class="mt-2" :message="form.errors.homeroom_teacher_id" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="curriculum_id" value="កម្មវិធីសិក្សា" />
                                <select id="curriculum_id" v-model="form.curriculum_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="">-- គ្មាន --</option>
                                    <option v-for="curriculum in curricula" :key="curriculum.id" :value="curriculum.id">
                                        {{ curriculum.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.curriculum_id" />
                            </div>



                            <div class="mb-4">
                                <InputLabel for="shift_id" value="វេនគោល" />
                                <select id="shift_id" v-model="form.shift_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <option :value="null">-- មិនទាន់កំណត់ --</option>
                                    <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                        {{ shift.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.shift_id" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="student_count" value="ចំនួនសិស្សសរុប" />
                                <TextInput id="student_count" type="number" min="0" class="mt-1 block w-full" v-model="form.student_count" required />
                                <InputError class="mt-2" :message="form.errors.student_count" />
                            </div>

                            <div class="flex items-center gap-4 mt-6">
                                <PrimaryButton :disabled="form.processing">រក្សាទុក</PrimaryButton>
                                <Link :href="route('classes.index')" class="text-gray-600 hover:text-gray-900">បោះបង់</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
