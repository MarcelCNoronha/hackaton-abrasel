<script setup>
import { onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from 'vuetify';

const page = usePage();
const user = page.props.auth?.user ?? null;
const appName = page.props.appName;

// See plugins/vuetify.js: this theme is scoped to the public/guest experience only, switched
// at runtime instead of being the app-wide default so it never touches the (still plain
// Tailwind) authenticated dashboards.
const theme = useTheme();
onMounted(() => {
    theme.change('vicosaFoodDark');
});
</script>

<template>
    <v-app>
        <v-app-bar flat color="surface" height="72" style="border-bottom: 1px solid rgba(249, 115, 22, 0.28)">
            <v-container class="d-flex align-center" fluid style="max-width: 1400px">
                <Link href="/" class="text-decoration-none d-flex align-center ga-2">
                    <v-avatar size="36" rounded="lg" style="background: linear-gradient(135deg, #f97316, #ef4444)">
                        <v-icon icon="mdi-silverware-fork-knife" color="white" size="20" />
                    </v-avatar>
                    <span class="text-h6 font-weight-bold" style="letter-spacing: -0.02em">{{ appName }}</span>
                </Link>

                <v-spacer />

                <template v-if="user">
                    <v-btn variant="text" :href="route('dashboard')" prepend-icon="mdi-account-circle">
                        Minha conta
                    </v-btn>
                </template>
                <template v-else>
                    <v-btn variant="text" :href="route('login')" class="mr-1">Entrar</v-btn>
                    <v-btn color="primary" variant="flat" :href="route('register')">
                        Cadastrar
                    </v-btn>
                </template>
            </v-container>
        </v-app-bar>

        <v-main>
            <slot />
        </v-main>
    </v-app>
</template>
