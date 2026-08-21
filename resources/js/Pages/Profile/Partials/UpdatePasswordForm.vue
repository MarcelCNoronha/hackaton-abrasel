<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <h3 class="text-subtitle-1 font-weight-bold mb-1">Alterar senha</h3>
    <p class="text-body-2 text-medium-emphasis mb-4">
        Use uma senha longa e única para manter sua conta segura.
    </p>

    <form @submit.prevent="updatePassword">
        <v-text-field
            ref="currentPasswordInput"
            v-model="form.current_password"
            type="password"
            label="Senha atual"
            autocomplete="current-password"
            :error-messages="form.errors.current_password"
            class="mb-2"
        />

        <v-text-field
            ref="passwordInput"
            v-model="form.password"
            type="password"
            label="Nova senha"
            autocomplete="new-password"
            :error-messages="form.errors.password"
            class="mb-2"
        />

        <v-text-field
            v-model="form.password_confirmation"
            type="password"
            label="Confirmar nova senha"
            autocomplete="new-password"
            :error-messages="form.errors.password_confirmation"
        />

        <div class="d-flex align-center ga-3">
            <v-btn type="submit" color="primary" variant="flat" :loading="form.processing">Salvar</v-btn>

            <Transition
                enter-active-class="transition ease-in-out"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out"
                leave-to-class="opacity-0"
            >
                <span v-if="form.recentlySuccessful" class="text-body-2 text-medium-emphasis">Salvo.</span>
            </Transition>
        </div>
    </form>
</template>
