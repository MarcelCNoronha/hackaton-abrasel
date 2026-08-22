<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { availabilityBadge, weekdayNames } from '@/utils/availabilityBadge';

const props = defineProps({
    freelancer: { type: Object, required: true },
    myRestaurants: { type: Array, default: () => [] },
    myHireRequests: { type: Array, default: () => [] },
});

const badge = computed(() => availabilityBadge(props.freelancer.availability_status));
const activeSlots = computed(() => (props.freelancer.availability_slots ?? []).filter((s) => !s.is_off));

const showContact = ref(false);

const hireDialog = ref(false);
const hireForm = useForm({ restaurant_id: props.myRestaurants[0]?.id ?? null, message: '' });

function sendHireRequest() {
    hireForm.post(route('owner.hire-requests.store', props.freelancer.id), {
        preserveScroll: true,
        onSuccess: () => {
            hireDialog.value = false;
            hireForm.reset();
        },
    });
}

function cancelHireRequest(hireRequest) {
    if (!confirm('Cancelar este pedido de contratação?')) return;
    router.delete(route('owner.hire-requests.cancel', hireRequest.id), { preserveScroll: true });
}

const reviewDialogFor = ref(null);
const reviewForm = useForm({ rating: 5, feedback_to_freelancer: '', feedback_to_owners: '' });

function openReviewDialog(hireRequest) {
    reviewDialogFor.value = hireRequest.id;
    reviewForm.reset();
}

function submitReview() {
    reviewForm.post(route('owner.freelancer-reviews.store', reviewDialogFor.value), {
        preserveScroll: true,
        onSuccess: () => {
            reviewDialogFor.value = null;
        },
    });
}

const statusLabels = {
    pending: { text: 'Pendente', color: 'warning' },
    accepted: { text: 'Aceito', color: 'secondary' },
    declined: { text: 'Recusado', color: undefined },
    cancelled: { text: 'Cancelado', color: undefined },
};
</script>

