<script setup>
import { computed, reactive, ref, watch, watchEffect } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PhotoInputField from '@/Components/PhotoInputField.vue';
import { availabilityBadge, weekdayNames } from '@/utils/availabilityBadge';

const props = defineProps({
    initialTab: { type: String, default: 'profile' },
    profile: { type: Object, required: true },
    availabilityStatuses: { type: Array, default: () => [] },
    jobSkillSuggestions: { type: Array, default: () => [] },
    jobPostings: { type: Array, default: () => [] },
    jobSkills: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    hireRequests: { type: Array, default: () => [] },
});

const tab = ref(props.initialTab);

/* ----------------------------- Perfil ----------------------------- */

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

const profileForm = useForm({
    headline: props.profile.headline ?? '',
    bio: props.profile.bio ?? '',
    photo: null,
    availability_status: props.profile.availability_status,
    job_skills: (props.profile.job_skills ?? []).map((skill) => skill.name),
    slots: slotsByWeekday.value,
});

const statusOptions = [
    { value: 'immediate', title: 'Disponível agora' },
    { value: 'scheduled', title: 'Dias/horários específicos' },
    { value: 'unavailable', title: 'Indisponível no momento' },
].map((option) => ({ ...option, ...availabilityBadge(option.value) }));

function submitProfile() {
    profileForm.patch(route('freelancer.profile.update'), { preserveScroll: true });
}

/* ------------------------------ Vagas ------------------------------ */

const jobFilters = reactive({ job_skills: props.filters.job_skills ?? [] });

let debounceTimer = null;
watch(jobFilters, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route('freelancer.jobs.index'), { ...jobFilters }, { preserveState: true, preserveScroll: true, replace: true });
    }, 300);
}, { deep: true });

const applyDialogFor = ref(null);
const applyForm = useForm({ message: '' });

function openApplyDialog(posting) {
    applyDialogFor.value = posting.id;
    applyForm.reset();
}

function submitApplication() {
    applyForm.post(route('freelancer.job-applications.store', applyDialogFor.value), {
        preserveScroll: true,
        onSuccess: () => {
            applyDialogFor.value = null;
        },
    });
}

function withdraw(posting) {
    if (!confirm('Retirar sua candidatura pra esta vaga?')) return;
    router.delete(route('freelancer.job-applications.destroy', posting.my_application_id), { preserveScroll: true });
}

const applicationStatusLabels = {
    pending: { text: 'Candidatura enviada', color: 'warning' },
    accepted: { text: 'Você foi contratado(a)!', color: 'secondary' },
    declined: { text: 'Não foi dessa vez', color: undefined },
    withdrawn: { text: 'Candidatura retirada', color: undefined },
};

/* ----------------------------- Pedidos ----------------------------- */

const pending = computed(() => props.hireRequests.filter((h) => h.status === 'pending'));
const awaitingReviewApproval = computed(() =>
    props.hireRequests.filter((h) => h.review?.status === 'pending_approval'),
);
const history = computed(() =>
    props.hireRequests.filter((h) => h.status !== 'pending' && h.review?.status !== 'pending_approval'),
);

watchEffect(() => {
    if (tab.value === 'approval' && !awaitingReviewApproval.value.length) {
        tab.value = 'hires';
    }
});

const hireStatusLabels = {
    accepted: { text: 'Aceito', color: 'secondary' },
    declined: { text: 'Recusado', color: undefined },
    cancelled: { text: 'Cancelado pelo restaurante', color: undefined },
};

const reviewStatusLabels = {
    approved: { text: 'Vínculo confirmado', color: 'secondary' },
    rejected: { text: 'Vínculo contestado', color: 'error' },
};

function acceptHire(hireRequest) {
    router.patch(route('freelancer.hires.accept', hireRequest.id), {}, { preserveScroll: true });
}

function declineHire(hireRequest) {
    if (!confirm('Recusar este pedido de contratação?')) return;
    router.patch(route('freelancer.hires.decline', hireRequest.id), {}, { preserveScroll: true });
}

function approveReview(review) {
    router.patch(route('freelancer.reviews.approve', review.id), {}, { preserveScroll: true });
}

