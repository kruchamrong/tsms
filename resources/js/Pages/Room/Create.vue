<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
const props = defineProps({
    roomTypes: Array,
});

const form = useForm({
    room_name: '',
    building: '',
    capacity: 40,
    room_type: 'បន្ទប់រៀនធម្មតា',
});

const submit = () => {
    form.post(route('rooms.store'));
};
</script>

<template>
    <Head title="បន្ថែមបន្ទប់រៀនថ្មី" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">បន្ថែមបន្ទប់រៀនថ្មី</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="max-w-xl">
                            <div class="mb-4">
                                <InputLabel for="room_name" value="ឈ្មោះបន្ទប់ ឬលេខបន្ទប់" />
                                <TextInput id="room_name" type="text" class="mt-1 block w-full" v-model="form.room_name" required autofocus placeholder="ឧ. R-11A1 ឬ 101" />
                                <InputError class="mt-2" :message="form.errors.room_name" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="building" value="អគារ (បើមាន)" />
                                <TextInput id="building" type="text" class="mt-1 block w-full" v-model="form.building" placeholder="ឧ. អគារ A" />
                                <InputError class="mt-2" :message="form.errors.building" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="capacity" value="ចំណុះសិស្ស" />
                                <TextInput id="capacity" type="number" min="1" class="mt-1 block w-full" v-model="form.capacity" required />
                                <InputError class="mt-2" :message="form.errors.capacity" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="room_type" value="ប្រភេទបន្ទប់" />
                                <TextInput id="room_type" type="text" list="roomTypesList" class="mt-1 block w-full" v-model="form.room_type" required />
                                <datalist id="roomTypesList">
                                    <option value="បន្ទប់រៀនធម្មតា">បន្ទប់រៀនធម្មតា</option>
                                    <option value="បន្ទប់ពិសោធន៍">បន្ទប់ពិសោធន៍</option>
                                    <option value="បន្ទប់កុំព្យូទ័រ">បន្ទប់កុំព្យូទ័រ</option>
                                    <option value="បណ្ណាល័យ">បណ្ណាល័យ</option>
                                    <option value="ទីលានកីឡា">ទីលានកីឡា</option>
                                    <option value="បន្ទប់សិល្បៈ">បន្ទប់សិល្បៈ</option>
                                    <option value="ផ្សេងៗ">ផ្សេងៗ</option>
                                    <option v-for="rt in roomTypes" :key="rt" :value="rt">{{ rt }}</option>
                                </datalist>
                                <InputError class="mt-2" :message="form.errors.room_type" />
                            </div>

                            <div class="flex items-center gap-4 mt-6">
                                <PrimaryButton :disabled="form.processing">រក្សាទុក</PrimaryButton>
                                <Link :href="route('rooms.index')" class="text-gray-600 hover:text-gray-900">បោះបង់</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
