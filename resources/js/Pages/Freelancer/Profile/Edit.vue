<script setup>
import { computed, reactive } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { availabilityBadge, weekdayNames } from '@/utils/availabilityBadge';

const props = defineProps({
    profile: { type: Object, required: true },
    availabilityStatuses: { type: Array, default: () => [] },
    jobSkillSuggestions: { type: Array, default: () => [] },
});

const slotsByWeekday = computed(() => {
    const map = new Map(props.profile.availability_slots?.map((s) => [s.weekday, s]) ?? []);
    return Array.from({ length: 7 }, (_, weekday) => {
        const existing = map.get(weekday);
        return {
            weekday,
            is_off: existing?.is_off ?? true,
            opens_at: existing?.opens_at?.slice(0, 5) ?? '',
            closes_at: existing?.closes_at?.slice(0, 5) ?? '',
        };
    });
});

const form = useForm({
    headline: props.profile.headline ?? '',
    bio: props.profile.bio ?? '',
    availability_status: props.profile.availability_status,
    job_skills: (props.profile.job_skills ?? []).map((tag) => tag.name),
    slots: slotsByWeekday.value,
});

const statusOptions = [
    { value: 'immediate', title: 'Verde -- disponível agora' },
    { value: 'scheduled', title: 'Amarela -- só em dias/horários específicos' },
    { value: 'unavailable', title: 'Vermelha -- indisponível no momento' },
];

const badge = computed(() => availabilityBadge(form.availability_status));

function submit() {
    form.patch(route('freelancer.profile.update'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Meu perfil de freelancer" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-h5 font-weight-bold">Meu perfil de freelancer</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <v-card>
                    <v-card-text>
                        <v-form @submit.prevent="submit">
                            <v-row>
                                <v-col cols="12">
                                    <v-text-field
                                        v-model="form.headline"
                                        label="Título profissional"
                                        hint="Ex.: Sushiman com 5 anos de experiência"
                                        persistent-hint
                                        :error-messages="form.errors.headline"
                                    />
                                </v-col>
                                <v-col cols="12">
                                    <v-textarea
                                        v-model="form.bio"
                                        label="Sobre você"
                                        rows="3"
                                        :error-messages="form.errors.bio"
                                    />
                                </v-col>
                                <v-col cols="12">
                                    <v-combobox
                                        v-model="form.job_skills"
                                        :items="jobSkillSuggestions"
                                        label="Habilidades"
                                        hint="Ex.: Sushiman, Churrasqueiro(a), Garçom -- digite e aperte Enter para criar uma nova"
                                        persistent-hint
                                        multiple
                                        chips
                                        closable-chips
                                        :error-messages="form.errors.job_skills"
                                    />
                                </v-col>

                                <v-col cols="12">
                                    <v-divider class="mb-4" />
                                    <h3 class="text-subtitle-1 font-weight-bold mb-3 d-flex align-center ga-2">
                                        Disponibilidade
                                        <v-chip :color="badge.color" size="small" variant="flat">
                                            <v-icon :icon="badge.icon" size="10" start />
                                            {{ badge.label }}
                                        </v-chip>
                                    </h3>
                                    <v-radio-group v-model="form.availability_status" :error-messages="form.errors.availability_status" hide-details class="mb-2">
                                        <v-radio v-for="option in statusOptions" :key="option.value" :value="option.value" :label="option.title" />
                                    </v-radio-group>
                                </v-col>

                                <v-col v-if="form.availability_status === 'scheduled'" cols="12">
                                    <p class="text-body-2 text-medium-emphasis mb-2">Marque os dias e horários em que você está disponível:</p>
                                    <div v-for="(day, index) in form.slots" :key="day.weekday" class="d-flex flex-wrap align-center ga-3 mb-2">
                                        <span style="min-width: 90px" class="text-body-2 font-weight-medium">{{ weekdayNames[day.weekday] }}</span>
                                        <v-switch v-model="day.is_off" color="primary" density="compact" hide-details label="Indisponível" />
                                        <template v-if="!day.is_off">
                                            <v-text-field
                                                v-model="form.slots[index].opens_at"
                                                type="time" label="A partir de" density="compact" hide-details
                                                style="max-width: 140px; flex: 1 1 120px"
                                            />
                                            <v-text-field
                                                v-model="form.slots[index].closes_at"
                                                type="time" label="Até" density="compact" hide-details
                                                style="max-width: 140px; flex: 1 1 120px"
                                            />
                                        </template>
                                    </div>
                                </v-col>
                            </v-row>

                            <v-btn type="submit" color="primary" variant="flat" class="mt-4" :loading="form.processing">
                                Salvar perfil
                            </v-btn>
                        </v-form>
                    </v-card-text>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
