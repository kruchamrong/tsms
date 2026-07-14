<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('admin.users.index'), { search: value }, { preserveState: true, replace: true });
});

// Create Form
const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
});

const submit = () => {
    form.post(route('admin.users.store'), {
        onSuccess: () => form.reset(),
        onError: () => form.reset('password'),
    });
};

// Edit Form
const editingUser = ref(null);
const showEditModal = ref(false);

const editForm = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
});

const openEditModal = (user) => {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.phone = user.phone;
    editForm.password = '';
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.reset();
    editingUser.value = null;
};

const submitEdit = () => {
    editForm.put(route('admin.users.update', editingUser.value.id), {
        onSuccess: () => closeEditModal(),
    });
};

const deleteUser = (id) => {
    if (confirm('តើអ្នកពិតជាចង់លុបគណនីនេះមែនទេ?')) {
        router.delete(route('admin.users.destroy', id));
    }
};

const generatePassword = (formInstance) => {
    const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "Tsms@";
    for (let i = 0; i < 6; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    formInstance.password = password;
};
</script>

<template>
    <Head title="គ្រប់គ្រងគណនីសាលា" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">គ្រប់គ្រងគណនីសាលា</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                        <div class="text-sm font-medium text-gray-500">គណនីសាលាសរុប</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.total }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                        <div class="text-sm font-medium text-gray-500">សាលាដំណើរការហើយ</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.configured }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                        <div class="text-sm font-medium text-gray-500">មិនទាន់រៀបចំសាលា</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.unconfigured }}</div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-6 items-start">
                    <!-- Create User Form -->
                    <div class="w-full md:w-1/3 p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">បង្កើតគណនីថ្មី</h2>
                                <p class="mt-1 text-sm text-gray-600">បង្កើតគណនីថ្មីសម្រាប់ឱ្យនាយក ឬអ្នកគ្រប់គ្រងសាលាប្រើប្រាស់។</p>
                            </header>

                            <form @submit.prevent="submit" class="mt-6 space-y-6">
                                <div>
                                    <InputLabel for="name" value="ឈ្មោះ" />
                                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div>
                                    <InputLabel for="email" value="អ៊ីមែល" />
                                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div>
                                    <InputLabel for="phone" value="លេខទូរសព្ទ" />
                                    <TextInput id="phone" type="text" class="mt-1 block w-full" v-model="form.phone" />
                                    <InputError class="mt-2" :message="form.errors.phone" />
                                </div>

                                <div>
                                    <InputLabel for="password" value="ពាក្យសម្ងាត់" />
                                    <div class="relative mt-1">
                                        <TextInput id="password" type="text" class="block w-full pr-10" v-model="form.password" required />
                                        <button type="button" @click="generatePassword(form)" class="absolute inset-y-0 right-0 px-3 flex items-center text-blue-600 hover:text-blue-500 transition-colors" title="បង្កើតស្វ័យប្រវត្តិ">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.password" />
                                </div>

                                <div class="flex items-center gap-4">
                                    <PrimaryButton :disabled="form.processing">បង្កើតគណនី</PrimaryButton>
                                    <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                        <p v-if="form.recentlySuccessful" class="text-sm text-green-600">បានបង្កើតដោយជោគជ័យ!</p>
                                    </Transition>
                                </div>
                            </form>
                        </section>
                    </div>

                    <!-- List Users -->
                    <div class="w-full md:w-2/3 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                            <h3 class="text-lg font-medium text-gray-900">បញ្ជីគណនីសាលា</h3>
                            <div class="w-full sm:w-64">
                                <TextInput v-model="search" type="text" class="block w-full text-sm" placeholder="ស្វែងរកតាមឈ្មោះ អ៊ីមែល ឬសាលា..." />
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ឈ្មោះ</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">អ៊ីមែល</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">លេខទូរសព្ទ</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">សាលារៀន</th>
                                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.phone || 'គ្មាន' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            <span v-if="user.school" class="text-gray-900 truncate block max-w-[150px]" :title="user.school.name">
                                                {{ user.school.name }}
                                            </span>
                                            <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                មិនទាន់រៀបចំសាលា
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="openEditModal(user)" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="កែប្រែ">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block">
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>
                                            <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900 ml-3 transition-colors" title="លុប">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block">
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="users.data.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                            មិនមានទិន្នន័យ។
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4" v-if="users.links && users.links.length > 3">
                            <Pagination :links="users.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <Modal :show="showEditModal" @close="closeEditModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">កែប្រែគណនី</h2>
                <form @submit.prevent="submitEdit" class="space-y-6">
                    <div>
                        <InputLabel for="edit_name" value="ឈ្មោះ" />
                        <TextInput id="edit_name" type="text" class="mt-1 block w-full" v-model="editForm.name" required />
                        <InputError class="mt-2" :message="editForm.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="edit_email" value="អ៊ីមែល" />
                        <TextInput id="edit_email" type="email" class="mt-1 block w-full" v-model="editForm.email" required />
                        <InputError class="mt-2" :message="editForm.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="edit_phone" value="លេខទូរសព្ទ" />
                        <TextInput id="edit_phone" type="text" class="mt-1 block w-full" v-model="editForm.phone" />
                        <InputError class="mt-2" :message="editForm.errors.phone" />
                    </div>

                    <div>
                        <InputLabel for="edit_password" value="ពាក្យសម្ងាត់ (ទុកទទេបើមិនចង់ប្ដូរ)" />
                        <div class="relative mt-1">
                            <TextInput id="edit_password" type="text" class="block w-full pr-10" v-model="editForm.password" />
                            <button type="button" @click="generatePassword(editForm)" class="absolute inset-y-0 right-0 px-3 flex items-center text-blue-600 hover:text-blue-500 transition-colors" title="បង្កើតស្វ័យប្រវត្តិ">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="editForm.errors.password" />
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-6">
                        <SecondaryButton @click="closeEditModal">បោះបង់</SecondaryButton>
                        <PrimaryButton :disabled="editForm.processing">រក្សាទុក</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