<template>
    <Head :title="freelancer.user?.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-h5 font-weight-bold">{{ freelancer.user?.name }}</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <v-card class="mb-4">
                    <v-card-item>
                        <v-card-title class="d-flex align-center justify-space-between ga-2">
                            <span>{{ freelancer.headline || freelancer.user?.name }}</span>
                            <v-chip :color="badge.color" size="small" variant="flat">
                                <v-icon :icon="badge.icon" size="10" start />
                                {{ badge.label }}
                            </v-chip>
                        </v-card-title>
                        <v-card-subtitle>
                            <v-icon icon="mdi-star" color="accent" size="16" />
                            {{ Number(freelancer.average_rating).toFixed(1) }}
                            ({{ freelancer.reviews_count }} avaliações)
                        </v-card-subtitle>
                    </v-card-item>
                    <v-card-text>
                        <p v-if="freelancer.bio">{{ freelancer.bio }}</p>
                        <div class="mb-3">
                            <v-chip v-for="skill in freelancer.job_skills" :key="skill.id" size="small" variant="tonal" class="mr-1 mb-1">
                                {{ skill.name }}
                            </v-chip>
                        </div>

                        <div v-if="freelancer.availability_status === 'scheduled' && activeSlots.length" class="mb-3">
                            <p class="text-caption text-medium-emphasis mb-1">Disponível:</p>
                            <div v-for="slot in activeSlots" :key="slot.id" class="text-body-2">
                                {{ weekdayNames[slot.weekday] }}: {{ slot.opens_at?.slice(0, 5) }} – {{ slot.closes_at?.slice(0, 5) }}
                            </div>
                        </div>

                        <v-btn v-if="!showContact" variant="outlined" prepend-icon="mdi-whatsapp" @click="showContact = true">
                            Ver contato
                        </v-btn>
                        <p v-else class="text-body-1">
                            <v-icon icon="mdi-whatsapp" size="18" />
                            {{ freelancer.user?.phone || 'Telefone não informado' }}
                        </p>
                    </v-card-text>
                    <v-card-actions>
                        <v-btn color="primary" variant="flat" @click="hireDialog = true">Solicitar contratação</v-btn>
                    </v-card-actions>
                </v-card>

                <h3 v-if="myHireRequests.length" class="text-subtitle-1 font-weight-bold mb-2">Minhas contratações com este freelancer</h3>
                <v-card v-for="hireRequest in myHireRequests" :key="hireRequest.id" variant="outlined" class="mb-2">
                    <v-card-item>
                        <v-card-title class="d-flex align-center justify-space-between ga-2">
                            {{ hireRequest.restaurant?.name }}
                            <v-chip size="small" :color="statusLabels[hireRequest.status]?.color" variant="tonal">
                                {{ statusLabels[hireRequest.status]?.text ?? hireRequest.status }}
                            </v-chip>
                        </v-card-title>
                    </v-card-item>
                    <v-card-actions v-if="hireRequest.status === 'pending' || (hireRequest.status === 'accepted' && !hireRequest.review)">
                        <v-spacer />
                        <v-btn v-if="hireRequest.status === 'pending'" variant="text" color="error" @click="cancelHireRequest(hireRequest)">
                            Cancelar
                        </v-btn>
                        <v-btn v-if="hireRequest.status === 'accepted' && !hireRequest.review" variant="tonal" color="primary" @click="openReviewDialog(hireRequest)">
                            Avaliar
                        </v-btn>
                    </v-card-actions>
                </v-card>

                <h3 class="text-subtitle-1 font-weight-bold mb-2 mt-6">Referências de outros donos</h3>
                <v-alert v-if="!freelancer.reviews?.length" type="info" variant="tonal">
                    Nenhuma referência ainda.
                </v-alert>
                <v-card v-for="review in freelancer.reviews" :key="review.id" variant="outlined" class="mb-2">
                    <v-card-item>
                        <v-card-title class="d-flex align-center ga-2">
                            <v-icon icon="mdi-star" color="accent" size="16" /> {{ review.rating }}/5
                            <span class="text-body-2 text-medium-emphasis">— {{ review.restaurant?.name }}</span>
                        </v-card-title>
                    </v-card-item>
                    <v-card-text v-if="review.feedback_to_owners">{{ review.feedback_to_owners }}</v-card-text>
                </v-card>
            </div>
        </div>

        <v-dialog v-model="hireDialog" max-width="440">
            <v-card>
                <v-card-title>Solicitar contratação</v-card-title>
                <v-card-text>
                    <v-form @submit.prevent="sendHireRequest">
                        <v-select
                            v-if="myRestaurants.length > 1"
                            v-model="hireForm.restaurant_id"
                            :items="myRestaurants.map((r) => ({ title: r.name, value: r.id }))"
                            label="Estabelecimento"
                            :error-messages="hireForm.errors.restaurant_id"
                        />
                        <v-textarea
                            v-model="hireForm.message"
                            label="Mensagem (opcional)"
                            hint="Ex.: turno, dias da semana, remuneração"
                            persistent-hint
                            rows="3"
                            :error-messages="hireForm.errors.message"
                        />
                        <v-btn type="submit" color="primary" variant="flat" class="mt-3" :loading="hireForm.processing">
                            Enviar pedido
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>

        <v-dialog :model-value="reviewDialogFor !== null" max-width="440" @update:model-value="reviewDialogFor = null">
            <v-card>
                <v-card-title>Avaliar contratação</v-card-title>
                <v-card-text>
                    <v-form @submit.prevent="submitReview">
                        <v-rating v-model="reviewForm.rating" length="5" class="mb-2" />
                        <v-textarea
                            v-model="reviewForm.feedback_to_freelancer"
                            label="Comentário para o freelancer"
                            hint="Ele(a) vai ver isso -- feedback construtivo"
                            persistent-hint
                            rows="2"
                            class="mb-2"
                        />
                        <v-textarea
                            v-model="reviewForm.feedback_to_owners"
                            label="Referência para outros donos"
                            hint="Visível pra outros donos que estejam avaliando contratar essa pessoa"
                            persistent-hint
                            rows="2"
                        />
                        <v-btn type="submit" color="primary" variant="flat" class="mt-3" :loading="reviewForm.processing">
                            Enviar avaliação
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </AuthenticatedLayout>
</template>
