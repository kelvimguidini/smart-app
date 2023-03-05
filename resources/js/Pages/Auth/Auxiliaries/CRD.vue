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
    crds: Array,
    customers: Array
});

const crdInEdition = ref(0);

const form = useForm({
    id: 0,
    name: '',
    number: '',
    customer_id: ''
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

    var options = {
        onKeyPress: function (crd, e, field, options) {
            var masks = ['00.000.00000', '00.000.0000.00000'];
            var mask = crd.length > 11 ? masks[1] : masks[0];
            field.mask(mask, options);
        }
    };
    $('.number').mask('00.000.00000', options);

    $('#customer_id').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        form.customer_id = e.params.data.id;
    });
});

const isLoader = ref(false);

const deleteCRD = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('crd-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
        },
    });
};

const edit = (crd) => {
    crdInEdition.value = crd.id;
    form.name = crd.name;
    form.number = crd.number;
    form.customer_id = crd.customer_id;
    form.id = crd.id;

    $('#customer_id').val(crd.customer_id).trigger('change');
};

const submit = () => {
    form.post(route('crd-save'), {
        onSuccess: () => {
            crdInEdition.value = 0;
            form.reset();
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Loader v-bind:show="isLoader"></Loader>

        <Head title="CRD's" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">CRD's</h1>
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
                                                <InputLabel for="number" value="Número:" />
                                                <TextInput type="text" class="form-control number" v-model="form.number"
                                                    required autofocus autocomplete="number" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.number" />
                                            </div>

                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="customer_id" value="Cliente:" />

                                                <select class="form-control" id="customer_id" :required="required">
                                                    <option>.::Selecione::.</option>
                                                    <option v-for="(option, index) in customers"
                                                        :selected="option.id == form.customer_id" :value="option.id">
                                                        {{ option.name }}
                                                    </option>
                                                </select>

                                                <InputError class="mt-2 text-danger" :message="form.errors.crd_id" />
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
                                        <PrimaryButton v-if="crdInEdition > 0" css-class="btn btn-info float-right m-1"
                                            v-on:click="form.reset(); crdInEdition = 0;"
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
                                                <th scope="col">Número</th>
                                                <th scope="col">Cliente</th>
                                                <th scope="col">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(crd, index) in crds"
                                                :class="{ 'table-info': crdInEdition == crd.id }">
                                                <th scope="row">{{ crd.id }}</th>
                                                <td>{{ crd.name }}</td>
                                                <td>{{ crd.number }}</td>
                                                <td>{{ crd.customer.name }}</td>
                                                <td>
                                                    <button class="btn btn-info btn-icon-split mr-2" v-on:click="edit(crd)">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                        <span class="text">Editar</span>
                                                    </button>

                                                    <Modal :key="index" :modal-title="'Confirmar Exclusão de ' + crd.name"
                                                        :ok-botton-callback="deleteCRD" :ok-botton-callback-param="crd.id"
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
