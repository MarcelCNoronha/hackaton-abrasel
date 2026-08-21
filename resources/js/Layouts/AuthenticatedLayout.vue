<script setup>
import { onMounted } from 'vue';
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
                        <v-list-item title="Sair" @click="logout" />
                    </v-list>
                </v-menu>
            </v-container>
        </v-app-bar>

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
