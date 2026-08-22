<script setup>
import { computed } from 'vue';

const props = defineProps({
    byHour: { type: Array, default: () => [] },
    peakHour: { type: Object, default: null },
    quietHour: { type: Object, default: null },
    simplified: { type: Boolean, default: false },
});

const LEVELS = {
    sem_dados: { color: '#3a3a3a', label: 'Sem dados' },
    baixo: { color: 'rgb(var(--v-theme-secondary))', label: 'Baixo movimento' },
    medio: { color: 'rgb(var(--v-theme-warning))', label: 'Movimento médio' },
    alto: { color: 'rgb(var(--v-theme-error))', label: 'Alto movimento' },
};

const BAR_HEIGHT = { sem_dados: 10, baixo: 35, medio: 65, alto: 100 };

const hours = computed(() => props.byHour.length === 24 ? props.byHour : []);

function formatHour(hour) {
    return `${String(hour).padStart(2, '0')}h`;
}

function levelInfo(level) {
    return LEVELS[level] ?? LEVELS.sem_dados;
}
</script>

<template>
    <div>
        <div class="d-flex align-end ga-1" style="height: 72px">
            <div
                v-for="slot in hours"
                :key="slot.hour"
                class="d-flex flex-column justify-end align-center flex-grow-1"
                style="height: 100%; min-width: 0"
            >
                <div
                    class="traffic-bar"
                    :style="{ height: `${BAR_HEIGHT[slot.level] ?? 10}%`, background: levelInfo(slot.level).color }"
                    :title="!simplified && slot.count !== undefined ? `${formatHour(slot.hour)}: ${slot.count} interações (${levelInfo(slot.level).label.toLowerCase()})` : `${formatHour(slot.hour)}: ${levelInfo(slot.level).label.toLowerCase()}`"
                />
            </div>
        </div>
        <div class="d-flex justify-space-between text-caption text-medium-emphasis mt-1">
            <span>00h</span>
            <span>06h</span>
            <span>12h</span>
            <span>18h</span>
            <span>23h</span>
        </div>

        <div class="d-flex flex-wrap ga-4 mt-3">
            <p v-if="peakHour" class="text-body-2 mb-0">
                <v-icon icon="mdi-trending-up" color="error" size="16" />
                Pico estimado: <strong>{{ formatHour(peakHour.hour) }}</strong>
                <span v-if="peakHour.estimated_wait" class="text-medium-emphasis"> · espera estimada {{ peakHour.estimated_wait }}</span>
            </p>
            <p v-if="quietHour" class="text-body-2 mb-0">
                <v-icon icon="mdi-trending-down" color="secondary" size="16" />
                Menor movimento: <strong>{{ formatHour(quietHour.hour) }}</strong>
                <span v-if="quietHour.estimated_wait" class="text-medium-emphasis"> · espera estimada {{ quietHour.estimated_wait }}</span>
            </p>
            <v-alert v-if="!peakHour && !quietHour" type="info" variant="tonal" density="compact" class="mt-1">
                Ainda não há dados suficientes pra estimar horários de movimento.
            </v-alert>
        </div>

        <p class="text-caption text-medium-emphasis mt-2 mb-0">
            Estimativa baseada em interações reais (visualizações, contatos, check-ins) -- não é uma medição de fila.
        </p>
    </div>
</template>

<style scoped>
.traffic-bar {
    width: 100%;
    max-width: 14px;
    border-radius: 3px 3px 1px 1px;
    transition: height 0.2s ease;
}
</style>
