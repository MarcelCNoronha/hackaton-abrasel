<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    restaurants: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    restaurant_id: null,
    notes: '',
});

function submit() {
    form.post(route('owner.claims.store'));
}
</script>

<template>
    <Head title="Reivindicar restaurante" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Reivindicar restaurante
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <v-card class="pa-4">
                    <v-card-text>
                        <p class="text-body-2 text-medium-emphasis mb-4">
                            Selecione o estabelecimento que você administra. Um administrador vai revisar
                            o pedido antes de liberar o acesso.
                        </p>

                        <v-alert v-if="restaurants.length === 0" type="info" variant="tonal" class="mb-4">
                            Não há estabelecimentos disponíveis para reivindicar no momento — todos já têm um gestor.
                        </v-alert>

                        <v-form v-else @submit.prevent="submit">
                            <v-select
                                v-model="form.restaurant_id"
                                :items="restaurants.map((r) => ({ title: r.address_city ? `${r.name} · ${r.address_city}` : r.name, value: r.id }))"
                                label="Estabelecimento"
                                :error-messages="form.errors.restaurant_id"
                            />

                            <v-textarea
                                v-model="form.notes"
                                label="Observações para o administrador (opcional)"
                                rows="3"
                                :error-messages="form.errors.notes"
                            />

                            <v-btn type="submit" color="primary" variant="flat" :loading="form.processing" :disabled="!form.restaurant_id">
                                Enviar pedido
                            </v-btn>
                        </v-form>
                    </v-card-text>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
