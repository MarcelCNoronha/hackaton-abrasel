<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirmar senha" />

        <h1 class="text-h5 font-weight-bold mb-4">Confirme sua senha</h1>

        <p class="text-body-2 text-medium-emphasis mb-4">
            Esta é uma área segura da aplicação. Confirme sua senha antes de continuar.
        </p>

        <form @submit.prevent="submit">
            <v-text-field
                v-model="form.password"
                label="Senha"
                type="password"
                autocomplete="current-password"
                autofocus
                required
                :error-messages="form.errors.password"
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
                Confirmar
            </v-btn>
        </form>
    </GuestLayout>
</template>
