<script setup>
import { computed } from 'vue';
import { emojiForRestaurant } from '@/utils/categoryIcons';

const props = defineProps({
    restaurant: { type: Object, required: true },
    highlighted: { type: Boolean, default: false },
});

const gradients = [
    ['#F2A93B', '#E15241'],
    ['#1F6E43', '#5FA777'],
    ['#2F6FED', '#7AA6FF'],
    ['#B93E30', '#E15241'],
    ['#16512F', '#3E8F63'],
    ['#7A4CC0', '#B084F0'],
];

const gradient = computed(() => {
    const pair = gradients[props.restaurant.id % gradients.length];
    return `linear-gradient(135deg, ${pair[0]}, ${pair[1]})`;
});

const distanceLabel = computed(() => {
    const km = props.restaurant.distance_km;
    if (km === undefined || km === null) return null;
    const value = Number(km);
    return value < 1 ? `${Math.round(value * 1000)} m` : `${value.toFixed(1)} km`;
});

// A foto do prato em destaque (quando o dono escolheu um) vence a foto de capa do
// restaurante nessa miniatura -- e' exatamente esse espaco que devia diferenciar cada
// estabelecimento, em vez de repetir a mesma foto/ícone genérico do perfil.
const featuredItem = computed(() => props.restaurant.featured_item ?? null);
const thumbPhotoPath = computed(() => featuredItem.value?.main_photo_path ?? props.restaurant.cover_photo_path ?? null);

function formatPrice(value) {
    return `R$ ${Number(value).toFixed(2).replace('.', ',')}`;
}
</script>

<template>
    <v-card
        class="restaurant-card mb-3"
        :class="{ 'restaurant-card--highlighted': highlighted }"
        variant="flat"
        :href="route('restaurants.show', restaurant.slug)"
    >
        <div class="d-flex">
            <div
                class="restaurant-thumb"
                :style="thumbPhotoPath ? {} : { background: gradient }"
            >
                <img
                    v-if="thumbPhotoPath"
                    :src="`/storage/${thumbPhotoPath}`"
                    :alt="featuredItem ? `${featuredItem.name} -- ${restaurant.name}` : restaurant.name"
                    class="restaurant-thumb__photo"
                />
                <span v-else class="restaurant-thumb__emoji">{{ emojiForRestaurant(restaurant.categories) }}</span>
                <span v-if="featuredItem" class="restaurant-thumb__badge">
                    <v-icon icon="mdi-star" size="10" />
                    Destaque
                </span>
            </div>

            <div class="flex-grow-1 pa-3">
                <div class="d-flex align-start justify-space-between ga-2">
                    <div class="text-subtitle-1 font-weight-bold text-truncate">
                        {{ restaurant.name }}
                    </div>
                    <v-chip
                        :color="restaurant.is_open_now ? 'secondary' : undefined"
                        :variant="restaurant.is_open_now ? 'flat' : 'outlined'"
                        size="x-small"
                        class="flex-shrink-0"
                    >
                        {{ restaurant.is_open_now ? 'Aberto agora' : 'Fechado' }}
                    </v-chip>
                </div>

                <div class="d-flex align-center flex-wrap ga-2 mt-1 text-body-2 text-medium-emphasis">
                    <span class="d-flex align-center ga-1">
                        <v-icon icon="mdi-star" color="accent" size="16" />
                        {{ Number(restaurant.average_rating).toFixed(1) }}
                        <span class="text-caption">({{ restaurant.reviews_count }})</span>
                    </span>
                    <span v-if="distanceLabel" class="d-flex align-center ga-1">
                        <v-icon icon="mdi-map-marker-outline" size="16" />
                        {{ distanceLabel }}
                    </span>
                    <span class="font-weight-bold text-primary">{{ restaurant.price_range }}</span>
                </div>

                <div v-if="featuredItem" class="mt-2 d-flex align-center ga-1 text-body-2 featured-line">
                    <v-icon icon="mdi-fire" size="16" color="accent" />
                    <span class="text-truncate" style="min-width: 0">{{ featuredItem.name }}</span>
                    <span class="font-weight-bold flex-shrink-0">{{ formatPrice(featuredItem.price) }}</span>
                </div>

                <div class="mt-2 d-flex flex-wrap ga-1">
                    <v-chip
                        v-for="cuisine in restaurant.cuisines?.slice(0, 3)"
                        :key="cuisine.id"
                        size="x-small"
                        variant="tonal"
                    >
                        {{ cuisine.name }}
                    </v-chip>
                </div>
            </div>
        </div>
    </v-card>
</template>

<style scoped>
.restaurant-card {
    background: rgb(var(--v-theme-surface));
    border: 1px solid rgba(249, 115, 22, 0.28);
    overflow: hidden;
    transition:
        transform 0.15s ease,
        box-shadow 0.15s ease,
        border-color 0.15s ease;
}

.restaurant-card:hover,
.restaurant-card--highlighted {
    border-color: #f97316;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35), 0 0 24px -8px rgba(249, 115, 22, 0.5);
    transform: translateY(-1px);
}

.restaurant-thumb {
    position: relative;
    width: 96px;
    min-width: 96px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.restaurant-thumb__badge {
    position: absolute;
    left: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    gap: 2px;
    background: rgba(28, 18, 12, 0.85);
    color: #fdf6f0;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 2px 6px;
    border-top-right-radius: 6px;
}

.featured-line {
    color: rgb(var(--v-theme-accent));
    font-weight: 600;
}

.restaurant-thumb__photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.restaurant-thumb__emoji {
    font-size: 32px;
    line-height: 1;
}
</style>
