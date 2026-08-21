<script setup>
import { onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from 'vuetify';

const page = usePage();
const appName = page.props.appName;

// Same runtime theme switch as PublicLayout.vue — see plugins/vuetify.js for why this is
// scoped here instead of being the app-wide default.
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
            </v-container>
        </v-app-bar>

        <v-main>
            <v-container class="d-flex align-center justify-center" style="min-height: calc(100vh - 72px)">
                <v-card width="100%" max-width="440" class="pa-6" style="border: 1px solid rgba(249, 115, 22, 0.28)">
                    <slot />
                </v-card>
            </v-container>
        </v-main>
    </v-app>
</template>
