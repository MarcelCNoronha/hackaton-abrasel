<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    restaurants: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Painel do estabelecimento" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Painel do estabelecimento
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <v-alert
                    v-if="restaurants.length === 0"
                    type="info"
                    variant="tonal"
                    class="mb-4"
                >
                    Você ainda não gerencia nenhum estabelecimento. Reivindique
                    um estabelecimento existente ou cadastre um novo para
                    começar.
                </v-alert>

                <v-row v-else>
                    <v-col
                        v-for="restaurant in restaurants"
                        :key="restaurant.id"
                        cols="12"
                        md="6"
                        lg="4"
                    >
                        <v-card :title="restaurant.name" variant="elevated">
                            <v-card-text>
                                <div class="mb-1">
                                    ⭐ {{ restaurant.average_rating ?? '—' }}
                                    ({{ restaurant.reviews_count }} avaliações)
                                </div>
                                <div>{{ restaurant.menus_count }} cardápio(s)</div>
                            </v-card-text>
                            <v-card-actions>
                                <v-btn variant="text">Gerenciar</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
