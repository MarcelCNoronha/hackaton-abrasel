<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Entrar" />

        <h1 class="text-h5 font-weight-bold mb-6">Entrar</h1>

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
                class="mb-2"
            />

            <v-text-field
                v-model="form.password"
                label="Senha"
                type="password"
                autocomplete="current-password"
                required
                :error-messages="form.errors.password"
            />

            <div class="d-flex align-center justify-space-between mt-1">
                <v-checkbox v-model="form.remember" label="Lembrar de mim" density="compact" hide-details />

                <Link v-if="canResetPassword" :href="route('password.request')" class="text-body-2 text-primary">
                    Esqueceu a senha?
                </Link>
            </div>

            <v-btn
                type="submit"
                color="primary"
                variant="flat"
                size="large"
                block
                class="mt-4"
                :loading="form.processing"
            >
                Entrar
            </v-btn>

            <div class="text-center text-body-2 mt-4">
                Ainda não tem conta?
                <Link :href="route('register')" class="text-primary font-weight-bold">Cadastre-se</Link>
            </div>
        </form>
    </GuestLayout>
</template>
