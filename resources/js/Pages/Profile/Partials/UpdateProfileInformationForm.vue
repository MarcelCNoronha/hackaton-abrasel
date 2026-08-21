<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <h3 class="text-subtitle-1 font-weight-bold mb-1">Informações do perfil</h3>
    <p class="text-body-2 text-medium-emphasis mb-4">Atualize seu nome e endereço de e-mail.</p>

    <form @submit.prevent="form.patch(route('profile.update'))">
        <v-text-field
            v-model="form.name"
            label="Nome"
            autofocus
            autocomplete="name"
            :error-messages="form.errors.name"
            class="mb-2"
        />

        <v-text-field
            v-model="form.email"
            type="email"
            label="E-mail"
            autocomplete="username"
            :error-messages="form.errors.email"
        />

        <v-alert v-if="mustVerifyEmail && user.email_verified_at === null" type="warning" variant="tonal" class="mb-4">
            Seu endereço de e-mail ainda não foi verificado.
            <Link :href="route('verification.send')" method="post" as="button" class="font-weight-bold text-decoration-underline">
                Clique aqui para reenviar o e-mail de verificação.
            </Link>
            <div v-show="status === 'verification-link-sent'" class="mt-1 text-body-2 font-weight-bold">
                Um novo link de verificação foi enviado para seu e-mail.
            </div>
        </v-alert>

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
