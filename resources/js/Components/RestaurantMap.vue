<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { emojiForRestaurant } from '@/utils/categoryIcons';

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

function restaurantIcon(restaurant, isHighlighted) {
    const color = restaurant.is_open_now ? '#22C55E' : '#8A7F7A';
    const size = isHighlighted ? 34 : 26;
    const ring = isHighlighted ? `box-shadow:0 0 0 4px ${color}33, 0 2px 8px rgba(0,0,0,.5);` : 'box-shadow:0 2px 6px rgba(0,0,0,.5);';
    const emoji = emojiForRestaurant(restaurant.categories);

    return L.divIcon({
        className: '',
        html: `
            <div style="
                background:${color};
                width:${size}px;height:${size}px;
                border-radius:50% 50% 50% 0;
                transform:rotate(-45deg);
                border:2px solid white;
                ${ring}
                display:flex;align-items:center;justify-content:center;
                transition:width .15s,height .15s;
            ">
                <span style="font-size:${size * 0.5}px;line-height:1;transform:rotate(45deg);">${emoji}</span>
            </div>
        `,
        iconSize: [size, size],
        iconAnchor: [size / 2, size],
        popupAnchor: [0, -size],
    });
}

function popupHtml(restaurant) {
    const distance = formatDistance(restaurant.distance_km);
    const cuisines = (restaurant.cuisines ?? []).map((c) => c.name).join(' · ');
    const profileUrl = route('restaurants.show', restaurant.slug);
    // Com a localizacao do usuario ja em maos (props.userLocation), passa como origin
    // explicito -- sem isso o Google Maps pede a localizacao de novo, com sua propria
    // permissao, que pode nao ter sido concedida mesmo com a do app.
    const origin = props.userLocation ? `&origin=${props.userLocation.lat},${props.userLocation.lng}` : '';
    const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${restaurant.latitude},${restaurant.longitude}${origin}`;
    const statusColor = restaurant.is_open_now ? '#22C55E' : '#A68D7C';

    return `
        <div style="min-width:240px;font-family:inherit;">
            <div style="font-weight:700;font-size:15px;margin-bottom:4px;color:#FDF6F0;">${restaurant.name}</div>
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:#E3CFC0;margin-bottom:4px;flex-wrap:wrap;">
                <span><i class="mdi mdi-star" style="color:#FBBF24;"></i> ${Number(restaurant.average_rating).toFixed(1)}</span>
                ${distance ? `<span><i class="mdi mdi-map-marker-outline"></i> ${distance}</span>` : ''}
                <span style="font-weight:700;color:#F97316;">${restaurant.price_range ?? ''}</span>
            </div>
            ${cuisines ? `<div style="font-size:12px;color:#A68D7C;margin-bottom:6px;">${cuisines}</div>` : ''}
            <div style="font-size:12px;font-weight:600;color:${statusColor};margin-bottom:10px;">
                <i class="mdi mdi-circle" style="font-size:8px;"></i>
                ${restaurant.is_open_now ? 'Aberto agora' : 'Fechado agora'}
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="${profileUrl}" style="background:#F97316;color:#2A0F02;padding:6px 10px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">Ver perfil</a>
                <a href="${profileUrl}#cardapio" style="border:1px solid rgba(249,115,22,0.35);padding:6px 10px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;color:#FDF6F0;">Cardápio</a>
                <a href="${directionsUrl}" target="_blank" rel="noopener" style="padding:6px 10px;font-size:12px;font-weight:600;text-decoration:none;color:#FDF6F0;">Como chegar</a>
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

    userMarker = L.circleMarker([props.userLocation.lat, props.userLocation.lng], {
        radius: 7,
        color: '#60A5FA',
        fillColor: '#60A5FA',
        fillOpacity: 0.9,
        weight: 2,
    }).addTo(map);
}

onMounted(() => {
    map = L.map(mapEl.value, { zoomControl: false }).setView([props.center.lat, props.center.lng], 14);

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // Dark basemap so the map reads as part of the app's dark theme instead of a bright
    // white rectangle dropped into it; same tile server family (CartoDB), just the dark variant.
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
        maxZoom: 19,
    }).addTo(map);

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
        map?.setView([center.lat, center.lng], map.getZoom());
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

.restaurant-map :deep(.leaflet-popup-content-wrapper) {
    border-radius: 10px;
    background: #1c120c;
    border: 1px solid rgba(249, 115, 22, 0.28);
}

.restaurant-map :deep(.leaflet-popup-tip) {
    background: #1c120c;
}

.restaurant-map :deep(.leaflet-control-zoom a) {
    background: #1c120c;
    color: #fdf6f0;
    border-color: rgba(249, 115, 22, 0.28) !important;
}
</style>
