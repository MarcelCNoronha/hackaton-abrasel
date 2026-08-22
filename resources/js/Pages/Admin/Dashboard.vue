<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    stats: { type: Object, required: true },
    pendingClaims: { type: Array, default: () => [] },
    users: { type: Object, required: true },
    restaurants: { type: Object, required: true },
    priceRanges: { type: Array, default: () => [] },
});

const roleOptions = [
    { title: 'Consumidor', value: 'consumer' },
    { title: 'Gestor', value: 'owner' },
    { title: 'Administrador', value: 'admin' },
];

const activeTab = ref('claims');

function goToPage(pageName, page) {
    router.get(route('admin.dashboard'), { [pageName]: page }, { preserveState: true, preserveScroll: true, only: ['users', 'restaurants'] });
}

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

// --- Restaurantes: ativar/desativar ---
function toggleRestaurant(restaurant) {
    router.patch(route('admin.restaurants.toggle-active', restaurant.slug), {}, { preserveScroll: true });
}

// --- Restaurantes: criar/editar ---
const editingRestaurant = ref(null); // null = fechado, {} = novo, objeto = editando
const restaurantForm = useForm({
    name: '',
    description: '',
    address_street: '',
    address_number: '',
    address_neighborhood: '',
    address_city: '',
    address_state: '',
    phone: '',
    whatsapp: '',
    price_range: null,
    latitude: '',
    longitude: '',
});

function openNewRestaurant() {
    restaurantForm.reset();
    // latitude/longitude are NOT NULL in the schema -- default to Viçosa's center so the form
    // doesn't start in an already-invalid state; the admin can drag these to the real spot.
    restaurantForm.latitude = '-20.7546';
    restaurantForm.longitude = '-42.8825';
    editingRestaurant.value = {};
}

function openEditRestaurant(restaurant) {
    editingRestaurant.value = restaurant;
    restaurantForm.name = restaurant.name ?? '';
    restaurantForm.description = restaurant.description ?? '';
    restaurantForm.address_street = restaurant.address_street ?? '';
    restaurantForm.address_number = restaurant.address_number ?? '';
    restaurantForm.address_neighborhood = restaurant.address_neighborhood ?? '';
    restaurantForm.address_city = restaurant.address_city ?? '';
    restaurantForm.address_state = restaurant.address_state ?? '';
    restaurantForm.phone = restaurant.phone ?? '';
    restaurantForm.whatsapp = restaurant.whatsapp ?? '';
    restaurantForm.price_range = restaurant.price_range ?? null;
    restaurantForm.latitude = restaurant.latitude ?? '';
    restaurantForm.longitude = restaurant.longitude ?? '';
}

function closeRestaurantDialog() {
    editingRestaurant.value = null;
}

function submitRestaurant() {
    const isNew = editingRestaurant.value && !editingRestaurant.value.id;
    const options = { preserveScroll: true, onSuccess: closeRestaurantDialog };

    if (isNew) {
        restaurantForm.post(route('admin.restaurants.store'), options);
    } else {
        restaurantForm.patch(route('admin.restaurants.update', editingRestaurant.value.slug), options);
    }
}

// --- Restaurantes: convidar gestor ---
const invitingRestaurant = ref(null);
const inviteForm = useForm({ email: '' });

function openInvite(restaurant) {
    invitingRestaurant.value = restaurant;
    inviteForm.reset();
}

function submitInvite() {
    inviteForm.post(route('admin.restaurants.invite', invitingRestaurant.value.slug), {
        preserveScroll: true,
        onSuccess: () => {
            invitingRestaurant.value = null;
        },
    });
}

// --- Restaurantes: sugerir campanha de cupom ---
const suggestingRestaurant = ref(null);
const campaignForm = useForm({
    name: '',
    description: '',
    benefit_description: '',
    starts_at: '',
    ends_at: '',
    coupon_validity_days: 7,
    quantity_available: '',
    per_user_limit: 1,
});

function openSuggestCampaign(restaurant) {
    suggestingRestaurant.value = restaurant;
    campaignForm.reset();
}

function submitCampaignSuggestion() {
    campaignForm.post(route('admin.coupon-campaigns.suggest', suggestingRestaurant.value.slug), {
        preserveScroll: true,
        onSuccess: () => {
            suggestingRestaurant.value = null;
        },
    });
}
</script>

