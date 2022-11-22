<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';

const form = useForm({
    id: 0,
    name: '',
    permissions: []
});

const permission = [
    { name: "user_create", "title": "Criar Usuário" },
    { name: "user_update", "title": "Editar Usuário" },
    { name: "user_delete", "title": "Remover Usuário" },
    { name: "role_admin", "title": "Administrar Perfil" }
];

const submit = () => {
    form.post(route('role-save'));
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Perfil" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Perfil</h1>
            </div>
        </template>

        <div class="row">
            <div class="col-lg-9">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4 py-3 border-left-primary">
                            <div class="card-body">

                                <form @submit.prevent="submit">

                                    <div class="form-group">
                                        <InputLabel for="name" value="Nome:" />
                                        <TextInput type="text" class="form-control" v-model="form.name" required
                                            autofocus autocomplete="name" />
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>
                                    <div class="form-group">
                                        <InputLabel for="permissions" value="Permissão para:" />
                                        <select multiple class="form-control" v-model="form.permissions">
                                            <option v-for="(option, index) in permission" :key="index"
                                                :value="option.name">{{ option.title }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.permissions" />
                                    </div>

                                    <div class="flex items-center justify-end mt-4 rigth">
                                        <PrimaryButton css-class="btn btn-primary float-right"
                                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            Salvar
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-12">
                        <div class="card mb-4 py-3 border-left-secondary">
                            <div class="card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nome</th>
                                            <th scope="col">Permissões</th>
                                            <th scope="col">Usuários</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-lg-3">
                <!-- Basic Card Example -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Permissões</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item list-group-item-primary">Administração Usuários</li>
                            <li class="list-group-item">Administrar Perfil</li>
                            <li class="list-group-item">Criar Usuário</li>
                            <li class="list-group-item">Editar Usuário</li>
                            <li class="list-group-item">Remover Usuário</li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>



    </AuthenticatedLayout>
</template>
