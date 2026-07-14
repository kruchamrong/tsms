<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                លុបគណនី
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                នៅពេលដែលគណនីរបស់អ្នកត្រូវបានលុប ទិន្នន័យទាំងអស់របស់អ្នកនឹងត្រូវបានលុបជាអចិន្ត្រៃយ៍។
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">លុបគណនី</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    តើអ្នកពិតជាចង់លុបគណនីរបស់អ្នកមែនទេ?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    នៅពេលគណនីរបស់អ្នកត្រូវបានលុប ទិន្នន័យទាំងអស់នឹងត្រូវបានលុបជាអចិន្ត្រៃយ៍។ សូមវាយបញ្ចូលពាក្យសម្ងាត់របស់អ្នក ដើម្បីបញ្ជាក់ពីការលុបនេះ។
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Password"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="ពាក្យសម្ងាត់"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        បោះបង់
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        លុបគណនី
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
