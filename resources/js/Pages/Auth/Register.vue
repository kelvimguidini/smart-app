<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';


const props = defineProps({
    users: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Usuários" />

        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Cadastro de usuários</h1>
            </div>
        </template>

        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4 py-3 border-left-primary">
                    <div class="card-body">
                        <form @submit.prevent="submit">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">

                                        <InputLabel for="name" value="Name" />
                                        <TextInput id="name" type="text" class="form-control" v-model="form.name"
                                            required autofocus autocomplete="name" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.name" />
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">

                                        <InputLabel for="email" value="Email" />
                                        <TextInput id="email" type="email" class="form-control" v-model="form.email"
                                            required autocomplete="username" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.email" />
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-4">

                                <div class="flex items-center justify-end mt-4 rigth">
                                    <PrimaryButton css-class="btn btn-primary float-right"
                                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        <span v-if="form.processing" class="spinner-border spinner-border-sm"
                                            role="status" aria-hidden="true"></span>
                                        Salvar
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <div class="col-lg-12">
                <div class="card mb-4 py-3 border-left-secondary">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Ativo</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(user, index) in users">
                                        <th>{{ user.id }}</th>
                                        <td>{{ user.name }}</td>
                                        <td>{{ user.email }}</td>
                                        <td>
                                            <span v-if="user.email_verified_at != null"
                                                class="btn-success btn-circle btn-sm">
                                                <i class="fas fa-check"></i>
                                            </span>
                                            <span v-if="user.email_verified_at == null"
                                                class="btn btn-warning btn-circle btn-sm" title="Não ativo">
                                                <i class="fas fa-times"></i>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- <Modal :key="index" :modal-title="'Confirmar Exclusão de ' + user.name"
                                                :ok-botton-callback="deleteRole" :ok-botton-callback-param="role.id"
                                                btn-class="btn btn-danger btn-icon-split">
                                                <template v-slot:button>
                                                    <span class="icon text-white-50">
                                                        <i class="fas fa-trash"></i>
                                                    </span>
                                                    <span class="text">Excluir</span>
                                                </template>
                                                <template v-slot:content>
                                                    Tem certeza que deseja apagar esse registro?
                                                </template>
                                            </Modal> -->

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
