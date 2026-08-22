<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    restaurant: { type: Object, required: true },
    priceRanges: { type: Array, default: () => [] },
    foodTagSuggestions: { type: Array, default: () => [] },
    dietaryTags: { type: Array, default: () => [] },
});

const weekdayNames = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
const tab = ref('perfil');

// --- Perfil ---
const profileForm = useForm({
    name: props.restaurant.name ?? '',
    description: props.restaurant.description ?? '',
    address_street: props.restaurant.address_street ?? '',
    address_number: props.restaurant.address_number ?? '',
    address_neighborhood: props.restaurant.address_neighborhood ?? '',
    address_city: props.restaurant.address_city ?? '',
    address_state: props.restaurant.address_state ?? '',
    phone: props.restaurant.phone ?? '',
    whatsapp: props.restaurant.whatsapp ?? '',
    price_range: props.restaurant.price_range ?? null,
    cover_photo: null,
    banner_photo: null,
});

function submitProfile() {
    // useForm() detecta os File nesses dois campos e troca pra POST com _method=PATCH
    // sozinho -- upload multipart nao viaja num PATCH de verdade.
    profileForm.patch(route('owner.restaurants.update', props.restaurant.slug), {
        preserveScroll: true,
        onSuccess: () => {
            profileForm.cover_photo = null;
            profileForm.banner_photo = null;
        },
    });
}

// --- Horários ---
const hoursByWeekday = computed(() => {
    const map = new Map(props.restaurant.business_hours?.map((h) => [h.weekday, h]) ?? []);
    return Array.from({ length: 7 }, (_, weekday) => {
        const existing = map.get(weekday);
        return {
            weekday,
            is_closed: existing?.is_closed ?? true,
            opens_at: existing?.opens_at?.slice(0, 5) ?? '',
            closes_at: existing?.closes_at?.slice(0, 5) ?? '',
        };
    });
});

const hoursForm = useForm({ hours: hoursByWeekday.value });

function submitHours() {
    hoursForm.put(route('owner.restaurants.hours.update', props.restaurant.slug), { preserveScroll: true });
}

// --- Cardápio ---
const menu = computed(() => props.restaurant.menus?.[0] ?? null);

const newCategoryName = ref('');
const categoryForm = useForm({ name: '' });

function addCategory() {
    categoryForm.name = newCategoryName.value;
    categoryForm.post(route('owner.menu-categories.store', menu.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            newCategoryName.value = '';
        },
    });
}

function deleteCategory(categoryId) {
    if (!confirm('Remover esta categoria e todos os itens dela?')) return;
    categoryForm.delete(route('owner.menu-categories.destroy', categoryId), { preserveScroll: true });
}

const dietaryTagOptions = computed(() =>
    props.dietaryTags.map((tag) => ({ title: tag.name, value: tag.id, kind: tag.kind })),
);

const itemDialogCategoryId = ref(null);
const editingItem = ref(null);
const itemForm = useForm({
    name: '',
    description: '',
    price: '',
    compare_at_price: '',
    is_available: true,
    food_tags: [],
    dietary_tags: [],
    photo: null,
});

function openNewItemDialog(categoryId) {
    editingItem.value = null;
    itemForm.reset();
    itemForm.is_available = true;
    itemForm.food_tags = [];
    itemForm.dietary_tags = [];
    itemForm.photo = null;
    itemDialogCategoryId.value = categoryId;
}

function openEditItemDialog(item) {
    editingItem.value = item;
    itemForm.name = item.name;
    itemForm.description = item.description ?? '';
    itemForm.price = item.price;
    itemForm.compare_at_price = item.compare_at_price ?? '';
    itemForm.is_available = item.is_available;
    itemForm.food_tags = (item.food_tags ?? []).map((tag) => tag.name);
    itemForm.dietary_tags = (item.dietary_tags ?? []).map((tag) => tag.id);
    itemForm.photo = null;
    itemDialogCategoryId.value = 'edit';
}

function closeItemDialog() {
    itemDialogCategoryId.value = null;
    editingItem.value = null;
}

