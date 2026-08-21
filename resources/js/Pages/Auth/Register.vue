<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Cadastrar" />

        <h1 class="text-h5 font-weight-bold mb-6">Criar conta</h1>

        <form @submit.prevent="submit">
            <v-text-field
                v-model="form.name"
                label="Nome"
                autocomplete="name"
                autofocus
                required
                :error-messages="form.errors.name"
                class="mb-2"
            />

            <v-text-field
                v-model="form.email"
                label="E-mail"
                type="email"
                autocomplete="username"
                required
                :error-messages="form.errors.email"
                class="mb-2"
            />

            <v-text-field
                v-model="form.password"
                label="Senha"
                type="password"
                autocomplete="new-password"
                required
                :error-messages="form.errors.password"
                class="mb-2"
            />

            <v-text-field
                v-model="form.password_confirmation"
                label="Confirmar senha"
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
                Cadastrar
            </v-btn>

            <div class="text-center text-body-2 mt-4">
                Já tem conta?
                <Link :href="route('login')" class="text-primary font-weight-bold">Entrar</Link>
            </div>
        </form>
    </GuestLayout>
</template>
