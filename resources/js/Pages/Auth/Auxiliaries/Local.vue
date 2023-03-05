<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import Loader from '@/Components/Loader.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { onMounted, ref } from 'vue';


const props = defineProps({
    locals: Array
});

const inEdition = ref(0);

const form = useForm({
    id: 0,
    name: '',
});

const formDelete = useForm({
    id: 0
});


onMounted(() => {

    $('table').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json',
        },
    });
});

const isLoader = ref(false);

const deleteLocals = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('local-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
        },
    });
};

const edit = (local) => {
    inEdition.value = local.id;
    form.name = local.name;
    form.id = local.id;
};

const submit = () => {
    form.post(route('local-save'), {
        onSuccess: () => {
            form.reset();
            inEdition.value = 0;
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Loader v-bind:show="isLoader"></Loader>

        <Head title="Local" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Local</h1>
            </div>
        </template>
        <div class="row">
            <div class="col-lg-12">

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
                                    </div>
                                    <div class="flex items-center justify-end mt-4 rigth">
                                        <PrimaryButton css-class="btn btn-primary float-right m-1"
                                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            <span v-if="form.processing" class="spinner-border spinner-border-sm"
                                                role="status" aria-hidden="true"></span>
                                            Salvar
                                        </PrimaryButton>
                                        <PrimaryButton v-if="inEdition > 0" css-class="btn btn-info float-right m-1"
                                            v-on:click="form.reset(); inEdition = 0;"
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
                                                <th scope="col">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(local, index) in locals"
                                                :class="{ 'table-info': inEdition == local.id }">
                                                <th scope="row">{{ local.id }}</th>
                                                <td>{{ local.name }}</td>
                                                <td>
                                                    <button class="btn btn-info btn-icon-split mr-2"
                                                        v-on:click="edit(local)">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                        <span class="text">Editar</span>
                                                    </button>

                                                    <Modal :key="index" :modal-title="'Confirmar Exclusão de ' + local.name"
                                                        :ok-botton-callback="deleteLocal"
                                                        :ok-botton-callback-param="local.id"
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
        </div>

    </AuthenticatedLayout>
</template>
