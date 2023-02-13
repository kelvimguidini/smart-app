<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import Loader from '@/Components/Loader.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import { onMounted, ref } from 'vue';


const props = defineProps({
    users: Array,
    roles: Array,
    // userEdit: Array
});

const form = useForm({
    id: 0,
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms: false,
    roles: []
});

const formDelete = useForm({
    id: 0
});

const formRemoveRole = useForm({
    role_id: 0,
    user_id: 0
});

const userInEdition = ref(0);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
            userInEdition.value = 0;
        },
    });
};

const edit = (user) => {
    userInEdition.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.phone = user.phone;
    form.id = user.id;
    form.roles = [];
    user.roles.map(function (value, key) {
        form.roles.push(value.id);
    });
};

const isLoader = ref(false);

const deleteUser = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('user-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
        },
    });
};


const removeRole = (role_user) => {
    isLoader.value = true;
    formRemoveRole.role_id = role_user[0];
    formRemoveRole.user_id = role_user[1];
    formRemoveRole.delete(route('role-remove'), {
        onFinish: () => {
            isLoader.value = false;
            formRemoveRole.reset();
        },
    });
};

onMounted(() => {

    $('table').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json',
        },
    });


    $('#phone').mask('(00) 00000-0000');
});

</script>

<template>
    <AuthenticatedLayout>

        <Head title="Usuários" />
        <Loader v-bind:show="isLoader"></Loader>


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
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">

                                                <InputLabel for="name" value="Name" />
                                                <TextInput id="name" type="text" class="form-control"
                                                    v-model="form.name" required autofocus autocomplete="name" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.name" />
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">

                                                <InputLabel for="email" value="Email" />
                                                <TextInput id="email" type="email" class="form-control"
                                                    v-model="form.email" required autocomplete="username" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.email" />
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <InputLabel for="Phone" value="Telefone" />
                                                <TextInput id="phone" type="text" class="form-control"
                                                    v-model="form.phone" required autofocus autocomplete="phone" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.phone" />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <InputLabel for="role" value="Grupo de acesso:" />
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="form-check" v-for="(role, index) in  roles">
                                                    <input v-model="form.roles" class="form-check-input" type="checkbox"
                                                        :value="role.id" :id="role.name">
                                                    <label class="form-check-label" :for="role.name">
                                                        {{ role.name }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <InputError class="mt-2 text-danger" :message="form.errors.roles" />
                                    </div>

                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-4">

                                <PrimaryButton css-class="btn btn-primary float-right m-1"
                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status"
                                        aria-hidden="true"></span>
                                    Salvar
                                </PrimaryButton>

                                <PrimaryButton v-if="userInEdition > 0" css-class="btn btn-info float-right m-1"
                                    v-on:click="form.reset(); userInEdition = 0"
                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Novo
                                </PrimaryButton>

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
                                        <th scope="col">Telefone</th>
                                        <th scope="col">Ativo</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(user, index) in users"
                                        :class="{ 'table-info': userInEdition == user.id }">
                                        <th>{{ user.id }}</th>
                                        <td>{{ user.name }}</td>
                                        <td>{{ user.phone }}</td>
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
                                            <Modal :modal-title="'Permissões para ' + user.name"
                                                btn-class="btn btn-info btn-icon-split mr-2">
                                                <template v-slot:button>
                                                    <span class="icon text-white-50">
                                                        <i class="fas fa-list"></i>
                                                    </span>
                                                    <span class="text">Grupos de acesso</span>
                                                </template>
                                                <template v-slot:content>
                                                    <ul class="list-group">
                                                        <li v-for="(role, key) in user.roles"
                                                            class="list-group-item d-flex justify-content-between align-items-center">
                                                            {{ role.name }}

                                                            <Modal :key="'role_' + index"
                                                                :modal-title="'Confirmar remoção de grupo'"
                                                                :ok-botton-callback="removeRole"
                                                                :ok-botton-callback-param="[role.id, user.id]"
                                                                btn-class="btn btn-danger btn-circle btn-sm">
                                                                <template v-slot:button>
                                                                    <span v-if="formRemoveRole.processing"
                                                                        class="spinner-border spinner-border-sm"
                                                                        role="status" aria-hidden="true"></span>
                                                                    <i class="fas fa-trash"></i>
                                                                </template>
                                                                <template v-slot:content>
                                                                    Tem certeza que deseja remover o grupo
                                                                    <b>{{ role.name }}</b> de
                                                                    <b>{{ user.name }}</b>?
                                                                </template>
                                                            </Modal>
                                                        </li>
                                                    </ul>
                                                </template>
                                            </Modal>

                                            <button class="btn btn-primary btn-icon-split mr-2" v-on:click="edit(user)">
                                                <span class="icon text-white-50">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                                <span class="text">Editar</span>
                                            </button>

                                            <Modal :key="index" :modal-title="'Confirmar Exclusão de ' + user.name"
                                                :ok-botton-callback="deleteUser" :ok-botton-callback-param="user.id"
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
                                            </Modal>

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
