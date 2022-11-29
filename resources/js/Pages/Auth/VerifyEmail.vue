<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
    status: String,
    email: String
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.email = props.email;
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>

        <Head title="Ativar Conta" />

        <div class="mb-4 text-sm text-gray-600">
            Sua conta já foi criada! Antes de começar, confirme o seu endereço de cadastro clicando no link presente no
            e-mail que te enviamos. Caso não tenha recebido o email, teremos o maior prazer em te enviar outro.
        </div>

        <div class="mb-4 font-medium text-sm text-green-600" v-if="verificationLinkSent">
            Um novo link de verificação foi enviado para o endereço de e-mail de cadastro..
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Enviar novamente o e-mail de verificação
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
