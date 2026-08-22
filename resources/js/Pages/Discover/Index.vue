<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import RestaurantMap from '@/Components/RestaurantMap.vue';
import RestaurantCard from '@/Components/RestaurantCard.vue';
import { emojiForCategorySlug } from '@/utils/categoryIcons';

function toggleCategory(slug) {
    filters.category = filters.category === slug ? null : slug;
}

// appUrl (nao route()) pro <Head> canonical/og:url -- precisa funcionar tambem no processo
// Node do SSR, que nao tem o config de rotas do Ziggy disponivel.
const discoverUrl = `${usePage().props.appUrl}/restaurantes`;

const props = defineProps({
    restaurants: { type: Array, default: () => [] },
    mapRestaurants: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    cuisines: { type: Array, default: () => [] },
    dietaryTags: { type: Array, default: () => [] },
    foodTags: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const filters = reactive({
    q: props.filters.q ?? '',
    category: props.filters.category ?? null,
    cuisines: props.filters.cuisines ?? [],
    dietary_tags: props.filters.dietary_tags ?? [],
    food_tags: props.filters.food_tags ?? [],
    radius_km: props.filters.radius_km ?? null,
    price_range: props.filters.price_range ?? null,
    min_rating: props.filters.min_rating ?? null,
    open_now: props.filters.open_now === true,
    sort: props.filters.sort ?? 'rating',
});

const activeFilterCount = computed(
    () =>
        (filters.category ? 1 : 0) +
        filters.cuisines.length +
        filters.dietary_tags.length +
        filters.food_tags.length +
        (filters.radius_km ? 1 : 0) +
        (filters.price_range ? 1 : 0) +
        (filters.min_rating ? 1 : 0) +
        (filters.open_now ? 1 : 0),
);

// Espelha DiscoveryController@index's $hasAnyFilter -- decide se mostra a lista (vazia por
// escolha, ate' selecionar algo, pra nao virar uma lista infinita) ou o convite pra filtrar.
const hasAnyFilter = computed(() => filters.q.trim() !== '' || activeFilterCount.value > 0);

const userLocation = ref(
    props.filters.lat && props.filters.lng
        ? { lat: Number(props.filters.lat), lng: Number(props.filters.lng) }
        : null,
);
const locationStatus = ref(userLocation.value ? 'granted' : 'idle');
const showFilters = ref(false);
const hoveredRestaurantId = ref(null);

const priceOptions = ['$', '$$', '$$$', '$$$$'];
const distanceOptions = [
    { title: 'Qualquer distância', value: null },
    { title: 'Até 500 m', value: 0.5 },
    { title: 'Até 1 km', value: 1 },
    { title: 'Até 2 km', value: 2 },
    { title: 'Até 5 km', value: 5 },
];
const ratingOptions = [
    { title: 'Qualquer avaliação', value: null },
    { title: '4+ estrelas', value: 4 },
    { title: '4,5+ estrelas', value: 4.5 },
];
const sortOptions = [
    { title: 'Relevância', value: 'rating' },
    { title: 'Mais próximos', value: 'distance' },
    { title: 'Mais avaliados', value: 'reviews' },
];

function requestLocation() {
    if (!navigator.geolocation) {
        locationStatus.value = 'unavailable';
        return;
    }

    locationStatus.value = 'requesting';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            userLocation.value = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
            };
            locationStatus.value = 'granted';
        },
        () => {
            locationStatus.value = 'denied';
        },
        { enableHighAccuracy: true, timeout: 8000 },
    );
}

onMounted(() => {
    if (!userLocation.value) {
        requestLocation();
    }
});

const mapCenter = computed(() => userLocation.value ?? { lat: -20.7546, lng: -42.8825 }); // Viçosa/MG

let debounceTimer = null;

function applyFilters({ immediate = false } = {}) {
    clearTimeout(debounceTimer);

    const run = () => {
        router.get(
            route('discover'),
            {
                q: filters.q || undefined,
                category: filters.category || undefined,
                cuisines: filters.cuisines.length ? filters.cuisines : undefined,
                dietary_tags: filters.dietary_tags.length ? filters.dietary_tags : undefined,
                food_tags: filters.food_tags.length ? filters.food_tags : undefined,
                radius_km: filters.radius_km || undefined,
                price_range: filters.price_range || undefined,
                min_rating: filters.min_rating || undefined,
                open_now: filters.open_now ? 1 : undefined,
                sort: filters.sort,
                lat: userLocation.value?.lat,
                lng: userLocation.value?.lng,
            },
            { preserveState: true, preserveScroll: true, replace: true, only: ['restaurants', 'mapRestaurants', 'filters'] },
        );
    };

    if (immediate) {
        run();
    } else {
        debounceTimer = setTimeout(run, 400);
    }
}

watch(() => filters.q, () => applyFilters());
watch(
    () => [
        filters.category,
        filters.cuisines,
        filters.dietary_tags,
        filters.food_tags,
        filters.radius_km,
        filters.price_range,
        filters.min_rating,
        filters.open_now,
        filters.sort,
    ],
    () => applyFilters({ immediate: true }),
    { deep: true },
);
watch(userLocation, () => applyFilters({ immediate: true }));

