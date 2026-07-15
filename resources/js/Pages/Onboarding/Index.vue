<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    education_level: 'អនុវិទ្យាល័យ',
    principal_name: '',
    phone: '',
    address: '',
});

const submit = () => {
    form.post(route('onboarding.store'));
};
</script>

<template>
    <Head title="រៀបចំសាលារៀនរបស់អ្នក" />

    <div class="min-h-screen bg-gray-100 flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">សូមស្វាគមន៍!</h2>
                <p class="text-sm text-gray-600 mt-2">សូមបំពេញព័ត៌មានសាលារៀនរបស់អ្នក ដើម្បីចាប់ផ្ដើមប្រើប្រាស់ប្រព័ន្ធ។</p>
            </div>
            
            <div class="flex justify-end mb-4">
                <Link :href="route('logout')" method="post" as="button" class="text-sm text-red-600 hover:text-red-900 underline">
                    ចាកចេញ (Log Out)
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="name" value="ឈ្មោះសាលា (School Name)" />
                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="education_level" value="ភូមិសិក្សា (Education Level)" />
                    <select id="education_level" v-model="form.education_level" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="អនុវិទ្យាល័យ">អនុវិទ្យាល័យ</option>
                        <option value="វិទ្យាល័យ">វិទ្យាល័យ</option>
                        <option value="អនុវិទ្យាល័យ និងវិទ្យាល័យ">អនុវិទ្យាល័យ និងវិទ្យាល័យ</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.education_level" />
                </div>

                <div>
                    <InputLabel for="principal_name" value="ឈ្មោះនាយក (Principal Name) - ស្រេចចិត្ត" />
                    <TextInput id="principal_name" type="text" class="mt-1 block w-full" v-model="form.principal_name" />
                    <InputError class="mt-2" :message="form.errors.principal_name" />
                </div>

                <div>
                    <InputLabel for="phone" value="លេខទូរសព្ទ (Phone) - ស្រេចចិត្ត" />
                    <TextInput id="phone" type="text" class="mt-1 block w-full" v-model="form.phone" />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <div>
                    <InputLabel for="address" value="អាសយដ្ឋាន (Address) - ស្រេចចិត្ត" />
                    <TextInput id="address" type="text" class="mt-1 block w-full" v-model="form.address" />
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>

                <div v-if="form.errors.error" class="text-red-600 text-sm mt-2">
                    {{ form.errors.error }}
                </div>

                <div class="flex items-center justify-end mt-6">
                    <PrimaryButton :disabled="form.processing" class="w-full justify-center text-lg py-3">
                        <span v-if="form.processing">កំពុងរៀបចំ...</span>
                        <span v-else>ចាប់ផ្ដើមប្រើប្រាស់</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
