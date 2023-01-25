<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import Loader from '@/Components/Loader.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';


const inEdition = ref(0);

const props = defineProps({
    roles: Array,
});

const form = useForm({
    id: 0,
    name: '',
    permissions: []
});

const edit = (role) => {
    inEdition.value = role.id;
    form.name = role.name;
    form.permissions = [];
    form.id = role.id;
    role.permissions.map(function (value, key) {
        form.permissions.push(value.name);
    });
};

const formDelete = useForm({
    id: 0
});

const formRemovePermission = useForm({
    role_id: 0,
    permission_id: 0
});

onMounted(() => {

    $('table').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json',
        },
    });
});

const isLoader = ref(false);

const deleteRole = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('role-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
        },
    });
};


const removePermission = (role_permisson) => {
    isLoader.value = true;
    formRemovePermission.role_id = role_permisson[0];
    formRemovePermission.permission_id = role_permisson[1];
    formRemovePermission.delete(route('permission-remove'), {
        onFinish: () => {
            isLoader.value = false;
            formRemovePermission.reset();
        },
    });
};



const submit = () => {
    form.post(route('role-save'), {
        onSuccess: () => {

            inEdition.value = 0;
            form.reset();
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Loader v-bind:show="isLoader"></Loader>

        <Head title="Grupo" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Grupo de acesso</h1>
            </div>
        </template>
        <div class="row">
            <div class="col-lg-9">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4 py-3 border-left-primary">
                            <div class="card-body">

                                <form @submit.prevent="submit">

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <InputLabel for="name" value="Nome:" />
                                                <TextInput type="text" class="form-control" v-model="form.name" required
                                                    autofocus autocomplete="name" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.name" />
                                            </div>
                                        </div>
                                        <div class="col-lg-6">

                                            <div class="form-group">
                                                <InputLabel for="permissions" value="Permissão para:" />
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="form-check"
                                                            v-for="(option, index) in  $page.props.permissionList">
                                                            <input v-model="form.permissions" class="form-check-input"
                                                                type="checkbox" :value="option.name" :id="option.name">
                                                            <label class="form-check-label" :for="option.name">
                                                                {{ option.title }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <InputError class="mt-2 text-danger"
                                                    :message="form.errors.permissions" />
                                            </div>

                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end mt-4 rigth">
                                        <PrimaryButton css-class="btn btn-primary float-right"
                                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            <span v-if="form.processing" class="spinner-border spinner-border-sm"
                                                role="status" aria-hidden="true"></span>
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
                                <div class="table-responsive">
                                    <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Nome</th>
                                                <th scope="col">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(role, index) in roles"
                                                :class="{ 'table-info': inEdition == role.id }">
                                                <th scope="row">{{ role.id }}</th>
                                                <td>{{ role.name }}</td>
                                                <td>
                                                    <button class="btn btn-info btn-icon-split mr-2"
                                                        v-on:click="edit(role)">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                        <span class="text">Editar</span>
                                                    </button>
                                                    <Modal :modal-title="'Permissões para ' + role.name"
                                                        btn-class="btn btn-info btn-icon-split mr-2">
                                                        <template v-slot:button>
                                                            <span class="icon text-white-50">
                                                                <i class="fas fa-list"></i>
                                                            </span>
                                                            <span class="text">Permissões</span>
                                                        </template>
                                                        <template v-slot:content>
                                                            <ul class="list-group">
                                                                <li v-for="(permission, key) in role.permissions"
                                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                                    {{ permission.title }}

                                                                    <Modal :key="'permission_' + index"
                                                                        :modal-title="'Confirmar remoção de permissão'"
                                                                        :ok-botton-callback="removePermission"
                                                                        :ok-botton-callback-param="[role.id, permission.id]"
                                                                        btn-class="btn btn-danger btn-circle btn-sm">
                                                                        <template v-slot:button>
                                                                            <span v-if="formRemovePermission.processing"
                                                                                class="spinner-border spinner-border-sm"
                                                                                role="status" aria-hidden="true"></span>
                                                                            <i class="fas fa-trash"></i>
                                                                        </template>
                                                                        <template v-slot:content>
                                                                            Tem certeza que deseja remover a permissão
                                                                            <b>{{ permission.title }}</b> do grupo
                                                                            <b>{{ role.name }}</b>?
                                                                        </template>
                                                                    </Modal>
                                                                </li>
                                                            </ul>
                                                        </template>
                                                    </Modal>


                                                    <Modal :key="index"
                                                        :modal-title="'Confirmar Exclusão de ' + role.name"
                                                        :ok-botton-callback="deleteRole"
                                                        :ok-botton-callback-param="role.id"
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
            </div>


            <div class="col-lg-3">
                <!-- Basic Card Example -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Permissões</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item" v-for="(p, index) in $page.props.permissionList">{{ p.title }}
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>



    </AuthenticatedLayout>
</template>
