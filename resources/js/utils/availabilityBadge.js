const BADGES = {
    immediate: { color: 'secondary', label: 'Disponível agora', icon: 'mdi-circle' },
    scheduled: { color: 'warning', label: 'Disponibilidade combinada', icon: 'mdi-clock-outline' },
    unavailable: { color: 'error', label: 'Indisponível', icon: 'mdi-circle-off-outline' },
};

export function availabilityBadge(status) {
    return BADGES[status] ?? BADGES.unavailable;
}

export const weekdayNames = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