function submitItem() {
    if (editingItem.value) {
        itemForm.patch(route('owner.menu-items.update', editingItem.value.id), {
            preserveScroll: true,
            onSuccess: closeItemDialog,
        });
    } else {
        itemForm.post(route('owner.menu-items.store', itemDialogCategoryId.value), {
            preserveScroll: true,
            onSuccess: closeItemDialog,
        });
    }
}

function toggleFeatured(item) {
    router.patch(route('owner.menu-items.toggle-featured', item.id), {}, { preserveScroll: true });
}

function deleteItem(itemId) {
    if (!confirm('Remover este item do cardápio?')) return;
    itemForm.delete(route('owner.menu-items.destroy', itemId), { preserveScroll: true });
}

// --- QR Code ---
const qrForm = useForm({});

function generateQrCode() {
    qrForm.post(route('owner.qrcode.store', props.restaurant.slug), { preserveScroll: true });
}

const qrImageUrl = computed(() => {
    const token = props.restaurant.active_qr_token;
    if (!token) return null;
    return `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(token.code)}`;
});

// --- Cupons ---
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

function submitCampaign() {
    campaignForm.post(route('owner.coupon-campaigns.store', props.restaurant.slug), {
        preserveScroll: true,
        onSuccess: () => campaignForm.reset(),
    });
}

function isCampaignPending(campaign) {
    return campaign.proposed_by !== null && campaign.accepted_at === null;
}

const pendingCampaigns = computed(() => (props.restaurant.coupon_campaigns ?? []).filter(isCampaignPending));
const ownCampaigns = computed(() => (props.restaurant.coupon_campaigns ?? []).filter((c) => !isCampaignPending(c)));

function acceptCampaign(campaignId) {
    router.patch(route('owner.coupon-campaigns.accept', campaignId), {}, { preserveScroll: true });
}

function rejectCampaign(campaignId) {
    if (!confirm('Recusar esta sugestão de campanha? Ela será removida.')) return;
    router.delete(route('owner.coupon-campaigns.reject', campaignId), { preserveScroll: true });
}

const redeemForm = useForm({ code: '' });

function redeemCoupon() {
    redeemForm.post(route('owner.coupons.redeem', props.restaurant.slug), {
        preserveScroll: true,
        onSuccess: () => {
            redeemForm.reset();
        },
    });
}

// --- Avaliações ---
const replyForms = reactive({});

function replyForm(reviewId) {
    if (!replyForms[reviewId]) {
        replyForms[reviewId] = useForm({ message: '' });
    }
    return replyForms[reviewId];
}

function submitReply(reviewId) {
    replyForm(reviewId).post(route('owner.reviews.reply', reviewId), { preserveScroll: true });
}
</script>

