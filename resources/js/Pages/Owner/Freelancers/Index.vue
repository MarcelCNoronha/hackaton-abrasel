<script setup>
import { reactive, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { availabilityBadge } from '@/utils/availabilityBadge';

const props = defineProps({
    freelancers: { type: Array, default: () => [] },
    jobSkills: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const filters = reactive({
    job_skills: props.filters.job_skills ?? [],
    availability_status: props.filters.availability_status ?? null,
});

const availabilityOptions = [
    { title: 'Qualquer disponibilidade', value: null },
    { title: 'Disponível agora', value: 'immediate' },
    { title: 'Disponibilidade combinada', value: 'scheduled' },
];

let debounceTimer = null;
watch(filters, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route('owner.freelancers.index'), { ...filters }, { preserveState: true, preserveScroll: true, replace: true });
    }, 300);
}, { deep: true });
</script>

<template>
    <Head title="Freelancers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-h5 font-weight-bold">Freelancers disponíveis</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <v-row class="mb-2">
                    <v-col cols="12" md="7">
                        <v-autocomplete
                            v-model="filters.job_skills"
                            :items="jobSkills.map((s) => ({ title: s.name, value: s.slug }))"
                            label="Habilidade"
                            multiple
                            chips
                            closable-chips
                            clearable
                            hide-details
                        />
                    </v-col>
                    <v-col cols="12" md="5">
                        <v-select
                            v-model="filters.availability_status"
                            :items="availabilityOptions"
                            label="Disponibilidade"
                            hide-details
                        />
                    </v-col>
                </v-row>

                <v-alert v-if="!freelancers.length" type="info" variant="tonal">
                    Nenhum freelancer encontrado com esses filtros.
                </v-alert>

                <v-card
                    v-for="freelancer in freelancers"
                    :key="freelancer.id"
                    variant="outlined"
                    class="mb-3"
                    :href="route('owner.freelancers.show', freelancer.id)"
                >
                    <v-card-item>
                        <template #prepend>
                            <v-avatar size="40">
                                <v-img v-if="freelancer.photo_path" :src="`/storage/${freelancer.photo_path}`" :alt="freelancer.user?.name" />
                                <v-icon v-else icon="mdi-account-circle-outline" size="40" />
                            </v-avatar>
                        </template>
                        <v-card-title class="d-flex align-center justify-space-between ga-2">
                            <span>{{ freelancer.user?.name }}</span>
                            <v-chip :color="availabilityBadge(freelancer.availability_status).color" size="small" variant="flat">
                                <v-icon :icon="availabilityBadge(freelancer.availability_status).icon" size="10" start />
                                {{ availabilityBadge(freelancer.availability_status).label }}
                            </v-chip>
                        </v-card-title>
                        <v-card-subtitle v-if="freelancer.headline">{{ freelancer.headline }}</v-card-subtitle>
                    </v-card-item>
                    <v-card-text>
                        <div class="d-flex align-center ga-1 mb-2 text-body-2">
                            <v-icon icon="mdi-star" color="accent" size="16" />
                            {{ Number(freelancer.average_rating).toFixed(1) }}
                            <span class="text-caption text-medium-emphasis">({{ freelancer.reviews_count }} avaliações)</span>
                        </div>
                        <v-chip v-for="skill in freelancer.job_skills" :key="skill.id" size="small" variant="tonal" class="mr-1 mb-1">
                            {{ skill.name }}
                        </v-chip>
                    </v-card-text>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
