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
    customers: Array
});

const file = ref(null);
const previewImage = ref(null);

const previewImageHandled = ref(false);

const handleFileUpload = async () => {
    previewImageHandled.value = true;
    form.logo = file.value.files[0];
    const reader = new FileReader();
    reader.readAsDataURL(form.logo);
    reader.onload = e => {
        previewImage.value = e.target.result;
    };
}

const customerInEdition = ref(0);


const handleEditForm = (customer) => {

    customerInEdition.value = customer.id;
    form.id = customer.id;
    form.name = customer.name;
    form.logo = customer.logo;
    form.document = customer.document;
    form.phone = customer.phone;
    form.email = customer.email;
    form.color = customer.color
    form.responsibleAuthorizing = customer.responsibleAuthorizing;
    previewImage.value = '.' + customer.logo;
}

const form = useForm({
    id: 0,
    name: '',
    document: '',
    phone: '',
    email: '',
    responsibleAuthorizing: '',
    logo: null,
    color: '#000000'
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

    $('.phone').mask('(00) 00000-0000');

    var options = {
        onKeyPress: function (cpf_cnpj, e, field, options) {
            var masks = ['000.000.000-000', '00.000.000/0000-00'];
            var mask = cpf_cnpj.length > 14 ? masks[1] : masks[0];
            field.mask(mask, options);
        }
    };
    $('.document').mask('000.000.000-000', options);

});

const isLoader = ref(false);

const deleteCustomer = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('customer-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
        },
    });
};

const submit = () => {

    form.post(route('customer-save'), {
        onSuccess: () => {
            form.reset();
        },
    });
};


</script>

<template>
    <AuthenticatedLayout>
        <Loader v-bind:show="isLoader"></Loader>

        <Head title="Clientes" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Clientes</h1>
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
                                                <InputLabel for="document" value="CPF/CNPJ:" />
                                                <TextInput type="text" class="form-control document" v-model="form.document"
                                                    required autofocus autocomplete="document" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.document" />
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="phone" value="Telefone:" />
                                                <TextInput type="text" class="form-control phone" v-model="form.phone"
                                                    required autofocus autocomplete="phone" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.phone" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">

                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="email" value="E-mail:" />
                                                <TextInput type="text" class="form-control" v-model="form.email" required
                                                    autofocus autocomplete="email" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.email" />
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="responsibleAuthorizing"
                                                    value="Responsável pela autorização:" />
                                                <TextInput type="text" class="form-control"
                                                    v-model="form.responsibleAuthorizing" required autofocus
                                                    autocomplete="responsibleAuthorizing" />
                                                <InputError class="mt-2 text-danger"
                                                    :message="form.errors.responsibleAuthorizing" />
                                            </div>
                                        </div>
                                        <div class="col">

                                            <div class="form-group">
                                                <InputLabel for="logo" value="Logo:" />
                                                <div class="card">
                                                    <div class="card-body">
                                                        <input ref="file" @input="form.logo"
                                                            v-on:change="handleFileUpload()" type="file" />

                                                    </div>
                                                </div>
                                                <InputError class="mt-2 text-danger" :message="form.errors.logo" />
                                            </div>

                                            <div v-if="customerInEdition > 0" class="alert alert-warning " role="alert">
                                                <h4
                                                    class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Alteração da logo!
                                                </h4>
                                                <p>Ao clicar em salvar SEM carregar imagem a alteração dos dados do
                                                    cliente NÃO apagará a imagem</p>
                                            </div>

                                        </div>

                                        <div class="col">

                                            <div class="form-group">
                                                <InputLabel for="color" value="Cor:" />
                                                <div class="card">
                                                    <div class="card-body">
                                                        <input type="color" id="color" v-model="form.color">
                                                    </div>
                                                </div>
                                                <InputError class="mt-2 text-danger" :message="form.errors.logo" />
                                            </div>

                                            <div class="alert alert-warning " role="alert">
                                                <h4
                                                    class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Seleção da cor!
                                                </h4>
                                                <p>Selecione a cor predominante na marca desse cliente, essa cor será usada
                                                    na impressão da proposta!</p>
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
                                        <PrimaryButton v-if="customerInEdition > 0" css-class="btn btn-info float-right m-1"
                                            v-on:click="form.reset(); customerInEdition = 0; previewImage = null"
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
                                                <th scope="col" class="document">CPF/CNPJ</th>
                                                <th scope="col">Telefone</th>
                                                <th scope="col">E-mail</th>
                                                <th scope="col">Responsável pela autorização</th>
                                                <th scope="col">Cor</th>
                                                <th scope="col">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(customer, index) in customers"
                                                :class="{ 'table-info': customerInEdition == customer.id }">
                                                <th scope="row">{{ customer.id }}</th>
                                                <td>{{ customer.name }}</td>
                                                <td>{{ customer.document }}</td>
                                                <td>{{ customer.phone }}</td>
                                                <td>{{ customer.email }}</td>
                                                <td>{{ customer.responsibleAuthorizing }}</td>
                                                <td><span class="border p-1" :style="'background-color:' +
                                                    customer.color">&nbsp;</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-icon-split mr-2"
                                                        v-on:click="handleEditForm(customer)">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                        <span class="text">Editar</span>
                                                    </button>

                                                    <Modal :key="index"
                                                        :modal-title="'Confirmar Exclusão de ' + customer.name"
                                                        :ok-botton-callback="deleteCustomer"
                                                        :ok-botton-callback-param="customer.id"
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
                        <h6 class="m-0 font-weight-bold text-primary">Logo</h6>
                    </div>
                    <div class="card-body">
                        <img :src="previewImage" style="padding-right: 0;padding-left: 0;" class="rounded col-12"
                            :class="{ 'border border-danger': previewImageHandled }" />
                    </div>
                </div>

            </div>

        </div>



    </AuthenticatedLayout>
</template>