function rejectReview(review) {
    if (!confirm('Contestar este vínculo? A avaliação não ficará visível pra outros donos.')) return;
    router.patch(route('freelancer.reviews.reject', review.id), {}, { preserveScroll: true });
}

function formatDate(value) {
    return new Date(value).toLocaleDateString('pt-BR');
}
</script>

<template>
    <Head title="Meu espaço freelancer" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-h5 font-weight-bold">Meu espaço freelancer</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <v-tabs v-model="tab" class="mb-4">
                    <v-tab value="profile">Meu perfil</v-tab>
                    <v-tab value="jobs">Vagas</v-tab>
                    <v-tab value="hires">
                        Pedidos
                        <v-chip v-if="pending.length" size="x-small" color="warning" variant="flat" class="ml-2">{{ pending.length }}</v-chip>
                    </v-tab>
                    <v-tab v-if="awaitingReviewApproval.length" value="approval">
                        Confirme o vínculo
                        <v-chip size="x-small" color="primary" variant="flat" class="ml-2">{{ awaitingReviewApproval.length }}</v-chip>
                    </v-tab>
                </v-tabs>

                <v-window v-model="tab">
                    <!-- Perfil -->
                    <v-window-item value="profile">
                        <v-card>
                            <v-card-text>
                                <v-form @submit.prevent="submitProfile">
                                    <v-row>
                                        <v-col cols="12">
                                            <v-text-field
                                                v-model="profileForm.headline"
                                                label="Título profissional"
                                                hint="Ex.: Sushiman com 5 anos de experiência"
                                                persistent-hint
                                                :error-messages="profileForm.errors.headline"
                                            />
                                        </v-col>
                                        <v-col cols="12">
                                            <v-textarea
                                                v-model="profileForm.bio"
                                                label="Sobre você"
                                                rows="3"
                                                :error-messages="profileForm.errors.bio"
                                            />
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <PhotoInputField
                                                v-model="profileForm.photo"
                                                label="Foto de perfil (opcional)"
                                                icon="mdi-account-circle-outline"
                                                :existing-path="profile.photo_path"
                                                :error-messages="profileForm.errors.photo"
                                                :preview-width="90"
                                                :preview-height="90"
                                            />
                                        </v-col>
                                        <v-col cols="12">
                                            <v-combobox
                                                v-model="profileForm.job_skills"
                                                :items="jobSkillSuggestions"
                                                label="Habilidades"
                                                hint="Ex.: Sushiman, Churrasqueiro(a), Garçom -- digite e aperte Enter para criar uma nova"
                                                persistent-hint
                                                multiple
                                                chips
                                                closable-chips
                                                :error-messages="profileForm.errors.job_skills"
                                            />
                                        </v-col>

                                        <v-col cols="12">
                                            <v-divider class="mb-4" />
                                            <v-select
                                                v-model="profileForm.availability_status"
                                                :items="statusOptions"
                                                item-title="title"
                                                item-value="value"
                                                label="Disponibilidade"
                                                :error-messages="profileForm.errors.availability_status"
                                                hide-details
                                                class="mb-2"
                                            >
                                                <template #item="{ props: itemProps, item }">
                                                    <v-list-item v-bind="itemProps" title="">
                                                        <template #prepend>
                                                            <v-icon :icon="item.raw.icon" :color="item.raw.color" size="12" />
                                                        </template>
                                                        <v-list-item-title>{{ item.raw.title }}</v-list-item-title>
                                                    </v-list-item>
                                                </template>
                                                <template #selection="{ item }">
                                                    <v-chip :color="item.raw.color" size="small" variant="flat">
                                                        <v-icon :icon="item.raw.icon" size="10" start />
                                                        {{ item.raw.label }}
                                                    </v-chip>
                                                </template>
                                            </v-select>
                                        </v-col>

                                        <v-col v-if="profileForm.availability_status === 'scheduled'" cols="12">
                                            <p class="text-body-2 text-medium-emphasis mb-2">Marque os dias e horários em que você está disponível:</p>
                                            <div v-for="(day, index) in profileForm.slots" :key="day.weekday" class="d-flex flex-wrap align-center ga-3 mb-2">
                                                <span style="min-width: 90px" class="text-body-2 font-weight-medium">{{ weekdayNames[day.weekday] }}</span>
                                                <v-switch v-model="day.is_off" color="primary" density="compact" hide-details label="Indisponível" />
                                                <template v-if="!day.is_off">
                                                    <v-text-field
                                                        v-model="profileForm.slots[index].opens_at"
                                                        type="time" label="A partir de" density="compact" hide-details
                                                        style="max-width: 140px; flex: 1 1 120px"
                                                    />
                                                    <v-text-field
                                                        v-model="profileForm.slots[index].closes_at"
                                                        type="time" label="Até" density="compact" hide-details
                                                        style="max-width: 140px; flex: 1 1 120px"
                                                    />
                                                </template>
                                            </div>
                                        </v-col>
                                    </v-row>

                                    <v-btn type="submit" color="primary" variant="flat" class="mt-4" :loading="profileForm.processing">
                                        Salvar perfil
                                    </v-btn>
                                </v-form>
                            </v-card-text>
                        </v-card>
                    </v-window-item>

                    <!-- Vagas -->
                    <v-window-item value="jobs">
                        <v-autocomplete
                            v-model="jobFilters.job_skills"
                            :items="jobSkills.map((s) => ({ title: s.name, value: s.slug }))"
                            label="Filtrar por habilidade"
                            multiple
                            chips
                            closable-chips
                            clearable
                            class="mb-4"
                            hide-details
                        />

                        <v-alert v-if="!jobPostings.length" type="info" variant="tonal">
                            Nenhuma vaga aberta com esse filtro no momento.
                        </v-alert>

                        <v-card v-for="posting in jobPostings" :key="posting.id" variant="outlined" class="mb-3">
                            <v-card-item>
                                <v-card-title>{{ posting.title }}</v-card-title>
                                <v-card-subtitle>{{ posting.restaurant?.name }} · {{ posting.restaurant?.address_neighborhood }}</v-card-subtitle>
                            </v-card-item>
                            <v-card-text>
                                <p v-if="posting.description" class="mb-2">{{ posting.description }}</p>
                                <v-chip v-for="skill in posting.job_skills" :key="skill.id" size="small" variant="tonal" class="mr-1">
                                    {{ skill.name }}
                                </v-chip>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer />
                                <template v-if="posting.my_application_status">
                                    <v-btn v-if="posting.my_application_status === 'pending'" variant="text" color="error" @click="withdraw(posting)">
                                        Retirar
                                    </v-btn>
                                    <v-chip size="small" :color="applicationStatusLabels[posting.my_application_status]?.color" variant="tonal">
                                        {{ applicationStatusLabels[posting.my_application_status]?.text ?? posting.my_application_status }}
                                    </v-chip>
                                </template>
                                <v-btn v-else color="primary" variant="flat" @click="openApplyDialog(posting)">Candidatar-se</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-window-item>

                    <!-- Pedidos -->
                    <v-window-item value="hires">
                        <v-alert v-if="!pending.length" type="info" variant="tonal" class="mb-6">
                            Nenhum pedido pendente no momento.
                        </v-alert>
                        <template v-else>
                            <h3 class="text-subtitle-1 font-weight-bold mb-3">Pendentes</h3>
                            <v-card v-for="hireRequest in pending" :key="hireRequest.id" variant="outlined" class="mb-3">
                                <v-card-item>
                                    <v-card-title>{{ hireRequest.restaurant?.name }}</v-card-title>
                                    <v-card-subtitle>{{ formatDate(hireRequest.created_at) }}</v-card-subtitle>
                                </v-card-item>
                                <v-card-text v-if="hireRequest.message">{{ hireRequest.message }}</v-card-text>
                                <v-card-actions>
                                    <v-spacer />
                                    <v-btn variant="outlined" @click="declineHire(hireRequest)">Recusar</v-btn>
                                    <v-btn color="primary" variant="flat" @click="acceptHire(hireRequest)">Aceitar</v-btn>
                                </v-card-actions>
                            </v-card>
                        </template>

                        <h3 class="text-subtitle-1 font-weight-bold mb-3 mt-6">Histórico e avaliações recebidas</h3>
                        <v-alert v-if="!history.length" type="info" variant="tonal">
                            Nenhum histórico ainda.
                        </v-alert>
                        <v-card v-for="hireRequest in history" :key="hireRequest.id" variant="outlined" class="mb-3">
                            <v-card-item>
                                <v-card-title class="d-flex align-center justify-space-between ga-2">
                                    {{ hireRequest.restaurant?.name }}
                                    <v-chip size="small" :color="hireStatusLabels[hireRequest.status]?.color" variant="tonal">
                                        {{ hireStatusLabels[hireRequest.status]?.text ?? hireRequest.status }}
                                    </v-chip>
                                </v-card-title>
                                <v-card-subtitle>{{ formatDate(hireRequest.created_at) }}</v-card-subtitle>
                            </v-card-item>
                            <v-card-text v-if="hireRequest.review">
                                <div class="d-flex align-center ga-2 mb-1">
                                    <v-icon icon="mdi-star" color="accent" size="16" />
                                    <strong>{{ hireRequest.review.rating }}/5</strong>
                                    <v-chip size="x-small" :color="reviewStatusLabels[hireRequest.review.status]?.color" variant="tonal">
                                        {{ reviewStatusLabels[hireRequest.review.status]?.text ?? hireRequest.review.status }}
                                    </v-chip>
                                </div>
                                <p v-if="hireRequest.review.feedback_to_freelancer" class="mb-0">
                                    "{{ hireRequest.review.feedback_to_freelancer }}"
                                </p>
                                <p v-else class="text-medium-emphasis mb-0">O restaurante não deixou um comentário pra você.</p>
                            </v-card-text>
                        </v-card>
                    </v-window-item>

                    <!-- Confirme o vínculo -->
                    <v-window-item value="approval">
                        <v-card v-for="hireRequest in awaitingReviewApproval" :key="hireRequest.review.id" variant="outlined" class="mb-3">
                            <v-card-item>
                                <v-card-title>{{ hireRequest.restaurant?.name }}</v-card-title>
                                <v-card-subtitle>Diz que trabalhou lá e quer deixar uma referência sobre você</v-card-subtitle>
                            </v-card-item>
                            <v-card-text>
                                <div class="d-flex align-center ga-1 mb-1">
                                    <v-icon icon="mdi-star" color="accent" size="16" />
                                    <strong>{{ hireRequest.review.rating }}/5</strong>
                                </div>
                                <p v-if="hireRequest.review.feedback_to_freelancer" class="mb-0">
                                    "{{ hireRequest.review.feedback_to_freelancer }}"
                                </p>
                                <p class="text-caption text-medium-emphasis mt-2 mb-0">
                                    Confirmar não muda a nota nem o comentário -- só atesta que você realmente trabalhou lá.
                                    A referência pra outros donos só fica visível depois da sua confirmação.
                                </p>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer />
                                <v-btn variant="outlined" color="error" @click="rejectReview(hireRequest.review)">Contestar</v-btn>
                                <v-btn color="primary" variant="flat" @click="approveReview(hireRequest.review)">Confirmar vínculo</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-window-item>
                </v-window>
            </div>
        </div>

        <v-dialog :model-value="applyDialogFor !== null" max-width="440" @update:model-value="applyDialogFor = null">
            <v-card>
                <v-card-title>Candidatar-se</v-card-title>
                <v-card-text>
                    <v-form @submit.prevent="submitApplication">
                        <v-textarea
                            v-model="applyForm.message"
                            label="Mensagem (opcional)"
                            hint="Conte rapidamente por que você é uma boa escolha"
                            persistent-hint
                            rows="3"
                            :error-messages="applyForm.errors.message"
                        />
                        <v-btn type="submit" color="primary" variant="flat" class="mt-3" :loading="applyForm.processing">
                            Enviar candidatura
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </AuthenticatedLayout>
</template>
