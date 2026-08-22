<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { emojiForRestaurant } from '@/utils/categoryIcons';

// Leaflet toca `window` so' de importar (nao so ao instanciar) -- import estatico quebraria
// o render em SSR (sem `window` no Node). Carregado dinamicamente dentro de onMounted, que
// nunca roda no servidor, entao isso nunca executa la'.
let L = null;

const props = defineProps({
    restaurants: {
        type: Array,
        default: () => [],
    },
    center: {
        type: Object,
        default: () => ({ lat: -20.7546, lng: -42.8825 }), // Viçosa/MG
    },
    userLocation: {
        type: Object,
        default: null,
    },
    highlightedId: {
        type: [Number, String],
        default: null,
    },
});

const emit = defineEmits(['select']);

const mapEl = ref(null);
let map = null;
let markersLayer = null;
let userMarker = null;
const markersById = new Map();

function formatDistance(distanceKm) {
    if (distanceKm === undefined || distanceKm === null) {
        return null;
    }

    const km = Number(distanceKm);

    return km < 1 ? `${Math.round(km * 1000)} metros` : `${km.toFixed(1)} km`;
}

function escapeHtml(text) {
    return String(text).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
}

// Badge redondo + nome sempre visivel do lado, no estilo dos POIs do Google Maps -- ao inves
// da gota clicavel-so-no-popup de antes. iconAnchor fica no CENTRO do badge (nao na ponta
// inferior, como seria numa gota), ja que e' um circulo, nao um pino apontando pra baixo.
// Cores lidas das custom properties que o Vuetify ja expoe no tema ativo (vicosaFoodDark,
// ver plugins/vuetify.js) em vez de hex fixo -- o marcador/popup e' HTML cru injetado pelo
// Leaflet, mas ainda vive dentro da mesma arvore de DOM onde essas variaveis estao
// definidas, entao `rgb(var(--v-theme-x))` resolve normalmente.
const THEME = {
    secondary: 'rgb(var(--v-theme-secondary))',
    onSurfaceMuted: 'rgba(var(--v-theme-on-surface), 0.55)',
    surface: 'rgba(var(--v-theme-surface), 0.92)',
    onSurface: 'rgb(var(--v-theme-on-surface))',
};

function restaurantIcon(restaurant, isHighlighted) {
    const statusVar = restaurant.is_open_now ? '--v-theme-secondary' : '--v-theme-on-surface';
    // Fechado usa on-surface BEM esmaecido (0.55) -- em opacidade cheia essa variavel e' um
    // off-white quase branco (cor de texto), ruim como preenchimento solido do badge.
    const color = restaurant.is_open_now ? `rgb(var(${statusVar}))` : `rgba(var(${statusVar}), 0.55)`;
    const size = isHighlighted ? 34 : 28;
    const ring = isHighlighted
        ? `box-shadow:0 0 0 4px rgba(var(${statusVar}), 0.25), 0 2px 8px rgba(0,0,0,.5);`
        : 'box-shadow:0 2px 6px rgba(0,0,0,.5);';
    const emoji = emojiForRestaurant(restaurant.categories);
    const name = escapeHtml(restaurant.name);

    return L.divIcon({
        className: '',
        html: `
            <div style="position:relative;width:${size}px;height:${size}px;">
                <div style="
                    background:${color};
                    width:${size}px;height:${size}px;
                    border-radius:50%;
                    border:2px solid white;
                    ${ring}
                    display:flex;align-items:center;justify-content:center;
                ">
                    <span style="font-size:${size * 0.55}px;line-height:1;">${emoji}</span>
                </div>
                <div style="
                    position:absolute;left:${size + 6}px;top:50%;transform:translateY(-50%);
                    background:${THEME.surface};color:${THEME.onSurface};
                    padding:2px 8px;border-radius:6px;font-size:12px;font-weight:600;
                    max-width:140px;overflow:hidden;text-overflow:ellipsis;
                    white-space:nowrap;box-shadow:0 1px 4px rgba(0,0,0,.4);
                    cursor:pointer;
                ">${name}</div>
            </div>
        `,
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
        popupAnchor: [0, -size / 2],
    });
}

