<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

const isOwner = computed(() => ['owner', 'admin'].includes(user.value.role));
const isFreelancerEnabled = computed(() => !!user.value.freelancer_enabled_at);

function activateFreelancer() {
    router.patch(route('freelancer.access.activate'), {}, { preserveScroll: true });
}

function deactivateFreelancer() {
    if (!confirm('Desativar o módulo freelancer? Seu perfil deixa de aparecer pros restaurantes.')) return;
    router.delete(route('freelancer.access.deactivate'), { preserveScroll: true });
}
</script>

<template>
    <div>
        <h3 class="text-subtitle-1 font-weight-bold mb-1">Módulos</h3>
        <p class="text-body-2 text-medium-emphasis mb-4">Recursos extras que você pode ligar pra essa conta.</p>

        <v-list class="pa-0" style="background: transparent">
            <v-list-item class="px-0">
                <template #prepend>
                    <v-icon icon="mdi-storefront-outline" class="mr-3" />
                </template>
                <v-list-item-title class="font-weight-medium">Painel do estabelecimento</v-list-item-title>
                <v-list-item-subtitle v-if="isOwner">Ativo -- ligado à sua conta pela reivindicação de um restaurante.</v-list-item-subtitle>
                <v-list-item-subtitle v-else>
                    Ativa automaticamente quando você reivindica um restaurante e um admin aprova.
                </v-list-item-subtitle>
                <template #append>
                    <v-chip v-if="isOwner" color="secondary" size="small" variant="flat">Ativo</v-chip>
                    <v-btn v-else variant="outlined" size="small" :href="route('owner.claims.create')">
                        Cadastrar restaurante
                    </v-btn>
                </template>
            </v-list-item>

            <v-divider />

            <v-list-item class="px-0">
                <template #prepend>
                    <v-icon icon="mdi-account-hard-hat-outline" class="mr-3" />
                </template>
                <v-list-item-title class="font-weight-medium">Módulo freelancer</v-list-item-title>
                <v-list-item-subtitle>
                    Crie um perfil profissional e apareça pros restaurantes que buscam contratar.
                </v-list-item-subtitle>
                <template #append>
                    <div class="d-flex align-center ga-2">
                        <v-btn v-if="isFreelancerEnabled" :href="route('freelancer.profile.edit')" color="primary" variant="flat" size="small">
                            Meu perfil
                        </v-btn>
                        <v-btn v-if="isFreelancerEnabled" variant="text" size="small" color="error" @click="deactivateFreelancer">
                            Desativar
                        </v-btn>
                        <v-btn v-else color="primary" variant="flat" size="small" @click="activateFreelancer">
                            Ativar
                        </v-btn>
                    </div>
                </template>
            </v-list-item>
        </v-list>
    </div>
</template>
