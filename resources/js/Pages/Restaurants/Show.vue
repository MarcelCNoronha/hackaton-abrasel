<script setup>
import { computed, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    restaurant: { type: Object, required: true },
});

// appUrl (nao route()/window.location) -- precisa funcionar tambem no processo Node do SSR,
// onde nao ha window nem o config de rotas do Ziggy.
const appUrl = usePage().props.appUrl;

const weekdayNames = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

const sortedHours = computed(() =>
    [...(props.restaurant.business_hours ?? [])].sort((a, b) => a.weekday - b.weekday),
);

const today = new Date().getDay();

const directionsUrl = computed(
    () =>
        `https://www.google.com/maps/dir/?api=1&destination=${props.restaurant.latitude},${props.restaurant.longitude}`,
);

// Tenta pegar a localizacao atual antes de abrir o Maps e passa como origin explicito --
// sem isso o Maps pede a localizacao de novo com sua propria permissao, que pode nao ter
// sido concedida mesmo com a do app. Sem geolocalizacao disponivel, cai no href normal
// (sem origin), que o Maps ja resolve como "sua localizacao" por conta propria.
function openDirections(event) {
    if (!navigator.geolocation) return;

    event.preventDefault();

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            window.open(`${directionsUrl.value}&origin=${latitude},${longitude}`, '_blank', 'noopener');
        },
        () => window.open(directionsUrl.value, '_blank', 'noopener'),
        { timeout: 4000 },
    );
}

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

