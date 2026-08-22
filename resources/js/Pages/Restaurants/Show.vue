<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    restaurant: { type: Object, required: true },
});

const weekdayNames = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

const sortedHours = computed(() =>
    [...(props.restaurant.business_hours ?? [])].sort((a, b) => a.weekday - b.weekday),
);

const today = new Date().getDay();

const directionsUrl = computed(
    () =>
        `https://www.google.com/maps/dir/?api=1&destination=${props.restaurant.latitude},${props.restaurant.longitude}`,
);

const whatsappUrl = computed(() => {
    if (!props.restaurant.whatsapp) return null;
    const digits = props.restaurant.whatsapp.replace(/\D/g, '');
    return `https://wa.me/${digits}`;
});

const address = computed(() => {
    const r = props.restaurant;
    return [r.address_street, r.address_number, r.address_neighborhood, r.address_city, r.address_state]
        .filter(Boolean)
        .join(', ');
});

const gradients = [
    ['#F2A93B', '#E15241'],
    ['#1F6E43', '#5FA777'],
    ['#2F6FED', '#7AA6FF'],
    ['#B93E30', '#E15241'],
    ['#16512F', '#3E8F63'],
    ['#7A4CC0', '#B084F0'],
];

const heroGradient = computed(() => {
    const pair = gradients[props.restaurant.id % gradients.length];
    return `linear-gradient(135deg, ${pair[0]}, ${pair[1]})`;
});
</script>

