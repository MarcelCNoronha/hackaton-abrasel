<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar senha" />

        <h1 class="text-h5 font-weight-bold mb-4">Esqueceu a senha?</h1>

        <p class="text-body-2 text-medium-emphasis mb-4">
            Sem problema. Informe seu e-mail e enviaremos um link pra você escolher uma nova senha.
        </p>

        <v-alert v-if="status" type="success" variant="tonal" class="mb-4">
            {{ status }}
        </v-alert>

        <form @submit.prevent="submit">
            <v-text-field
                v-model="form.email"
                label="E-mail"
                type="email"
                autocomplete="username"
                autofocus
                required
                :error-messages="form.errors.email"
            />

            <v-btn
                type="submit"
                color="primary"
                variant="flat"
                size="large"
                block
                class="mt-4"
                :loading="form.processing"
            >
                Enviar link de recuperação
            </v-btn>
        </form>
    </GuestLayout>
</template>
