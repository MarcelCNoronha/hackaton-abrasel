<script setup>
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <h3 class="text-subtitle-1 font-weight-bold mb-1">Excluir conta</h3>
    <p class="text-body-2 text-medium-emphasis mb-4">
        Depois de excluída, todos os dados da sua conta serão permanentemente removidos.
        Baixe qualquer informação que queira manter antes de continuar.
    </p>

    <v-btn color="error" variant="flat" @click="confirmUserDeletion">Excluir conta</v-btn>

    <v-dialog v-model="confirmingUserDeletion" max-width="480">
        <v-card>
            <v-card-title>Tem certeza que deseja excluir sua conta?</v-card-title>
            <v-card-text>
                <p class="text-body-2 text-medium-emphasis mb-4">
                    Depois de excluída, todos os dados da sua conta serão permanentemente removidos.
                    Digite sua senha para confirmar.
                </p>

                <v-text-field
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    label="Senha"
                    :error-messages="form.errors.password"
                    @keyup.enter="deleteUser"
                />

                <div class="d-flex justify-end ga-2 mt-2">
                    <v-btn variant="text" @click="closeModal">Cancelar</v-btn>
                    <v-btn color="error" variant="flat" :loading="form.processing" @click="deleteUser">
                        Excluir conta
                    </v-btn>
                </div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>
