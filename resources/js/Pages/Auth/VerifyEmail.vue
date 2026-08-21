<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Verificar e-mail" />

        <h1 class="text-h5 font-weight-bold mb-4">Verifique seu e-mail</h1>

        <p class="text-body-2 text-medium-emphasis mb-4">
            Antes de começar, confirme seu endereço de e-mail clicando no link que acabamos de
            enviar. Se não recebeu, podemos enviar outro com prazer.
        </p>

        <v-alert v-if="verificationLinkSent" type="success" variant="tonal" class="mb-4">
            Um novo link de verificação foi enviado para o e-mail cadastrado.
        </v-alert>

        <form @submit.prevent="submit" class="d-flex align-center justify-space-between">
            <v-btn type="submit" color="primary" variant="flat" :loading="form.processing">
                Reenviar e-mail de verificação
            </v-btn>

            <Link :href="route('logout')" method="post" as="button" class="text-body-2 text-primary">
                Sair
            </Link>
        </form>
    </GuestLayout>
</template>
