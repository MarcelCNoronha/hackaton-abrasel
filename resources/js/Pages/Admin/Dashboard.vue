<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Administração" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Administração
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <v-row>
                    <v-col cols="12" sm="4">
                        <v-card title="Usuários" :subtitle="String(stats.users)" />
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-card
                            title="Estabelecimentos"
                            :subtitle="String(stats.restaurants)"
                        />
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-card
                            title="Reivindicações pendentes"
                            :subtitle="String(stats.pendingClaims)"
                        />
                    </v-col>
                </v-row>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