function popupHtml(restaurant) {
    const distance = formatDistance(restaurant.distance_km);
    // escapeHtml -- nome do restaurante e das cozinhas sao texto livre (editavel pelo gestor),
    // interpolados aqui direto num innerHTML; sem isso e' XSS armazenado pra quem clicar no pino.
    const name = escapeHtml(restaurant.name);
    const cuisines = escapeHtml((restaurant.cuisines ?? []).map((c) => c.name).join(' · '));
    const profileUrl = route('restaurants.show', restaurant.slug);
    // Com a localizacao do usuario ja em maos (props.userLocation), passa como origin
    // explicito -- sem isso o Google Maps pede a localizacao de novo, com sua propria
    // permissao, que pode nao ter sido concedida mesmo com a do app.
    const origin = props.userLocation ? `&origin=${props.userLocation.lat},${props.userLocation.lng}` : '';
    const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${restaurant.latitude},${restaurant.longitude}${origin}`;
    const statusColor = restaurant.is_open_now ? THEME.secondary : THEME.onSurfaceMuted;

    return `
        <div style="min-width:260px;font-family:inherit;">
            <div style="font-weight:700;font-size:15px;margin-bottom:4px;color:${THEME.onSurface};">${name}</div>
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(var(--v-theme-on-surface),0.8);margin-bottom:4px;flex-wrap:wrap;">
                <span><i class="mdi mdi-star" style="color:rgb(var(--v-theme-accent));"></i> ${Number(restaurant.average_rating).toFixed(1)}</span>
                ${distance ? `<span><i class="mdi mdi-map-marker-outline"></i> ${distance}</span>` : ''}
                <span style="font-weight:700;color:rgb(var(--v-theme-primary));">${restaurant.price_range ?? ''}</span>
            </div>
            ${cuisines ? `<div style="font-size:12px;color:${THEME.onSurfaceMuted};margin-bottom:6px;">${cuisines}</div>` : ''}
            <div style="font-size:12px;font-weight:600;color:${statusColor};margin-bottom:10px;">
                <i class="mdi mdi-circle" style="font-size:8px;"></i>
                ${restaurant.is_open_now ? 'Aberto agora' : 'Fechado agora'}
            </div>
            <a href="${profileUrl}" style="display:block;text-align:center;background:rgb(var(--v-theme-primary));color:rgb(var(--v-theme-on-primary));padding:8px 10px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none;margin-bottom:6px;transition:filter .15s;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">
                Ver perfil
            </a>
            <div style="display:flex;gap:6px;">
                <a href="${profileUrl}#cardapio" style="flex:1;display:flex;align-items:center;justify-content:center;gap:5px;border:1px solid rgba(var(--v-theme-primary),0.4);padding:7px 8px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;color:${THEME.onSurface};transition:border-color .15s,background-color .15s;" onmouseover="this.style.borderColor='rgb(var(--v-theme-primary))';this.style.backgroundColor='rgba(var(--v-theme-primary),.1)'" onmouseout="this.style.borderColor='rgba(var(--v-theme-primary),.4)';this.style.backgroundColor='transparent'">
                    <i class="mdi mdi-book-open-variant" style="font-size:14px;"></i> Cardápio
                </a>
                <a href="${directionsUrl}" target="_blank" rel="noopener" style="flex:1;display:flex;align-items:center;justify-content:center;gap:5px;border:1px solid rgba(var(--v-theme-primary),0.4);padding:7px 8px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;color:${THEME.onSurface};transition:border-color .15s,background-color .15s;" onmouseover="this.style.borderColor='rgb(var(--v-theme-primary))';this.style.backgroundColor='rgba(var(--v-theme-primary),.1)'" onmouseout="this.style.borderColor='rgba(var(--v-theme-primary),.4)';this.style.backgroundColor='transparent'">
                    <i class="mdi mdi-directions" style="font-size:14px;"></i> Como chegar
                </a>
            </div>
        </div>
    `;
}

function renderMarkers() {
    if (!map) return;

    markersLayer.clearLayers();
    markersById.clear();

    props.restaurants.forEach((restaurant) => {
        if (restaurant.latitude == null || restaurant.longitude == null) return;

        const marker = L.marker([restaurant.latitude, restaurant.longitude], {
            icon: restaurantIcon(restaurant, restaurant.id === props.highlightedId),
        });

        marker.bindPopup(popupHtml(restaurant));
        marker.on('click', () => {
            map.flyTo([restaurant.latitude, restaurant.longitude], Math.max(map.getZoom(), 17), { duration: 0.5 });
            emit('select', restaurant);
        });
        marker.addTo(markersLayer);
        markersById.set(restaurant.id, { marker, restaurant });
    });

    // Sem isso, o mapa fica preso em `center` (localizacao bruta do navegador, que pode
    // cair longe de qualquer resultado) e a lista de restaurantes nunca aparece na tela --
    // lia como "mapa quebrado" quando na verdade so estava olhando pro lugar errado.
    if (markersById.size > 0) {
        map.fitBounds(markersLayer.getBounds(), { padding: [40, 40], maxZoom: 15 });
    }
}

function updateHighlight() {
    markersById.forEach(({ marker, restaurant }, id) => {
        marker.setIcon(restaurantIcon(restaurant, id === props.highlightedId));
        if (id === props.highlightedId) {
            marker.setZIndexOffset(1000);
        } else {
            marker.setZIndexOffset(0);
        }
    });
}

function renderUserMarker() {
    if (!map) return;

    if (userMarker) {
        userMarker.remove();
        userMarker = null;
    }

    if (!props.userLocation) return;

    const infoColor = getComputedStyle(mapEl.value).getPropertyValue('--v-theme-info').trim();
    userMarker = L.circleMarker([props.userLocation.lat, props.userLocation.lng], {
        radius: 7,
        color: `rgb(${infoColor})`,
        fillColor: `rgb(${infoColor})`,
        fillOpacity: 0.9,
        weight: 2,
    }).addTo(map);
}

onMounted(async () => {
    L = (await import('leaflet')).default;
    await import('leaflet/dist/leaflet.css');

    map = L.map(mapEl.value, { zoomControl: false }).setView([props.center.lat, props.center.lng], 14);

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // Voyager (CartoDB): ruas, nomes e POIs legiveis como no Google Maps, sem precisar de
    // API key/faturamento -- a variante "dark_all" ficava praticamente preta em zoom de
    // bairro, com pouco detalhe pra uma cidade pequena como Vicosa.
    const streets = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
        maxZoom: 19,
    }).addTo(map);

    // Satelite via Esri World Imagery -- tambem gratuito e sem API key, mesmo padrao usado
    // por incontaveis projetos open-source pra essa camada.
    const satellite = L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        { attribution: 'Tiles &copy; Esri', maxZoom: 19 },
    );

    L.control.layers({ Mapa: streets, Satélite: satellite }, {}, { position: 'bottomright' }).addTo(map);

    markersLayer = L.layerGroup().addTo(map);

    renderMarkers();
    renderUserMarker();
});

onBeforeUnmount(() => {
    map?.remove();
    map = null;
});

watch(() => props.restaurants, renderMarkers, { deep: true });
watch(() => props.userLocation, renderUserMarker, { deep: true });
watch(() => props.highlightedId, updateHighlight);
watch(
    () => props.center,
    (center) => {
        // So reposiciona por `center` (ex.: geolocalizacao resolvida de forma assincrona,
        // depois da 1a renderizacao) quando nao ha resultado nenhum pra enquadrar -- do
        // contrario isso desfaz o fitBounds de renderMarkers() e volta pro bug de "mapa
        // vazio" que esse enquadramento existe pra evitar.
        if (markersById.size === 0) {
            map?.setView([center.lat, center.lng], map.getZoom());
        }
    },
);
</script>

<template>
    <div ref="mapEl" class="restaurant-map" />
</template>

<style scoped>
.restaurant-map {
    width: 100%;
    height: 100%;
    min-height: 400px;
}

/* Abaixo de 960px o pai (.results-map no Discover) reserva so' 45vh pro mapa -- em telas
   baixas isso fica abaixo de 400px, e o min-height acima vencia e empurrava o mapa por
   cima da lista de resultados. */
@media (max-width: 960px) {
    .restaurant-map {
        min-height: 260px;
    }
}

.restaurant-map :deep(.leaflet-popup-content-wrapper) {
    border-radius: 10px;
    background: rgb(var(--v-theme-surface));
    border: 1px solid rgba(var(--v-theme-primary), 0.28);
}

.restaurant-map :deep(.leaflet-popup-tip) {
    background: rgb(var(--v-theme-surface));
}

.restaurant-map :deep(.leaflet-control-zoom a) {
    background: rgb(var(--v-theme-surface));
    color: rgb(var(--v-theme-on-surface));
    border-color: rgba(var(--v-theme-primary), 0.28) !important;
}

.restaurant-map :deep(.leaflet-control-layers) {
    background: rgb(var(--v-theme-surface));
    color: rgb(var(--v-theme-on-surface));
    border: 1px solid rgba(var(--v-theme-primary), 0.28);
}

.restaurant-map :deep(.leaflet-control-layers-toggle) {
    filter: invert(1);
}
</style>
