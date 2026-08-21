<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    stats: { type: Object, required: true },
    pendingClaims: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    restaurants: { type: Array, default: () => [] },
});

const roleOptions = [
    { title: 'Consumidor', value: 'consumer' },
    { title: 'Gestor', value: 'owner' },
    { title: 'Administrador', value: 'admin' },
];

// --- Reivindicações ---
function approveClaim(claim) {
    router.patch(route('admin.claims.approve', claim.id), {}, { preserveScroll: true });
}

const rejectingClaim = ref(null);
const rejectForm = useForm({ rejection_reason: '' });

function openReject(claim) {
    rejectingClaim.value = claim;
    rejectForm.reset();
}

function submitReject() {
    rejectForm.patch(route('admin.claims.reject', rejectingClaim.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            rejectingClaim.value = null;
        },
    });
}

// --- Usuários ---
function updateRole(user, role) {
    router.patch(route('admin.users.update-role', user.id), { role }, { preserveScroll: true });
}

// --- Restaurantes ---
function toggleRestaurant(restaurant) {
    router.patch(route('admin.restaurants.toggle-active', restaurant.slug), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Administração" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Administração
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <v-row class="mb-2">
                    <v-col cols="12" sm="4">
                        <v-card title="Usuários" :subtitle="String(stats.users)" />
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-card title="Estabelecimentos" :subtitle="String(stats.restaurants)" />
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-card title="Reivindicações pendentes" :subtitle="String(stats.pendingClaims)" />
                    </v-col>
                </v-row>

                <v-card class="mt-6">
                    <v-card-title>Reivindicações pendentes</v-card-title>
                    <v-card-text>
                        <v-alert v-if="pendingClaims.length === 0" type="info" variant="tonal">
                            Nenhuma reivindicação pendente.
                        </v-alert>
                        <v-table v-else>
                            <thead>
                                <tr>
                                    <th>Estabelecimento</th>
                                    <th>Solicitante</th>
                                    <th>Observações</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="claim in pendingClaims" :key="claim.id">
                                    <td>{{ claim.restaurant?.name }}</td>
                                    <td>{{ claim.user?.name }}<br /><span class="text-caption text-medium-emphasis">{{ claim.user?.email }}</span></td>
                                    <td>{{ claim.notes || '—' }}</td>
                                    <td class="text-right">
                                        <v-btn size="small" color="secondary" variant="flat" class="mr-1" @click="approveClaim(claim)">
                                            Aprovar
                                        </v-btn>
                                        <v-btn size="small" color="error" variant="outlined" @click="openReject(claim)">
                                            Rejeitar
                                        </v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-card-text>
                </v-card>

                <v-card class="mt-6">
                    <v-card-title>Usuários</v-card-title>
                    <v-card-text>
                        <v-table>
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th style="width: 200px">Permissão</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users" :key="user.id">
                                    <td>{{ user.name }}</td>
                                    <td>{{ user.email }}</td>
                                    <td>
                                        <v-select
                                            :model-value="user.role"
                                            :items="roleOptions"
                                            density="compact"
                                            hide-details
                                            variant="outlined"
                                            @update:model-value="(role) => updateRole(user, role)"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-card-text>
                </v-card>

                <v-card class="mt-6">
                    <v-card-title>Estabelecimentos</v-card-title>
                    <v-card-text>
                        <v-table>
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Cidade</th>
                                    <th>Reivindicado</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="restaurant in restaurants" :key="restaurant.id">
                                    <td>{{ restaurant.name }}</td>
                                    <td>{{ restaurant.address_city || '—' }}</td>
                                    <td>{{ restaurant.claimed_at ? 'Sim' : 'Não' }}</td>
                                    <td>
                                        <v-chip size="small" :color="restaurant.is_active ? 'secondary' : undefined" variant="tonal">
                                            {{ restaurant.is_active ? 'Ativo' : 'Inativo' }}
                                        </v-chip>
                                    </td>
                                    <td class="text-right">
                                        <v-btn size="small" variant="text" @click="toggleRestaurant(restaurant)">
                                            {{ restaurant.is_active ? 'Desativar' : 'Reativar' }}
                                        </v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-card-text>
                </v-card>
            </div>
        </div>

        <v-dialog :model-value="rejectingClaim !== null" max-width="480" @update:model-value="(v) => !v && (rejectingClaim = null)">
            <v-card v-if="rejectingClaim">
                <v-card-title>Rejeitar reivindicação</v-card-title>
                <v-card-text>
                    <p class="text-body-2 mb-3">
                        {{ rejectingClaim.restaurant?.name }} · solicitado por {{ rejectingClaim.user?.name }}
                    </p>
                    <v-form @submit.prevent="submitReject">
                        <v-textarea
                            v-model="rejectForm.rejection_reason"
                            label="Motivo da rejeição"
                            rows="3"
                            :error-messages="rejectForm.errors.rejection_reason"
                        />
                        <v-btn type="submit" color="error" variant="flat" :loading="rejectForm.processing">
                            Confirmar rejeição
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </AuthenticatedLayout>
</template>