<template>
    <Head title="Administração" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-h5 font-weight-bold">
                Administração
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <v-row class="mb-2">
                    <v-col cols="12" sm="4">
                        <v-card
                            title="Usuários"
                            :subtitle="String(stats.users)"
                            :class="{ 'tab-card--active': activeTab === 'users' }"
                            class="tab-card"
                            @click="activeTab = 'users'"
                        />
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-card
                            title="Estabelecimentos"
                            :subtitle="String(stats.restaurants)"
                            :class="{ 'tab-card--active': activeTab === 'restaurants' }"
                            class="tab-card"
                            @click="activeTab = 'restaurants'"
                        />
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-card
                            title="Reivindicações pendentes"
                            :subtitle="String(stats.pendingClaims)"
                            :class="{ 'tab-card--active': activeTab === 'claims' }"
                            class="tab-card"
                            @click="activeTab = 'claims'"
                        />
                    </v-col>
                </v-row>

                <v-card v-if="activeTab === 'claims'" class="mt-6">
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

                <v-card v-if="activeTab === 'users'" class="mt-6">
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
                                <tr v-for="user in users.data" :key="user.id">
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
                        <div class="d-flex justify-center mt-4">
                            <v-pagination
                                v-if="users.meta.last_page > 1"
                                :model-value="users.meta.current_page"
                                :length="users.meta.last_page"
                                density="comfortable"
                                @update:model-value="(page) => goToPage('users_page', page)"
                            />
                        </div>
                    </v-card-text>
                </v-card>

                <v-card v-if="activeTab === 'restaurants'" class="mt-6">
                    <v-card-title class="d-flex align-center justify-space-between">
                        Estabelecimentos
                        <v-btn size="small" color="primary" variant="flat" prepend-icon="mdi-plus" @click="openNewRestaurant">
                            Novo estabelecimento
                        </v-btn>
                    </v-card-title>
                    <v-card-text>
                        <v-table>
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Cidade</th>
                                    <th>Responsável</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="restaurant in restaurants.data" :key="restaurant.id">
                                    <td>{{ restaurant.name }}</td>
                                    <td>{{ restaurant.address_city || '—' }}</td>
                                    <td>
                                        <span v-if="restaurant.owners?.length">
                                            {{ restaurant.owners.map((o) => o.name).join(', ') }}
                                        </span>
                                        <span v-else class="text-medium-emphasis">Não reivindicado</span>
                                    </td>
                                    <td>
                                        <v-chip size="small" :color="restaurant.is_active ? 'secondary' : undefined" variant="tonal">
                                            {{ restaurant.is_active ? 'Ativo' : 'Inativo' }}
                                        </v-chip>
                                    </td>
                                    <td class="text-right text-no-wrap">
                                        <v-btn size="small" variant="text" @click="openEditRestaurant(restaurant)">Editar</v-btn>
                                        <v-btn size="small" variant="text" @click="openInvite(restaurant)">Convidar</v-btn>
                                        <v-btn size="small" variant="text" @click="openSuggestCampaign(restaurant)">Sugerir cupom</v-btn>
                                        <v-btn size="small" variant="text" @click="toggleRestaurant(restaurant)">
                                            {{ restaurant.is_active ? 'Desativar' : 'Reativar' }}
                                        </v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                        <div class="d-flex justify-center mt-4">
                            <v-pagination
                                v-if="restaurants.meta.last_page > 1"
                                :model-value="restaurants.meta.current_page"
                                :length="restaurants.meta.last_page"
                                density="comfortable"
                                @update:model-value="(page) => goToPage('restaurants_page', page)"
                            />
                        </div>
                    </v-card-text>
                </v-card>
            </div>
        </div>

        <!-- Rejeitar reivindicação -->
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

        <!-- Criar/editar estabelecimento -->
        <v-dialog :model-value="editingRestaurant !== null" max-width="640" @update:model-value="(v) => !v && closeRestaurantDialog()">
            <v-card v-if="editingRestaurant">
                <v-card-title>{{ editingRestaurant.id ? `Editar: ${editingRestaurant.name}` : 'Novo estabelecimento' }}</v-card-title>
                <v-card-text>
                    <v-form @submit.prevent="submitRestaurant">
                        <v-row>
                            <v-col cols="12" md="8">
                                <v-text-field v-model="restaurantForm.name" label="Nome" :error-messages="restaurantForm.errors.name" />
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-select v-model="restaurantForm.price_range" :items="priceRanges" label="Faixa de preço" />
                            </v-col>
                            <v-col cols="12">
                                <v-textarea v-model="restaurantForm.description" label="Descrição" rows="2" />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field v-model="restaurantForm.address_street" label="Rua" />
                            </v-col>
                            <v-col cols="6" md="2">
                                <v-text-field v-model="restaurantForm.address_number" label="Número" />
                            </v-col>
                            <v-col cols="6" md="4">
                                <v-text-field v-model="restaurantForm.address_neighborhood" label="Bairro" />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field v-model="restaurantForm.address_city" label="Cidade" />
                            </v-col>
                            <v-col cols="6" md="2">
                                <v-text-field v-model="restaurantForm.address_state" label="UF" maxlength="2" />
                            </v-col>
                            <v-col cols="6" md="4">
                                <v-text-field v-model="restaurantForm.phone" label="Telefone" />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field v-model="restaurantForm.whatsapp" label="WhatsApp" />
                            </v-col>
                            <v-col cols="6" md="3">
                                <v-text-field
                                    v-model="restaurantForm.latitude"
                                    label="Latitude"
                                    required
                                    hint="Obrigatório, pra aparecer no mapa"
                                    persistent-hint
                                    :error-messages="restaurantForm.errors.latitude"
                                />
                            </v-col>
                            <v-col cols="6" md="3">
                                <v-text-field
                                    v-model="restaurantForm.longitude"
                                    label="Longitude"
                                    required
                                    :error-messages="restaurantForm.errors.longitude"
                                />
                            </v-col>
                        </v-row>
                        <v-btn type="submit" color="primary" variant="flat" :loading="restaurantForm.processing">
                            {{ editingRestaurant.id ? 'Salvar' : 'Criar' }}
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>

        <!-- Convidar gestor -->
        <v-dialog :model-value="invitingRestaurant !== null" max-width="480" @update:model-value="(v) => !v && (invitingRestaurant = null)">
            <v-card v-if="invitingRestaurant">
                <v-card-title>Convidar gestor</v-card-title>
                <v-card-text>
                    <p class="text-body-2 mb-3">
                        Pra {{ invitingRestaurant.name }}. A pessoa precisa já ter uma conta cadastrada
                        — sem isso, não há como enviar um convite por e-mail ainda.
                    </p>
                    <v-form @submit.prevent="submitInvite">
                        <v-text-field
                            v-model="inviteForm.email"
                            type="email"
                            label="E-mail da pessoa"
                            :error-messages="inviteForm.errors.email"
                        />
                        <v-btn type="submit" color="primary" variant="flat" :loading="inviteForm.processing">
                            Vincular como gestor
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>

        <!-- Sugerir campanha de cupom -->
        <v-dialog :model-value="suggestingRestaurant !== null" max-width="600" @update:model-value="(v) => !v && (suggestingRestaurant = null)">
            <v-card v-if="suggestingRestaurant">
                <v-card-title>Sugerir campanha para {{ suggestingRestaurant.name }}</v-card-title>
                <v-card-text>
                    <p class="text-body-2 mb-3">
                        A campanha fica pendente até o gestor aceitar — ela não vale cupom sozinha.
                    </p>
                    <v-form @submit.prevent="submitCampaignSuggestion">
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field v-model="campaignForm.name" label="Nome da campanha" :error-messages="campaignForm.errors.name" />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field v-model="campaignForm.benefit_description" label="Benefício" :error-messages="campaignForm.errors.benefit_description" />
                            </v-col>
                            <v-col cols="12">
                                <v-textarea v-model="campaignForm.description" label="Descrição" rows="2" />
                            </v-col>
                            <v-col cols="6" md="3">
                                <v-text-field v-model="campaignForm.starts_at" type="date" label="Início" :error-messages="campaignForm.errors.starts_at" />
                            </v-col>
                            <v-col cols="6" md="3">
                                <v-text-field v-model="campaignForm.ends_at" type="date" label="Fim" :error-messages="campaignForm.errors.ends_at" />
                            </v-col>
                            <v-col cols="6" md="3">
                                <v-text-field v-model="campaignForm.coupon_validity_days" type="number" min="1" label="Validade (dias)" />
                            </v-col>
                            <v-col cols="6" md="3">
                                <v-text-field v-model="campaignForm.per_user_limit" type="number" min="1" label="Limite/cliente" />
                            </v-col>
                        </v-row>
                        <v-btn type="submit" color="primary" variant="flat" :loading="campaignForm.processing">
                            Enviar sugestão
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
.tab-card {
    cursor: pointer;
    transition: border-color 0.15s ease;
    border: 2px solid transparent;
}

.tab-card--active {
    border-color: rgb(var(--v-theme-primary));
}
</style>