function clearFilters() {
    filters.category = null;
    filters.cuisines = [];
    filters.dietary_tags = [];
    filters.food_tags = [];
    filters.radius_km = null;
    filters.price_range = null;
    filters.min_rating = null;
    filters.open_now = false;
}
</script>

<template>
    <Head>
        <title>Restaurantes em Viçosa, MG</title>
        <meta
            name="description"
            content="Descubra restaurantes, bares e lanchonetes em Viçosa/MG. Filtre por tipo de comida, prato ou restrição alimentar, veja avaliações reais e cupons exclusivos."
        />
        <link rel="canonical" :href="discoverUrl" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="Restaurantes em Viçosa, MG | VicosaFood" />
        <meta
            property="og:description"
            content="Descubra restaurantes, bares e lanchonetes em Viçosa/MG, com avaliações reais e cupons exclusivos."
        />
        <meta property="og:url" :content="discoverUrl" />
        <meta property="og:locale" content="pt_BR" />
    </Head>

    <PublicLayout>
        <div class="discover-shell">
            <div class="search-bar">
                <v-container fluid style="max-width: 1400px">
                    <div class="d-flex flex-wrap align-center ga-3">
                        <v-text-field
                            v-model="filters.q"
                            placeholder="Buscar restaurante, tipo de comida ou prato..."
                            prepend-inner-icon="mdi-magnify"
                            hide-details
                            clearable
                            class="search-field"
                            bg-color="surface"
                        />
                    </div>

                    <div class="category-quickbar">
                        <button
                            v-for="category in categories"
                            :key="category.id"
                            type="button"
                            class="category-quickbar__item"
                            :class="{ 'category-quickbar__item--active': filters.category === category.slug }"
                            @click="toggleCategory(category.slug)"
                        >
                            <span class="category-quickbar__emoji">{{ emojiForCategorySlug(category.slug) }}</span>
                            <span class="category-quickbar__label">{{ category.name }}</span>
                        </button>
                    </div>

                    <div class="d-flex flex-wrap align-center ga-3 mt-3">
                        <v-btn
                            :variant="showFilters ? 'flat' : 'tonal'"
                            :color="showFilters ? 'primary' : undefined"
                            prepend-icon="mdi-tune-variant"
                            @click="showFilters = !showFilters"
                        >
                            Filtros
                            <v-badge
                                v-if="activeFilterCount"
                                :content="activeFilterCount"
                                color="accent"
                                inline
                            />
                        </v-btn>

                        <v-select
                            v-model="filters.sort"
                            :items="sortOptions"
                            hide-details
                            style="max-width: 190px"
                            prepend-inner-icon="mdi-sort"
                            bg-color="surface"
                        />

                        <v-btn
                            variant="tonal"
                            :color="locationStatus === 'granted' ? 'secondary' : 'primary'"
                            :prepend-icon="locationStatus === 'granted' ? 'mdi-crosshairs-gps' : 'mdi-map-marker-outline'"
                            :loading="locationStatus === 'requesting'"
                            @click="requestLocation"
                        >
                            {{ locationStatus === 'granted' ? 'Localização ativa' : 'Usar minha localização' }}
                        </v-btn>
                    </div>

                    <v-expand-transition>
                        <div v-if="showFilters" class="filters-panel">
                            <div class="filter-group">
                                <span class="filter-label">Tipo de comida</span>
                                <v-autocomplete
                                    v-model="filters.cuisines"
                                    :items="cuisines.map((c) => ({ title: c.name, value: c.slug }))"
                                    placeholder="Digite pra buscar..."
                                    multiple
                                    chips
                                    closable-chips
                                    clearable
                                    hide-details
                                    density="compact"
                                    bg-color="surface"
                                />
                            </div>

                            <div class="filter-group">
                                <span class="filter-label">Prato ou ingrediente</span>
                                <v-autocomplete
                                    v-model="filters.food_tags"
                                    :items="foodTags.map((t) => ({ title: t.name, value: t.slug }))"
                                    placeholder="Digite pra buscar..."
                                    multiple
                                    chips
                                    closable-chips
                                    clearable
                                    hide-details
                                    density="compact"
                                    bg-color="surface"
                                />
                            </div>

                            <div class="filter-group">
                                <span class="filter-label">Restrição alimentar</span>
                                <v-chip-group v-model="filters.dietary_tags" selected-class="chip-selected" column multiple>
                                    <v-chip v-for="tag in dietaryTags" :key="tag.id" :value="tag.slug" filter variant="outlined">
                                        <v-icon v-if="tag.kind === 'allergen'" icon="mdi-alert-circle-outline" size="14" start />
                                        {{ tag.name }}
                                    </v-chip>
                                </v-chip-group>
                            </div>

                            <v-row dense class="mt-1">
                                <v-col cols="6" sm="3">
                                    <v-select
                                        v-model="filters.radius_km"
                                        :items="distanceOptions"
                                        label="Distância"
                                        hide-details
                                        bg-color="surface"
                                    />
                                </v-col>
                                <v-col cols="6" sm="3">
                                    <v-select
                                        v-model="filters.min_rating"
                                        :items="ratingOptions"
                                        label="Avaliação"
                                        hide-details
                                        bg-color="surface"
                                    />
                                </v-col>
                                <v-col cols="12" sm="3">
                                    <v-chip-group v-model="filters.price_range" selected-class="chip-selected">
                                        <v-chip v-for="p in priceOptions" :key="p" :value="p" filter variant="outlined">
                                            {{ p }}
                                        </v-chip>
                                    </v-chip-group>
                                </v-col>
                                <v-col cols="12" sm="3" class="d-flex align-center justify-space-between">
                                    <v-switch
                                        v-model="filters.open_now"
                                        label="Aberto agora"
                                        color="primary"
                                        density="comfortable"
                                        hide-details
                                    />
                                    <v-btn
                                        v-if="activeFilterCount"
                                        variant="text"
                                        size="small"
                                        color="primary"
                                        @click="clearFilters"
                                    >
                                        Limpar
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </div>
                    </v-expand-transition>
                </v-container>
            </div>

            <div class="results-area">
                <div class="results-list">
                    <template v-if="hasAnyFilter">
                        <div class="results-count">
                            <strong>{{ restaurants.length }}</strong>
                            {{ restaurants.length === 1 ? 'estabelecimento encontrado' : 'estabelecimentos encontrados' }}
                        </div>

                        <v-alert v-if="restaurants.length === 0" type="info" variant="tonal" class="mx-4">
                            Nenhum estabelecimento encontrado com esses filtros. Tente ampliar a distância ou remover algum filtro.
                        </v-alert>

                        <RestaurantCard
                            v-for="restaurant in restaurants"
                            :key="restaurant.id"
                            :restaurant="restaurant"
                            :highlighted="hoveredRestaurantId === restaurant.id"
                            @mouseenter="hoveredRestaurantId = restaurant.id"
                            @mouseleave="hoveredRestaurantId = null"
                        />
                    </template>

                    <v-alert v-else type="info" variant="tonal" class="mx-4">
                        Escolha um tipo de estabelecimento acima ou busque algo pra ver os restaurantes --
                        o mapa ao lado já mostra todos, pode explorar clicando nos pinos.
                    </v-alert>
                </div>

                <div class="results-map">
                    <RestaurantMap
                        :restaurants="mapRestaurants"
                        :center="mapCenter"
                        :user-location="userLocation"
                        :highlighted-id="hoveredRestaurantId"
                    />
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.discover-shell {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 72px);
}

