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
    currencies: Array
});

const inEdition = ref(0);

const form = useForm({
    id: 0,
    name: '',
    symbol: '',
    sigla: ''
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

const deleteCurrency = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('currency-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
        },
    });
};

const edit = (currency) => {
    inEdition.value = currency.id;
    form.name = currency.name;
    form.sigla = currency.sigla;
    form.symbol = currency.symbol;
    form.id = currency.id;
};

const submit = () => {
    form.post(route('currency-save'), {
        onSuccess: () => {
            form.reset();
            inEdition.value = 0;
        },
    });
};

const activate = (id) => {
    isLoader.value = true;
    form.put(route('currencies-activate', id), {
        onFinish: () => {
            isLoader.value = false;
        },
    });
};

const deactivate = (id) => {
    isLoader.value = true;
    form.put(route('currencies-deactivate', id), {
        onFinish: () => {
            isLoader.value = false;
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Loader v-bind:show="isLoader"></Loader>

        <Head title="Moeda" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Moeda</h1>
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
                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="name" value="Nome:" />
                                                <TextInput type="text" class="form-control" v-model="form.name" required
                                                    autofocus autocomplete="name" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.name" />
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="sigla" value="Sigla:" />
                                                <TextInput type="text" class="form-control" v-model="form.sigla" required
                                                    autofocus autocomplete="symsiglabol" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.sigla" />
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="symbol" value="Símbolo:" />
                                                <TextInput type="text" class="form-control" v-model="form.symbol" required
                                                    autofocus autocomplete="symbol" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.symbol" />
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
                                                <th scope="col">Sigla</th>

                                                <th scope="col">Símbolo</th>
                                                <th scope="col">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(currency, index) in currencies"
                                                :class="{ 'table-info': inEdition == currency.id }">
                                                <th scope="row">{{ currency.id }}</th>
                                                <td>{{ currency.name }}</td>

                                                <td>{{ currency.sigla }}</td>
                                                <td>{{ currency.symbol }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info btn-icon-split mr-2"
                                                        v-on:click="edit(currency)">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                        <span class="text">Editar</span>
                                                    </button>

                                                    <Modal :key="index"
                                                        :modal-title="'Confirmar Exclusão de ' + currency.name"
                                                        :ok-botton-callback="deleteCurrency"
                                                        :ok-botton-callback-param="currency.id"
                                                        btn-class="btn  btn-sm btn-danger btn-icon-split mr-2">
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


                                                    <button v-if="!currency.active" class="btn btn-sm btn-success btn-icon-split mr-2"
                                                        v-on:click="activate(currency.id)">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                        <span class="text">Ativar</span>
                                                    </button>

                                                    <button v-if="currency.active" class="btn btn-sm btn-warning btn-icon-split mr-2"
                                                        v-on:click="deactivate(currency.id)">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-ban"></i>
                                                        </span>
                                                        <span class="text">Inativar</span>
                                                    </button>

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