<template>
    <Head :title="`Gerenciar ${restaurant.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-h5 font-weight-bold">
                Gerenciar: {{ restaurant.name }}
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <v-card>
                    <v-tabs v-model="tab" color="primary" show-arrows>
                        <v-tab value="perfil">Perfil</v-tab>
                        <v-tab value="horarios">Horários</v-tab>
                        <v-tab value="cardapio">Cardápio</v-tab>
                        <v-tab value="qrcode">QR Code</v-tab>
                        <v-tab value="cupons">Cupons</v-tab>
                        <v-tab value="avaliacoes">Avaliações</v-tab>
                    </v-tabs>

                    <v-card-text>
                        <v-window v-model="tab">
                            <!-- Perfil -->
                            <v-window-item value="perfil">
                                <v-form @submit.prevent="submitProfile">
                                    <v-row>
                                        <v-col cols="12" md="8">
                                            <v-text-field v-model="profileForm.name" label="Nome" :error-messages="profileForm.errors.name" />
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-select
                                                v-model="profileForm.price_range"
                                                :items="priceRanges"
                                                label="Faixa de preço"
                                                :error-messages="profileForm.errors.price_range"
                                            />
                                        </v-col>
                                        <v-col cols="12">
                                            <v-textarea v-model="profileForm.description" label="Descrição" rows="3" :error-messages="profileForm.errors.description" />
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="profileForm.address_street" label="Rua" :error-messages="profileForm.errors.address_street" />
                                        </v-col>
                                        <v-col cols="6" md="2">
                                            <v-text-field v-model="profileForm.address_number" label="Número" />
                                        </v-col>
                                        <v-col cols="6" md="4">
                                            <v-text-field v-model="profileForm.address_neighborhood" label="Bairro" />
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="profileForm.address_city" label="Cidade" />
                                        </v-col>
                                        <v-col cols="6" md="2">
                                            <v-text-field v-model="profileForm.address_state" label="UF" maxlength="2" />
                                        </v-col>
                                        <v-col cols="6" md="4">
                                            <v-text-field v-model="profileForm.phone" label="Telefone" />
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="profileForm.whatsapp" label="WhatsApp" hint="Somente números, com DDD" />
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-file-input
                                                v-model="profileForm.cover_photo"
                                                label="Foto de capa (aparece na lista de busca)"
                                                accept="image/*"
                                                prepend-icon="mdi-image"
                                                show-size
                                                :error-messages="profileForm.errors.cover_photo"
                                            />
                                            <v-img
                                                v-if="!profileForm.cover_photo && restaurant.cover_photo_path"
                                                :src="`/storage/${restaurant.cover_photo_path}`"
                                                height="90"
                                                width="120"
                                                cover
                                                rounded="lg"
                                                class="mt-1"
                                            />
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-file-input
                                                v-model="profileForm.banner_photo"
                                                label="Banner de topo da página"
                                                accept="image/*"
                                                prepend-icon="mdi-panorama"
                                                show-size
                                                :error-messages="profileForm.errors.banner_photo"
                                            />
                                            <v-img
                                                v-if="!profileForm.banner_photo && restaurant.banner_photo_path"
                                                :src="`/storage/${restaurant.banner_photo_path}`"
                                                height="90"
                                                width="200"
                                                cover
                                                rounded="lg"
                                                class="mt-1"
                                            />
                                        </v-col>
                                    </v-row>
                                    <v-btn type="submit" color="primary" variant="flat" :loading="profileForm.processing">
                                        Salvar perfil
                                    </v-btn>
                                </v-form>
                            </v-window-item>

                            <!-- Horários -->
                            <v-window-item value="horarios">
                                <v-form @submit.prevent="submitHours">
                                    <div v-for="(day, index) in hoursForm.hours" :key="day.weekday" class="d-flex flex-wrap align-center ga-3 mb-2">
                                        <span style="min-width: 90px" class="text-body-2 font-weight-medium">{{ weekdayNames[day.weekday] }}</span>
                                        <v-switch v-model="day.is_closed" color="primary" density="compact" hide-details label="Fechado" />
                                        <template v-if="!day.is_closed">
                                            <v-text-field v-model="hoursForm.hours[index].opens_at" type="time" label="Abre" density="compact" hide-details style="max-width: 140px; flex: 1 1 120px" />
                                            <v-text-field v-model="hoursForm.hours[index].closes_at" type="time" label="Fecha" density="compact" hide-details style="max-width: 140px; flex: 1 1 120px" />
                                        </template>
                                    </div>
                                    <v-btn type="submit" color="primary" variant="flat" class="mt-3" :loading="hoursForm.processing">
                                        Salvar horários
                                    </v-btn>
                                </v-form>
                            </v-window-item>

                            <!-- Cardápio -->
                            <v-window-item value="cardapio">
                                <div v-if="menu">
                                    <div v-for="category in menu.categories" :key="category.id" class="mb-6">
                                        <div class="d-flex align-center justify-space-between mb-2">
                                            <h3 class="text-subtitle-1 font-weight-bold">{{ category.name }}</h3>
                                            <div>
                                                <v-btn size="small" variant="text" prepend-icon="mdi-plus" @click="openNewItemDialog(category.id)">
                                                    Item
                                                </v-btn>
                                                <v-btn variant="text" color="error" icon="mdi-delete-outline" @click="deleteCategory(category.id)" />
                                            </div>
                                        </div>

                                        <v-card
                                            v-for="item in category.items"
                                            :key="item.id"
                                            variant="outlined"
                                            class="mb-2"
                                            :style="item.id === restaurant.featured_menu_item_id ? { borderColor: 'rgb(var(--v-theme-accent))' } : {}"
                                        >
                                            <v-card-item>
                                                <template v-if="item.main_photo_path" #prepend>
                                                    <v-img :src="`/storage/${item.main_photo_path}`" width="48" height="48" cover rounded="lg" />
                                                </template>
                                                <v-card-title class="d-flex justify-space-between align-start ga-2">
                                                    <span>{{ item.name }}</span>
                                                    <span class="text-no-wrap">
                                                        <span
                                                            v-if="item.compare_at_price && Number(item.compare_at_price) > Number(item.price)"
                                                            class="text-medium-emphasis text-decoration-line-through mr-1"
                                                        >
                                                            R$ {{ Number(item.compare_at_price).toFixed(2).replace('.', ',') }}
                                                        </span>
                                                        <span class="text-primary font-weight-bold">R$ {{ Number(item.price).toFixed(2).replace('.', ',') }}</span>
                                                    </span>
                                                </v-card-title>
                                                <v-card-subtitle v-if="item.description">{{ item.description }}</v-card-subtitle>
                                            </v-card-item>
                                            <v-card-text v-if="item.food_tags?.length || item.dietary_tags?.length" class="pt-0 pb-2">
                                                <v-chip v-for="tag in item.food_tags" :key="`food-${tag.id}`" size="small" variant="tonal" class="mr-1">
                                                    {{ tag.name }}
                                                </v-chip>
                                                <v-chip
                                                    v-for="tag in item.dietary_tags"
                                                    :key="`dietary-${tag.id}`"
                                                    size="small"
                                                    variant="tonal"
                                                    :color="tag.kind === 'allergen' ? 'warning' : 'secondary'"
                                                    class="mr-1"
                                                >
                                                    {{ tag.name }}
                                                </v-chip>
                                            </v-card-text>
                                            <v-card-actions>
                                                <v-chip v-if="!item.is_available" size="small" color="warning" variant="tonal">Indisponível</v-chip>
                                                <v-chip v-if="item.id === restaurant.featured_menu_item_id" size="small" color="accent" variant="flat" prepend-icon="mdi-star">
                                                    Destaque
                                                </v-chip>
                                                <v-spacer />
                                                <v-btn
                                                    variant="text"
                                                    :color="item.id === restaurant.featured_menu_item_id ? 'accent' : undefined"
                                                    :icon="item.id === restaurant.featured_menu_item_id ? 'mdi-star' : 'mdi-star-outline'"
                                                    title="Destacar este item na busca"
                                                    @click="toggleFeatured(item)"
                                                />
                                                <v-btn size="small" variant="text" @click="openEditItemDialog(item)">Editar</v-btn>
                                                <v-btn size="small" variant="text" color="error" @click="deleteItem(item.id)">Remover</v-btn>
                                            </v-card-actions>
                                        </v-card>

                                        <p v-if="!category.items.length" class="text-body-2 text-medium-emphasis">
                                            Nenhum item nessa categoria ainda.
                                        </p>
                                    </div>

                                    <v-form @submit.prevent="addCategory" class="d-flex ga-2 align-center mt-4">
                                        <v-text-field v-model="newCategoryName" label="Nova categoria" density="compact" hide-details />
                                        <v-btn type="submit" color="primary" variant="flat" :loading="categoryForm.processing">Adicionar</v-btn>
                                    </v-form>
                                </div>

                                <v-dialog :model-value="itemDialogCategoryId !== null" max-width="480" @update:model-value="closeItemDialog">
                                    <v-card>
                                        <v-card-title>{{ editingItem ? 'Editar item' : 'Novo item' }}</v-card-title>
                                        <v-card-text>
                                            <v-form @submit.prevent="submitItem">
                                                <v-text-field v-model="itemForm.name" label="Nome" :error-messages="itemForm.errors.name" />
                                                <v-textarea v-model="itemForm.description" label="Descrição" rows="2" :error-messages="itemForm.errors.description" />
                                                <v-file-input
                                                    v-model="itemForm.photo"
                                                    label="Foto do prato"
                                                    accept="image/*"
                                                    prepend-icon="mdi-camera"
                                                    show-size
                                                    :error-messages="itemForm.errors.photo"
                                                />
                                                <v-img
                                                    v-if="!itemForm.photo && editingItem?.main_photo_path"
                                                    :src="`/storage/${editingItem.main_photo_path}`"
                                                    height="60"
                                                    width="60"
                                                    cover
                                                    rounded="lg"
                                                    class="mb-3"
                                                />
                                                <div class="d-flex ga-2">
                                                    <v-text-field
                                                        v-model="itemForm.compare_at_price"
                                                        type="number" step="0.01" min="0"
                                                        label="De (R$) -- opcional"
                                                        hint="Preço original, exibido riscado"
                                                        persistent-hint
                                                        :error-messages="itemForm.errors.compare_at_price"
                                                    />
                                                    <v-text-field
                                                        v-model="itemForm.price"
                                                        type="number" step="0.01" min="0"
                                                        label="Por (R$)"
                                                        :error-messages="itemForm.errors.price"
                                                    />
                                                </div>
                                                <v-combobox
                                                    v-model="itemForm.food_tags"
                                                    :items="foodTagSuggestions"
                                                    label="Tags (ingredientes, tipo de prato)"
                                                    hint="Ex.: Frango, Feijão, Hambúrguer -- digite e aperte Enter para criar uma nova"
                                                    persistent-hint
                                                    multiple
                                                    chips
                                                    closable-chips
                                                    :error-messages="itemForm.errors.food_tags"
                                                />
                                                <v-select
                                                    v-model="itemForm.dietary_tags"
                                                    :items="dietaryTagOptions"
                                                    label="Restrições alimentares (dieta, alergia)"
                                                    hint="Marque o que esse item atende -- alimenta o filtro de restrição alimentar da busca"
                                                    persistent-hint
                                                    multiple
                                                    chips
                                                    closable-chips
                                                    :error-messages="itemForm.errors.dietary_tags"
                                                />
                                                <v-switch v-model="itemForm.is_available" color="primary" label="Disponível" class="mt-2" />
                                                <v-btn type="submit" color="primary" variant="flat" :loading="itemForm.processing">
                                                    Salvar
                                                </v-btn>
                                            </v-form>
                                        </v-card-text>
                                    </v-card>
                                </v-dialog>
                            </v-window-item>

                            <!-- QR Code -->
                            <v-window-item value="qrcode">
                                <div v-if="restaurant.active_qr_token" class="mb-4">
                                    <img :src="qrImageUrl" alt="QR Code de check-in" width="220" height="220" class="mb-2" />
                                    <p class="text-body-2 text-medium-emphasis">
                                        Válido até {{ new Date(restaurant.active_qr_token.expires_at).toLocaleString('pt-BR') }}
                                    </p>
                                </div>
                                <v-alert v-else type="info" variant="tonal" class="mb-4">
                                    Nenhum QR Code ativo no momento.
                                </v-alert>
                                <v-btn color="primary" variant="flat" :loading="qrForm.processing" @click="generateQrCode">
                                    Gerar novo QR Code
                                </v-btn>
                            </v-window-item>

                            <!-- Cupons -->
                            <v-window-item value="cupons">
                                <h3 class="text-subtitle-1 font-weight-bold mb-2">Resgatar cupom no balcão</h3>
                                <v-form @submit.prevent="redeemCoupon" class="d-flex flex-wrap ga-2 align-start mb-6">
                                    <v-text-field
                                        v-model="redeemForm.code"
                                        label="Código do cupom"
                                        density="compact"
                                        hide-details="auto"
                                        :error-messages="redeemForm.errors.code"
                                        style="max-width: 260px"
                                    />
                                    <v-btn type="submit" color="primary" variant="flat" :loading="redeemForm.processing">
                                        Resgatar
                                    </v-btn>
                                </v-form>

                                <template v-if="pendingCampaigns.length">
                                    <h3 class="text-subtitle-1 font-weight-bold mb-2">Sugestões da administração</h3>
                                    <v-card v-for="campaign in pendingCampaigns" :key="campaign.id" variant="outlined" class="mb-2" style="border-color: rgb(var(--v-theme-warning))">
                                        <v-card-item>
                                            <v-card-title>{{ campaign.name }}</v-card-title>
                                            <v-card-subtitle>{{ campaign.benefit_description }}</v-card-subtitle>
                                        </v-card-item>
                                        <v-card-text>
                                            Vigência: {{ campaign.starts_at }} até {{ campaign.ends_at }} · Limite por cliente: {{ campaign.per_user_limit }}
                                        </v-card-text>
                                        <v-card-actions>
                                            <v-chip size="small" color="warning" variant="tonal">Aguardando sua decisão</v-chip>
                                            <v-spacer />
                                            <v-btn size="small" color="secondary" variant="flat" @click="acceptCampaign(campaign.id)">Aceitar</v-btn>
                                            <v-btn size="small" color="error" variant="outlined" @click="rejectCampaign(campaign.id)">Recusar</v-btn>
                                        </v-card-actions>
                                    </v-card>
                                    <v-divider class="my-4" />
                                </template>

                                <v-card v-for="campaign in ownCampaigns" :key="campaign.id" variant="outlined" class="mb-2">
                                    <v-card-item>
                                        <v-card-title>{{ campaign.name }}</v-card-title>
                                        <v-card-subtitle>{{ campaign.benefit_description }}</v-card-subtitle>
                                    </v-card-item>
                                    <v-card-text>
                                        Vigência: {{ campaign.starts_at }} até {{ campaign.ends_at }} · Limite por cliente: {{ campaign.per_user_limit }}
                                        <v-chip size="small" class="ml-2" :color="campaign.is_active ? 'secondary' : undefined" variant="tonal">
                                            {{ campaign.is_active ? 'Ativa' : 'Inativa' }}
                                        </v-chip>
                                    </v-card-text>
                                </v-card>
                                <p v-if="!ownCampaigns.length && !pendingCampaigns.length" class="text-body-2 text-medium-emphasis mb-4">
                                    Nenhuma campanha de cupom criada ainda.
                                </p>

                                <v-divider class="my-4" />

                                <h3 class="text-subtitle-1 font-weight-bold mb-3">Nova campanha</h3>
                                <v-form @submit.prevent="submitCampaign">
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="campaignForm.name" label="Nome da campanha" :error-messages="campaignForm.errors.name" />
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="campaignForm.benefit_description" label="Benefício (ex: 10% de desconto)" :error-messages="campaignForm.errors.benefit_description" />
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
                                            <v-text-field v-model="campaignForm.coupon_validity_days" type="number" min="1" label="Validade do cupom (dias)" />
                                        </v-col>
                                        <v-col cols="6" md="3">
                                            <v-text-field v-model="campaignForm.per_user_limit" type="number" min="1" label="Limite por cliente" />
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-text-field v-model="campaignForm.quantity_available" type="number" min="1" label="Quantidade disponível (opcional)" />
                                        </v-col>
                                    </v-row>
                                    <v-btn type="submit" color="primary" variant="flat" :loading="campaignForm.processing">
                                        Criar campanha
                                    </v-btn>
                                </v-form>
                            </v-window-item>

                            <!-- Avaliações -->
                            <v-window-item value="avaliacoes">
                                <v-alert v-if="!restaurant.reviews?.length" type="info" variant="tonal">
                                    Nenhuma avaliação recebida ainda.
                                </v-alert>
                                <v-card v-for="review in restaurant.reviews" :key="review.id" variant="outlined" class="mb-3">
                                    <v-card-item>
                                        <v-card-title class="d-flex align-center ga-2">
                                            {{ review.user?.name ?? 'Usuário removido' }}
                                            <v-icon icon="mdi-star" size="16" color="accent" /> {{ review.rating }}
                                        </v-card-title>
                                    </v-card-item>
                                    <v-card-text>
                                        <p v-if="review.comment">{{ review.comment }}</p>

                                        <div v-if="review.reply" class="reply-box">
                                            <strong>Sua resposta</strong>
                                            <p class="mb-0">{{ review.reply.message }}</p>
                                        </div>
                                        <v-form v-else @submit.prevent="submitReply(review.id)" class="d-flex ga-2 align-end mt-2">
                                            <v-textarea v-model="replyForm(review.id).message" label="Responder avaliação" rows="2" hide-details density="compact" />
                                            <v-btn type="submit" color="primary" variant="flat" :loading="replyForm(review.id).processing">
                                                Responder
                                            </v-btn>
                                        </v-form>
                                    </v-card-text>
                                </v-card>
                            </v-window-item>
                        </v-window>
                    </v-card-text>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.reply-box {
    background: rgb(var(--v-theme-background));
    border-radius: 8px;
    padding: 12px;
    margin-top: 8px;
}
</style>
