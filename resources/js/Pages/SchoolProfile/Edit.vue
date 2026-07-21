<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    school: {
        type: Object,
        required: true,
    },
    provinces: {
        type: Array,
        required: true,
    }
});

const form = useForm({
    name: props.school.name,
    province: props.school.province,
});

const submit = () => {
    form.patch(route('school-profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="ព័ត៌មានទូទៅសាលា" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">ព័ត៌មានទូទៅសាលា</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">ព័ត៌មានទូទៅសាលា</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                កែប្រែឈ្មោះសាលា និងរាជធានី/ខេត្ត សម្រាប់បង្ហាញលើរបាយការណ៍។
                            </p>
                        </header>

                        <form @submit.prevent="submit" class="mt-6 space-y-6">
                            <div>
                                <InputLabel for="name" value="ឈ្មោះសាលា" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="province" value="រាជធានី/ខេត្ត" />
                                <select
                                    id="province"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                    v-model="form.province"
                                >
                                    <option value="" disabled>ជ្រើសរើសរាជធានី/ខេត្ត</option>
                                    <option v-for="province in provinces" :key="province" :value="province">
                                        {{ province }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.province" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">រក្សាទុក</PrimaryButton>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">បានរក្សាទុកដោយជោគជ័យ។</p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
