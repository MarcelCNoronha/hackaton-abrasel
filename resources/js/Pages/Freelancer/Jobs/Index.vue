<script setup>
import { reactive, ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    jobPostings: { type: Array, default: () => [] },
    jobSkills: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const filters = reactive({ job_skills: props.filters.job_skills ?? [] });

let debounceTimer = null;
watch(filters, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route('freelancer.jobs.index'), { ...filters }, { preserveState: true, preserveScroll: true, replace: true });
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

const statusLabels = {
    pending: { text: 'Candidatura enviada', color: 'warning' },
    accepted: { text: 'Você foi contratado(a)!', color: 'secondary' },
    declined: { text: 'Não foi dessa vez', color: undefined },
    withdrawn: { text: 'Candidatura retirada', color: undefined },
};
</script>

<template>
    <Head title="Vagas abertas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-h5 font-weight-bold">Vagas abertas</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <v-autocomplete
                    v-model="filters.job_skills"
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
                            <v-chip size="small" :color="statusLabels[posting.my_application_status]?.color" variant="tonal">
                                {{ statusLabels[posting.my_application_status]?.text ?? posting.my_application_status }}
                            </v-chip>
                        </template>
                        <v-btn v-else color="primary" variant="flat" @click="openApplyDialog(posting)">Candidatar-se</v-btn>
                    </v-card-actions>
                </v-card>
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
