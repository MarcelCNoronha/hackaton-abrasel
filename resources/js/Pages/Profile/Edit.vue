<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import ModulesCard from './Partials/ModulesCard.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Meu perfil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-h5 font-weight-bold">Meu perfil</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto d-flex flex-column ga-4" style="max-width: 640px">
                <v-card class="pa-4">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                </v-card>

                <v-card class="pa-4">
                    <ModulesCard />
                </v-card>

                <v-card class="pa-4">
                    <UpdatePasswordForm />
                </v-card>

                <v-card class="pa-4">
                    <DeleteUserForm />
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