const heroStyle = computed(() => {
    if (props.restaurant.banner_photo_path) {
        return {
            backgroundImage: `linear-gradient(rgba(18,11,8,.35), rgba(18,11,8,.75)), url(/storage/${props.restaurant.banner_photo_path})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
        };
    }

    const pair = gradients[props.restaurant.id % gradients.length];
    return { background: `linear-gradient(135deg, ${pair[0]}, ${pair[1]})` };
});

const reviewsDialog = ref(false);

const canonicalUrl = computed(() => `${appUrl}/restaurantes/${props.restaurant.slug}`);

const metaDescription = computed(() => {
    const r = props.restaurant;
    const cuisines = (r.cuisines ?? []).map((c) => c.name).join(', ');
    const ratingPart = Number(r.average_rating) > 0 ? `nota ${Number(r.average_rating).toFixed(1)}` : 'novo no VicosaFood';
    const cuisinePart = cuisines ? `${cuisines} em` : 'Restaurante em';
    return `${r.name} -- ${cuisinePart} ${r.address_neighborhood ?? 'Viçosa'}, MG. ${ratingPart}, cardápio, horário de funcionamento e como chegar.`;
});

const ogImage = computed(() => {
    const path = props.restaurant.banner_photo_path ?? props.restaurant.cover_photo_path;
    return path ? `${appUrl}/storage/${path}` : null;
});

// schema.org/Restaurant -- rich snippets (nota, endereco, geo, horario) pra busca local
// ("GEO"), alem do que os campos OG/description acima cobrem pra preview em redes sociais.
const jsonLd = computed(() => {
    const r = props.restaurant;
    const data = {
        '@context': 'https://schema.org',
        '@type': 'Restaurant',
        name: r.name,
        url: canonicalUrl.value,
        servesCuisine: (r.cuisines ?? []).map((c) => c.name),
        priceRange: r.price_range ?? undefined,
        telephone: r.phone ?? undefined,
        address: {
            '@type': 'PostalAddress',
            streetAddress: [r.address_street, r.address_number].filter(Boolean).join(', ') || undefined,
            addressLocality: r.address_city ?? 'Viçosa',
            addressRegion: r.address_state ?? 'MG',
            addressCountry: 'BR',
        },
        geo: {
            '@type': 'GeoCoordinates',
            latitude: r.latitude,
            longitude: r.longitude,
        },
    };

    if (ogImage.value) data.image = ogImage.value;

    if (Number(r.reviews_count) > 0) {
        data.aggregateRating = {
            '@type': 'AggregateRating',
            ratingValue: Number(r.average_rating).toFixed(1),
            reviewCount: r.reviews_count,
        };
    }

    if (sortedHours.value.length) {
        data.openingHoursSpecification = sortedHours.value
            .filter((h) => !h.is_closed)
            .map((h) => ({
                '@type': 'OpeningHoursSpecification',
                dayOfWeek: [
                    'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday',
                ][h.weekday],
                opens: h.opens_at?.slice(0, 5),
                closes: h.closes_at?.slice(0, 5),
            }));
    }

    return JSON.stringify(data);
});
</script>

<template>
    <Head>
        <title>{{ restaurant.name }} em Viçosa, MG</title>
        <meta name="description" :content="metaDescription" />
        <link rel="canonical" :href="canonicalUrl" />

        <meta property="og:type" content="restaurant.restaurant" />
        <meta property="og:title" :content="`${restaurant.name} em Viçosa, MG`" />
        <meta property="og:description" :content="metaDescription" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta v-if="ogImage" property="og:image" :content="ogImage" />
        <meta property="og:locale" content="pt_BR" />

        <meta name="twitter:card" :content="ogImage ? 'summary_large_image' : 'summary'" />
        <meta name="twitter:title" :content="`${restaurant.name} em Viçosa, MG`" />
        <meta name="twitter:description" :content="metaDescription" />
        <meta v-if="ogImage" name="twitter:image" :content="ogImage" />

        <meta v-if="restaurant.address_neighborhood" name="geo.placename" :content="`${restaurant.address_neighborhood}, Viçosa, MG`" />
        <meta name="geo.position" :content="`${restaurant.latitude};${restaurant.longitude}`" />
        <meta name="ICBM" :content="`${restaurant.latitude}, ${restaurant.longitude}`" />

        <script type="application/ld+json" v-html="jsonLd"></script>
    </Head>

    <PublicLayout>
        <div class="hero" :style="heroStyle">
            <v-container style="max-width: 1040px">
                <div class="d-flex flex-wrap align-center justify-space-between ga-3">
                    <div>
                        <div class="d-flex flex-wrap align-center ga-2 mb-2">
                            <v-menu>
                                <template #activator="{ props: menuProps }">
                                    <v-chip
                                        :color="restaurant.is_open_now ? 'secondary' : undefined"
                                        :variant="restaurant.is_open_now ? 'flat' : 'outlined'"
                                        size="small"
                                        class="hero-chip"
                                        v-bind="menuProps"
                                        style="cursor: pointer"
                                        append-icon="mdi-chevron-down"
                                    >
                                        <v-icon icon="mdi-circle" size="8" start color="white" />
                                        {{ restaurant.is_open_now ? 'Aberto agora' : 'Fechado agora' }}
                                    </v-chip>
                                </template>
                                <v-card min-width="220">
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
                            </v-menu>
                        </div>
                        <h1 class="text-h4 font-weight-bold text-white mb-1">{{ restaurant.name }}</h1>
                        <div class="d-flex flex-wrap align-center ga-3 text-white hero-meta">
                            <span class="d-flex align-center ga-1 hero-meta-trigger" @click="reviewsDialog = true">
                                <v-icon icon="mdi-star" size="18" />
                                {{ Number(restaurant.average_rating).toFixed(1) }}
                                ({{ restaurant.reviews_count }} avaliações)
                                <v-icon icon="mdi-chevron-down" size="14" />
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
                            <v-menu v-if="address">
                                <template #activator="{ props: menuProps }">
                                    <span class="d-flex align-center ga-1 hero-meta-trigger" v-bind="menuProps">
                                        <v-icon icon="mdi-map-marker-outline" size="18" />
                                        Endereço
                                        <v-icon icon="mdi-chevron-down" size="14" />
                                    </span>
                                </template>
                                <v-card min-width="240" max-width="320">
                                    <v-card-text>{{ address }}</v-card-text>
                                </v-card>
                            </v-menu>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap ga-2">
                        <v-btn color="primary" variant="flat" prepend-icon="mdi-directions" :href="directionsUrl" target="_blank" rel="noopener" @click="openDirections">
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

            <div class="d-flex align-center ga-2 mb-4">
                <v-icon icon="mdi-book-open-variant" color="primary" />
                <h2 class="text-h5 font-weight-bold">Cardápio</h2>
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
                                    <template v-if="item.main_photo_path" #prepend>
                                        <v-img :src="`/storage/${item.main_photo_path}`" width="56" height="56" cover rounded="lg" />
                                    </template>
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
        </v-container>

        <v-dialog v-model="reviewsDialog" max-width="640" scrollable>
            <v-card>
                <v-card-title class="d-flex align-center justify-space-between">
                    Avaliações
                    <v-btn icon="mdi-close" variant="text" size="small" @click="reviewsDialog = false" />
                </v-card-title>
                <v-card-text style="max-height: 70vh">
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
                </v-card-text>
            </v-card>
        </v-dialog>
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

.hero-meta-trigger {
    cursor: pointer;
}

.hero-meta-trigger:hover {
    text-decoration: underline;
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