<template>
    <Head :title="restaurant.name" />

    <PublicLayout>
        <div class="hero" :style="{ background: heroGradient }">
            <v-container style="max-width: 1040px">
                <div class="d-flex flex-wrap align-center justify-space-between ga-3">
                    <div>
                        <div class="d-flex flex-wrap align-center ga-2 mb-2">
                            <v-chip
                                :color="restaurant.is_open_now ? 'white' : undefined"
                                :variant="restaurant.is_open_now ? 'flat' : 'outlined'"
                                size="small"
                                class="hero-chip"
                            >
                                <v-icon icon="mdi-circle" size="8" start :color="restaurant.is_open_now ? 'secondary' : 'white'" />
                                {{ restaurant.is_open_now ? 'Aberto agora' : 'Fechado agora' }}
                            </v-chip>
                        </div>
                        <h1 class="text-h4 font-weight-bold text-white mb-1">{{ restaurant.name }}</h1>
                        <div class="d-flex flex-wrap align-center ga-3 text-white hero-meta">
                            <span class="d-flex align-center ga-1">
                                <v-icon icon="mdi-star" size="18" />
                                {{ Number(restaurant.average_rating).toFixed(1) }}
                                ({{ restaurant.reviews_count }} avaliações)
                            </span>
                            <span class="font-weight-bold">{{ restaurant.price_range }}</span>
                            <span v-if="restaurant.distance_km != null" class="d-flex align-center ga-1">
                                <v-icon icon="mdi-map-marker-outline" size="18" />
                                {{
                                    restaurant.distance_km < 1
                                        ? Math.round(restaurant.distance_km * 1000) + ' m'
                                        : Number(restaurant.distance_km).toFixed(1) + ' km'
                                }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap ga-2">
                        <v-btn color="primary" variant="flat" prepend-icon="mdi-directions" :href="directionsUrl" target="_blank" rel="noopener">
                            Como chegar
                        </v-btn>
                        <v-btn v-if="restaurant.phone" variant="outlined" color="white" prepend-icon="mdi-phone" :href="`tel:${restaurant.phone}`">
                            Ligar
                        </v-btn>
                        <v-btn v-if="whatsappUrl" variant="outlined" color="white" prepend-icon="mdi-whatsapp" :href="whatsappUrl" target="_blank" rel="noopener">
                            WhatsApp
                        </v-btn>
                    </div>
                </div>
            </v-container>
        </div>

        <v-container class="py-6" style="max-width: 1040px">
            <div class="d-flex flex-wrap ga-2 mb-4">
                <v-chip v-for="c in restaurant.categories" :key="'cat-' + c.id" size="small" variant="tonal">
                    {{ c.name }}
                </v-chip>
                <v-chip v-for="c in restaurant.cuisines" :key="'cui-' + c.id" size="small" variant="outlined">
                    {{ c.name }}
                </v-chip>
            </div>

            <p v-if="restaurant.description" class="text-body-1 text-medium-emphasis mb-6" style="max-width: 640px">
                {{ restaurant.description }}
            </p>

            <v-row>
                <v-col cols="12" md="8">
                    <div class="d-flex align-center ga-2 mb-4">
                        <v-icon icon="mdi-book-open-variant" color="primary" />
                        <h2 id="cardapio" class="text-h5 font-weight-bold">Cardápio</h2>
                    </div>

                    <v-alert v-if="!restaurant.menus?.length" type="info" variant="tonal" class="mb-6">
                        Cardápio ainda não cadastrado.
                    </v-alert>

                    <div v-for="menu in restaurant.menus" :key="menu.id" class="mb-6">
                        <div v-for="category in menu.categories" :key="category.id" class="mb-5">
                            <h3 class="text-subtitle-1 font-weight-bold mb-2 text-primary">
                                {{ category.name }}
                            </h3>

                            <v-card v-for="item in category.items" :key="item.id" variant="outlined" class="mb-2 menu-item-card">
                                <v-card-item>
                                    <v-card-title class="d-flex justify-space-between align-start ga-2">
                                        <span>{{ item.name }}</span>
                                        <span class="text-no-wrap text-right">
                                            <template v-if="item.compare_at_price && Number(item.compare_at_price) > Number(item.price)">
                                                <span class="d-block text-caption text-medium-emphasis text-decoration-line-through">
                                                    De R$ {{ Number(item.compare_at_price).toFixed(2).replace('.', ',') }}
                                                </span>
                                                <span class="text-primary font-weight-bold">
                                                    Por R$ {{ Number(item.price).toFixed(2).replace('.', ',') }}
                                                </span>
                                            </template>
                                            <span v-else class="text-primary font-weight-bold">
                                                R$ {{ Number(item.price).toFixed(2).replace('.', ',') }}
                                            </span>
                                        </span>
                                    </v-card-title>
                                    <v-card-subtitle v-if="item.description" style="white-space: normal">
                                        {{ item.description }}
                                    </v-card-subtitle>
                                </v-card-item>
                                <v-card-text v-if="item.dietary_tags?.length || !item.is_available || (item.compare_at_price && Number(item.compare_at_price) > Number(item.price))" class="pt-0">
                                    <v-chip
                                        v-if="item.compare_at_price && Number(item.compare_at_price) > Number(item.price)"
                                        size="small" color="error" variant="flat" class="mr-1"
                                    >
                                        -{{ Math.round((1 - Number(item.price) / Number(item.compare_at_price)) * 100) }}%
                                    </v-chip>
                                    <v-chip v-if="!item.is_available" size="small" variant="outlined" class="mr-1">
                                        Indisponível
                                    </v-chip>
                                    <v-chip
                                        v-for="tag in item.dietary_tags"
                                        :key="tag.id"
                                        size="small"
                                        :color="tag.kind === 'allergen' ? 'secondary' : undefined"
                                        variant="tonal"
                                        class="mr-1"
                                    >
                                        {{ tag.name }}
                                    </v-chip>
                                </v-card-text>
                            </v-card>
                        </div>
                    </div>

                    <div class="d-flex align-center ga-2 mb-4 mt-8">
                        <v-icon icon="mdi-message-star-outline" color="primary" />
                        <h2 class="text-h5 font-weight-bold">Avaliações</h2>
                    </div>

                    <v-alert v-if="!restaurant.reviews?.length" type="info" variant="tonal">
                        Ainda não há avaliações verificadas para este estabelecimento.
                    </v-alert>

                    <v-card v-for="review in restaurant.reviews" :key="review.id" variant="outlined" class="mb-3">
                        <v-card-item>
                            <v-card-title class="d-flex align-center ga-2">
                                {{ review.user?.name }}
                                <v-chip size="x-small" color="secondary" variant="flat" prepend-icon="mdi-check-decagram">
                                    Visita verificada
                                </v-chip>
                            </v-card-title>
                            <v-card-subtitle class="d-flex align-center ga-1">
                                <v-icon icon="mdi-star" size="14" color="accent" /> {{ review.rating }}
                            </v-card-subtitle>
                        </v-card-item>
                        <v-card-text>
                            <p v-if="review.comment">{{ review.comment }}</p>
                            <div v-if="review.reply" class="reply-box mt-2">
                                <strong>Resposta do estabelecimento</strong>
                                <p class="mb-0">{{ review.reply.message }}</p>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <v-col cols="12" md="4">
                    <v-card variant="outlined" class="mb-4">
                        <v-card-item>
                            <template #prepend>
                                <v-icon icon="mdi-map-marker-outline" color="primary" />
                            </template>
                            <v-card-title class="text-subtitle-1">Endereço</v-card-title>
                        </v-card-item>
                        <v-card-text>{{ address || 'Endereço não informado' }}</v-card-text>
                    </v-card>

                    <v-card variant="outlined">
                        <v-card-item>
                            <template #prepend>
                                <v-icon icon="mdi-clock-outline" color="primary" />
                            </template>
                            <v-card-title class="text-subtitle-1">Horário de funcionamento</v-card-title>
                        </v-card-item>
                        <v-list density="compact">
                            <v-list-item
                                v-for="hours in sortedHours"
                                :key="hours.id"
                                :class="{ 'font-weight-bold': hours.weekday === today }"
                            >
                                <div class="d-flex justify-space-between" style="width: 100%">
                                    <span>{{ weekdayNames[hours.weekday] }}</span>
                                    <span>
                                        {{
                                            hours.is_closed
                                                ? 'Fechado'
                                                : `${hours.opens_at?.slice(0, 5)} – ${hours.closes_at?.slice(0, 5)}`
                                        }}
                                    </span>
                                </div>
                            </v-list-item>
                        </v-list>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </PublicLayout>
</template>

<style scoped>
.hero {
    padding-block: 40px;
}

.hero-chip {
    border-color: rgba(255, 255, 255, 0.6) !important;
}

.hero-meta {
    opacity: 0.95;
}

.menu-item-card {
    border-color: rgba(249, 115, 22, 0.28);
}

.reply-box {
    background: rgb(var(--v-theme-background));
    border-radius: 8px;
    padding: 12px;
}
</style>
