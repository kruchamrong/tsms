<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    subject: Object,
});

const form = useForm({
    subject_code: props.subject.subject_code,
    khmer_name: props.subject.khmer_name,
    english_name: props.subject.english_name,
    short_name: props.subject.short_name,
});

const submit = () => {
    form.put(route('subjects.update', props.subject.id));
};
</script>

<template>
    <Head title="កែប្រែមុខវិជ្ជា" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">កែប្រែមុខវិជ្ជា</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="max-w-xl">
                            <div class="mb-4">
                                <InputLabel for="subject_code" value="លេខកូដមុខវិជ្ជា" />
                                <TextInput id="subject_code" type="text" class="mt-1 block w-full" v-model="form.subject_code" required />
                                <InputError class="mt-2" :message="form.errors.subject_code" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="khmer_name" value="ឈ្មោះជាភាសាខ្មែរ" />
                                <TextInput id="khmer_name" type="text" class="mt-1 block w-full" v-model="form.khmer_name" required />
                                <InputError class="mt-2" :message="form.errors.khmer_name" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="english_name" value="ឈ្មោះជាភាសាអង់គ្លេស" />
                                <TextInput id="english_name" type="text" class="mt-1 block w-full" v-model="form.english_name" />
                                <InputError class="mt-2" :message="form.errors.english_name" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="short_name" value="អក្សរកាត់" />
                                <TextInput id="short_name" type="text" class="mt-1 block w-full" v-model="form.short_name" />
                                <InputError class="mt-2" :message="form.errors.short_name" />
                            </div>

                            <div class="flex items-center gap-4 mt-6">
                                <PrimaryButton :disabled="form.processing">រក្សាទុកការកែប្រែ</PrimaryButton>
                                <Link :href="route('subjects.index')" class="text-gray-600 hover:text-gray-900">បោះបង់</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
