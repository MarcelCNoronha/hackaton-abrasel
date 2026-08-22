<script setup>
import { onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useTheme } from 'vuetify';

const page = usePage();
const appName = page.props.appName;
const user = page.props.auth.user;

// Same runtime theme switch as PublicLayout/GuestLayout -- see plugins/vuetify.js.
const theme = useTheme();
onMounted(() => {
    theme.change('vicosaFoodDark');
});

const drawerOpen = ref(false);

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <v-app>
        <v-app-bar flat color="surface" height="72" style="border-bottom: 1px solid rgba(249, 115, 22, 0.28)">
            <v-container class="d-flex align-center" fluid style="max-width: 1400px">
                <Link :href="route('discover')" class="text-decoration-none d-flex align-center ga-2 mr-6">
                    <v-avatar size="36" rounded="lg" style="background: linear-gradient(135deg, #f97316, #ef4444)">
                        <v-icon icon="mdi-silverware-fork-knife" color="white" size="20" />
                    </v-avatar>
                    <span class="text-h6 font-weight-bold d-none d-sm-inline" style="letter-spacing: -0.02em">{{ appName }}</span>
                </Link>

                <!-- Nav horizontal so' a partir de md -- em telas menores esses botoes nao
                     cabem lado a lado e quebravam o app-bar; vira menu lateral abaixo. -->
                <template v-if="$vuetify.display.mdAndUp">
                    <v-btn variant="text" :href="route('discover')" :active="route().current('discover')">Descobrir</v-btn>
                    <v-btn variant="text" :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</v-btn>
                    <v-btn
                        v-if="['owner', 'admin'].includes(user.role)"
                        variant="text"
                        :href="route('owner.dashboard')"
                        :active="route().current('owner.*')"
                    >
                        Painel do estabelecimento
                    </v-btn>
                    <v-btn
                        v-else
                        variant="text"
                        :href="route('owner.claims.create')"
                        :active="route().current('owner.claims.*')"
                    >
                        Cadastrar restaurante
                    </v-btn>
                    <v-btn
                        v-if="['owner', 'admin'].includes(user.role)"
                        variant="text"
                        :href="route('owner.freelancers.index')"
                        :active="route().current('owner.freelancers.*')"
                    >
                        Freelancers
                    </v-btn>
                    <v-btn
                        v-if="user.freelancer_enabled_at"
                        variant="text"
                        :href="route('freelancer.jobs.index')"
                        :active="route().current('freelancer.jobs.*')"
                    >
                        Vagas
                    </v-btn>
                    <v-btn
                        v-if="user.freelancer_enabled_at"
                        variant="text"
                        :href="route('freelancer.hires.index')"
                        :active="route().current('freelancer.hires.*')"
                    >
                        Meus pedidos
                    </v-btn>
                    <v-btn
                        v-if="user.role === 'admin'"
                        variant="text"
                        :href="route('admin.dashboard')"
                        :active="route().current('admin.*')"
                    >
                        Administração
                    </v-btn>

                    <v-spacer />

                    <v-menu>
                        <template #activator="{ props: menuProps }">
                            <v-btn variant="text" v-bind="menuProps" append-icon="mdi-chevron-down">
                                {{ user.name }}
                            </v-btn>
                        </template>
                        <v-list density="compact">
                            <v-list-item :href="route('profile.edit')" title="Perfil" />
                            <v-list-item v-if="user.freelancer_enabled_at" :href="route('freelancer.profile.edit')" title="Meu perfil de freelancer" />
                            <v-list-item title="Sair" @click="logout" />
                        </v-list>
                    </v-menu>
                </template>

                <template v-else>
                    <v-spacer />
                    <v-app-bar-nav-icon @click="drawerOpen = true" />
                </template>
            </v-container>
        </v-app-bar>

        <v-navigation-drawer v-model="drawerOpen" temporary location="right" width="280">
            <v-list nav>
                <v-list-item :title="user.name" :subtitle="user.email" class="mb-2" />
                <v-divider class="mb-2" />
                <v-list-item :href="route('discover')" :active="route().current('discover')" title="Descobrir" prepend-icon="mdi-compass-outline" />
                <v-list-item :href="route('dashboard')" :active="route().current('dashboard')" title="Dashboard" prepend-icon="mdi-view-dashboard-outline" />
                <v-list-item
                    v-if="['owner', 'admin'].includes(user.role)"
                    :href="route('owner.dashboard')"
                    :active="route().current('owner.*')"
                    title="Painel do estabelecimento"
                    prepend-icon="mdi-storefront-outline"
                />
                <v-list-item
                    v-else
                    :href="route('owner.claims.create')"
                    :active="route().current('owner.claims.*')"
                    title="Cadastrar restaurante"
                    prepend-icon="mdi-storefront-outline"
                />
                <v-list-item
                    v-if="['owner', 'admin'].includes(user.role)"
                    :href="route('owner.freelancers.index')"
                    :active="route().current('owner.freelancers.*')"
                    title="Freelancers"
                    prepend-icon="mdi-account-hard-hat-outline"
                />
                <v-list-item
                    v-if="user.freelancer_enabled_at"
                    :href="route('freelancer.jobs.index')"
                    :active="route().current('freelancer.jobs.*')"
                    title="Vagas"
                    prepend-icon="mdi-bulletin-board"
                />
                <v-list-item
                    v-if="user.freelancer_enabled_at"
                    :href="route('freelancer.hires.index')"
                    :active="route().current('freelancer.hires.*')"
                    title="Meus pedidos"
                    prepend-icon="mdi-briefcase-outline"
                />
                <v-list-item
                    v-if="user.role === 'admin'"
                    :href="route('admin.dashboard')"
                    :active="route().current('admin.*')"
                    title="Administração"
                    prepend-icon="mdi-shield-crown-outline"
                />
                <v-divider class="my-2" />
                <v-list-item :href="route('profile.edit')" title="Perfil" prepend-icon="mdi-account-outline" />
                <v-list-item
                    v-if="user.freelancer_enabled_at"
                    :href="route('freelancer.profile.edit')"
                    title="Meu perfil de freelancer"
                    prepend-icon="mdi-card-account-details-outline"
                />
                <v-list-item title="Sair" prepend-icon="mdi-logout" @click="logout" />
            </v-list>
        </v-navigation-drawer>

        <v-main>
            <div v-if="$slots.header" class="page-header">
                <v-container style="max-width: 1400px">
                    <slot name="header" />
                </v-container>
            </div>

            <slot />
        </v-main>
    </v-app>
</template>

<style scoped>
.page-header {
    background: rgb(var(--v-theme-surface));
    border-bottom: 1px solid rgba(249, 115, 22, 0.28);
    padding-block: 20px;
}
</style>
