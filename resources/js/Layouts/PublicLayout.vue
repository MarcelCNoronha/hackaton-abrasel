<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth?.user ?? null;
const appName = page.props.appName;
</script>

<template>
    <v-app>
        <v-app-bar flat color="surface" height="72" border="b">
            <v-container class="d-flex align-center" fluid style="max-width: 1400px">
                <Link href="/" class="text-decoration-none d-flex align-center ga-2">
                    <v-avatar color="primary" size="36" rounded="lg">
                        <v-icon icon="mdi-silverware-fork-knife" color="white" size="20" />
                    </v-avatar>
                    <span class="text-h6 font-weight-bold" style="color: #241c1a; letter-spacing: -0.02em">{{
                        appName
                    }}</span>
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

        <v-main style="background: #faf7f2">
            <slot />
        </v-main>
    </v-app>
</template>
