<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Redefinir senha" />

        <h1 class="text-h5 font-weight-bold mb-6">Redefinir senha</h1>

        <form @submit.prevent="submit">
            <v-text-field
                v-model="form.email"
                label="E-mail"
                type="email"
                autocomplete="username"
                autofocus
                required
                :error-messages="form.errors.email"
                class="mb-2"
            />

            <v-text-field
                v-model="form.password"
                label="Nova senha"
                type="password"
                autocomplete="new-password"
                required
                :error-messages="form.errors.password"
                class="mb-2"
            />

            <v-text-field
                v-model="form.password_confirmation"
                label="Confirmar nova senha"
                type="password"
                autocomplete="new-password"
                required
                :error-messages="form.errors.password_confirmation"
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
                Redefinir senha
            </v-btn>
        </form>
    </GuestLayout>
</template>