.search-bar {
    background: rgb(var(--v-theme-surface));
    border-bottom: 1px solid rgba(249, 115, 22, 0.28);
    padding-block: 16px;
    position: sticky;
    top: 72px;
    z-index: 5;
}

.search-field {
    flex: 1 1 320px;
    min-width: 240px;
}

.category-quickbar {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-top: 14px;
    padding-bottom: 2px;
    scrollbar-width: thin;
}

.category-quickbar__item {
    flex: 0 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 8px 14px;
    border-radius: 12px;
    border: 1px solid rgba(249, 115, 22, 0.28);
    background: transparent;
    color: inherit;
    cursor: pointer;
    transition: border-color 0.15s ease, background-color 0.15s ease;
}

.category-quickbar__item:hover {
    border-color: #f97316;
}

.category-quickbar__item--active {
    background: #f97316;
    border-color: #f97316;
    color: #2a0f02;
}

.category-quickbar__emoji {
    font-size: 22px;
    line-height: 1;
}

.category-quickbar__label {
    font-size: 0.7rem;
    font-weight: 600;
    white-space: nowrap;
}

.filters-panel {
    padding-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgba(253, 246, 240, 0.6);
}

:deep(.chip-selected) {
    background: #f97316 !important;
    color: #2a0f02 !important;
    border-color: #f97316 !important;
}

.results-area {
    flex: 1 1 auto;
    display: flex;
    min-height: 0;
}

.results-list {
    width: 42%;
    min-width: 360px;
    max-width: 560px;
    overflow-y: auto;
    padding: 16px;
    background: rgb(var(--v-theme-background));
}

.results-count {
    padding: 4px 8px 16px;
    color: rgba(253, 246, 240, 0.7);
    font-size: 0.9rem;
}

.results-map {
    flex: 1 1 auto;
    position: relative;
    overflow: hidden;
}

@media (max-width: 960px) {
    .results-area {
        flex-direction: column;
    }

    .results-list,
    .results-map {
        width: 100%;
        min-width: 0;
        max-width: none;
    }

    .results-map {
        height: 45vh;
    }

    /* Fixo (nao vh) de proposito -- em vh, uma janela alta (comum ao so encolher a
       LARGURA do navegador pra simular mobile) deixa passar bem mais que 3 cards antes
       de cortar, escondendo o mapa la' embaixo. ~150px por card (thumb + conteudo + margem). */
    .results-list {
        max-height: 460px;
    }

    .search-bar {
        position: static;
    }
}
</style>
