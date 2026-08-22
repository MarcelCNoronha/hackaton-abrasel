<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    hireRequests: { type: Array, default: () => [] },
});

const pending = computed(() => props.hireRequests.filter((h) => h.status === 'pending'));
const history = computed(() => props.hireRequests.filter((h) => h.status !== 'pending'));

const statusLabels = {
    accepted: { text: 'Aceito', color: 'secondary' },
    declined: { text: 'Recusado', color: undefined },
    cancelled: { text: 'Cancelado pelo restaurante', color: undefined },
};

function accept(hireRequest) {
    router.patch(route('freelancer.hires.accept', hireRequest.id), {}, { preserveScroll: true });
}

function decline(hireRequest) {
    if (!confirm('Recusar este pedido de contratação?')) return;
    router.patch(route('freelancer.hires.decline', hireRequest.id), {}, { preserveScroll: true });
}

function formatDate(value) {
    return new Date(value).toLocaleDateString('pt-BR');
}
</script>

<template>
    <Head title="Pedidos de contratação" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-h5 font-weight-bold">Pedidos de contratação</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h3 class="text-subtitle-1 font-weight-bold mb-3">Pendentes</h3>
                <v-alert v-if="!pending.length" type="info" variant="tonal" class="mb-6">
                    Nenhum pedido pendente no momento.
                </v-alert>
                <v-card v-for="hireRequest in pending" :key="hireRequest.id" variant="outlined" class="mb-3">
                    <v-card-item>
                        <v-card-title>{{ hireRequest.restaurant?.name }}</v-card-title>
                        <v-card-subtitle>{{ formatDate(hireRequest.created_at) }}</v-card-subtitle>
                    </v-card-item>
                    <v-card-text v-if="hireRequest.message">{{ hireRequest.message }}</v-card-text>
                    <v-card-actions>
                        <v-spacer />
                        <v-btn variant="outlined" @click="decline(hireRequest)">Recusar</v-btn>
                        <v-btn color="primary" variant="flat" @click="accept(hireRequest)">Aceitar</v-btn>
                    </v-card-actions>
                </v-card>

                <h3 class="text-subtitle-1 font-weight-bold mb-3 mt-6">Histórico e avaliações recebidas</h3>
                <v-alert v-if="!history.length" type="info" variant="tonal">
                    Nenhum histórico ainda.
                </v-alert>
                <v-card v-for="hireRequest in history" :key="hireRequest.id" variant="outlined" class="mb-3">
                    <v-card-item>
                        <v-card-title class="d-flex align-center justify-space-between ga-2">
                            {{ hireRequest.restaurant?.name }}
                            <v-chip size="small" :color="statusLabels[hireRequest.status]?.color" variant="tonal">
                                {{ statusLabels[hireRequest.status]?.text ?? hireRequest.status }}
                            </v-chip>
                        </v-card-title>
                        <v-card-subtitle>{{ formatDate(hireRequest.created_at) }}</v-card-subtitle>
                    </v-card-item>
                    <v-card-text v-if="hireRequest.review">
                        <div class="d-flex align-center ga-1 mb-1">
                            <v-icon icon="mdi-star" color="accent" size="16" />
                            <strong>{{ hireRequest.review.rating }}/5</strong>
                        </div>
                        <p v-if="hireRequest.review.feedback_to_freelancer" class="mb-0">
                            "{{ hireRequest.review.feedback_to_freelancer }}"
                        </p>
                        <p v-else class="text-medium-emphasis mb-0">O restaurante não deixou um comentário pra você.</p>
                    </v-card-text>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
