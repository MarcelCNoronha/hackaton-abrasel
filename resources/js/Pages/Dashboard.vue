<script setup>
import { reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    stats: { type: Object, required: true },
    coupons: { type: Array, default: () => [] },
    visits: { type: Array, default: () => [] },
    favorites: { type: Array, default: () => [] },
});

const reviewForms = reactive({});

function reviewForm(visitId) {
    if (!reviewForms[visitId]) {
        reviewForms[visitId] = useForm({ rating: 5, comment: '' });
    }
    return reviewForms[visitId];
}

const openReviewFormFor = reactive({});

function submitReview(visitId) {
    reviewForm(visitId).post(route('reviews.store', visitId), {
        preserveScroll: true,
        onSuccess: () => {
            openReviewFormFor[visitId] = false;
        },
    });
}

function formatDate(value) {
    return new Date(value).toLocaleDateString('pt-BR');
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-h5 font-weight-bold">
                Minha conta
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <v-row class="mb-2">
                    <v-col cols="6" sm="3">
                        <v-card title="Visitas" :subtitle="String(stats.visits)" />
                    </v-col>
                    <v-col cols="6" sm="3">
                        <v-card title="Avaliações" :subtitle="String(stats.reviews)" />
                    </v-col>
                    <v-col cols="6" sm="3">
                        <v-card title="Cupons" :subtitle="String(stats.coupons)" />
                    </v-col>
                    <v-col cols="6" sm="3">
                        <v-card title="Favoritos" :subtitle="String(stats.favorites)" />
                    </v-col>
                </v-row>

                <v-card class="mt-4">
                    <v-card-title class="d-flex align-center justify-space-between flex-wrap ga-1">
                        Meus cupons
                        <v-btn variant="text" color="primary" class="tap-target" :href="route('discover')">
                            Descobrir restaurantes
                        </v-btn>
                    </v-card-title>
                    <v-card-text>
                        <v-alert v-if="!coupons.length" type="info" variant="tonal">
                            Nenhum cupom disponível ainda. Faça check-in em um restaurante e avalie sua
                            visita pra ganhar cupons.
                        </v-alert>
                        <v-row v-else>
                            <v-col v-for="coupon in coupons" :key="coupon.id" cols="12" sm="6" md="4">
                                <v-card variant="outlined">
                                    <v-card-item>
                                        <v-card-title>{{ coupon.restaurant?.name }}</v-card-title>
                                        <v-card-subtitle>Código: {{ coupon.code }}</v-card-subtitle>
                                    </v-card-item>
                                    <v-card-text>
                                        Válido até {{ formatDate(coupon.expires_at) }}
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>

                <v-card class="mt-4">
                    <v-card-title>Minhas visitas</v-card-title>
                    <v-card-text>
                        <v-alert v-if="!visits.length" type="info" variant="tonal">
                            Você ainda não fez check-in em nenhum restaurante. Escaneie o QR Code na mesa
                            na sua próxima visita.
                        </v-alert>
                        <v-card v-for="visit in visits" :key="visit.id" variant="outlined" class="mb-3">
                            <v-card-item>
                                <v-card-title class="d-flex align-center ga-2" style="flex-wrap: nowrap">
                                    <Link
                                        :href="route('restaurants.show', visit.restaurant?.slug)"
                                        class="text-decoration-none text-truncate"
                                        style="min-width: 0"
                                    >
                                        {{ visit.restaurant?.name }}
                                    </Link>
                                    <span class="text-caption text-medium-emphasis flex-shrink-0">{{ formatDate(visit.visited_at) }}</span>
                                </v-card-title>
                            </v-card-item>
                            <v-card-text>
                                <template v-if="visit.review">
                                    <v-chip size="small" color="secondary" variant="tonal" prepend-icon="mdi-check-decagram">
                                        Visita avaliada
                                    </v-chip>
                                </template>
                                <template v-else-if="openReviewFormFor[visit.id]">
                                    <v-form @submit.prevent="submitReview(visit.id)">
                                        <v-rating v-model="reviewForm(visit.id).rating" length="5" class="mb-2" />
                                        <v-textarea
                                            v-model="reviewForm(visit.id).comment"
                                            label="Como foi sua visita? (opcional)"
                                            rows="2"
                                            :error-messages="reviewForm(visit.id).errors.comment"
                                        />
                                        <v-btn type="submit" color="primary" variant="flat" class="tap-target" :loading="reviewForm(visit.id).processing">
                                            Enviar avaliação
                                        </v-btn>
                                    </v-form>
                                </template>
                                <v-btn v-else variant="tonal" color="primary" class="tap-target" @click="openReviewFormFor[visit.id] = true">
                                    Avaliar visita
                                </v-btn>
                            </v-card-text>
                        </v-card>
                    </v-card-text>
                </v-card>

                <v-card class="mt-4 mb-4">
                    <v-card-title>Favoritos</v-card-title>
                    <v-card-text>
                        <v-alert v-if="!favorites.length" type="info" variant="tonal">
                            Você ainda não favoritou nenhum restaurante.
                        </v-alert>
                        <v-chip
                            v-for="restaurant in favorites"
                            :key="restaurant.id"
                            :href="route('restaurants.show', restaurant.slug)"
                            class="mr-2 mb-2"
                            variant="outlined"
                        >
                            {{ restaurant.name }}
                        </v-chip>
                    </v-card-text>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.tap-target {
    min-height: 44px;
}
</style>
