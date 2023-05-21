<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { onMounted } from 'vue';
import CKEditor from '@/Components/CKEditor.vue';


const props = defineProps({
    user: Array,
});

const form = useForm({
    id: 0,
    name: '',
    email: '',
    phone: '',
    signature: '',
});

const submit = () => {
    form.post(route('profile'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

onMounted(() => {
    if (props.user != null) {
        form.id = props.user.id;
        form.name = props.user.name;
        form.phone = props.user.phone;
        form.email = props.user.email;
        form.signature = props.user.signature;
    }

    $('#phone').mask('(00) 00000-0000');
});

</script>

<template>
    <AuthenticatedLayout>

        <Head title="Editar Perfil" />

        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Meus Dados</h1>
            </div>
        </template>

        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4 py-3 border-left-primary">
                    <div class="card-body">
                        <form @submit.prevent="submit">

                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <InputLabel for="name" value="Name" />
                                                <TextInput id="name" type="text" class="form-control" v-model="form.name"
                                                    required autofocus autocomplete="name" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.name" />
                                            </div>
                                            <div class="form-group">
                                                <InputLabel for="Phone" value="Telefone" />
                                                <TextInput id="phone" type="text" class="form-control" v-model="form.phone"
                                                    required autofocus autocomplete="phone" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.phone" />
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">

                                                <InputLabel for="email" value="Email" />
                                                <TextInput id="email" type="email" class="form-control" v-model="form.email"
                                                    required autocomplete="username" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.email" />
                                            </div>

                                            <div class="form-group">
                                                <InputLabel for="signature" value="Assinatura" />
                                                <CKEditor v-model:content="form.signature" :height="150" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.signature" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <ul class="list-group">
                                        <li v-for="(role, key) in user.roles"
                                            class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ role.name }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-4">

                                <PrimaryButton css-class="btn btn-primary float-right m-1"
                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status"
                                        aria-hidden="true"></span>
                                    Salvar
                                </PrimaryButton>

                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </AuthenticatedLayout>
</template>
