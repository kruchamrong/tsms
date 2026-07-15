<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post(route('curricula.store'));
};
</script>

<template>
    <Head title="បន្ថែមកម្មវិធីសិក្សាថ្មី" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">បន្ថែមកម្មវិធីសិក្សាថ្មី</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="max-w-xl">
                            <div class="mb-4">
                                <InputLabel for="name" value="ឈ្មោះកម្មវិធីសិក្សា *" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus placeholder="ឧ. ថ្នាក់ទី១១ វិទ្យាសាស្ត្រ" />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="description" value="ការពិពណ៌នា" />
                                <TextInput id="description" type="text" class="mt-1 block w-full" v-model="form.description" />
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div class="flex items-center gap-4 mt-6">
                                <PrimaryButton :disabled="form.processing">រក្សាទុក</PrimaryButton>
                                <Link :href="route('curricula.index')" class="text-gray-600 hover:text-gray-900">បោះបង់</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
