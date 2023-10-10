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
    hotels: Array,
    cities: Array,
    aptos: Array,
    categories: Array,
});

const inEdition = ref(0);

const form = useForm({
    id: 0,
    name: '',
    city: '',
    contact: '',
    phone: '',
    email: '',
    aptos: [],
    categories: [],
    national: false,
    service_percent: null,
    iva_percent: null,
    iss_percent: null,
});

const formDelete = useForm({
    id: 0
});

onMounted(() => {

    $('#city').select2({
        theme: "bootstrap4",
        language: "pt-Br"
    }).on('select2:select', (e) => {
        form.city = e.params.data.id;
    });

    $('table').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json',
        },
    });
});

const isLoader = ref(false);


const edit = (hotel) => {

    if (hotel != null) {
        form.reset();

        inEdition.value = hotel.id;
        form.id = hotel.id;
        form.name = hotel.name;
        form.contact = hotel.contact;
        form.phone = hotel.phone;
        form.email = hotel.email;
        form.national = hotel.national == true || hotel.national == 1;
        form.city = hotel.city;
        form.iss_percent = hotel.iss_percent;
        form.service_percent = hotel.service_percent;
        form.iva_percent = hotel.iva_percent;

        $('#city').val(hotel.city).trigger('change');
        $('.phone').val(form.phone).trigger('keyup');
    }
};

const newItem = (() => {
    form.reset();
    $('#city').val('').trigger('change');
    inEdition.value = 0;
});

const submit = () => {
    isLoader.value = true;
    form.post(route('provider-transport-save'), {
        onSuccess: () => {
            newItem();
            isLoader.value = false;
        },
    });
};

const deleteHotel = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('provider-transport-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Loader v-bind:show="isLoader"></Loader>

        <Head title="Fornecedor" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Fornecedor</h1>
            </div>
        </template>

        <div class="row">
            <div class="col-lg-12">

                <div class="card mb-4 py-3 border-left-primary">
                    <div class="card-body">
                        <form @submit.prevent="submit()">

                            <div class="row">
                                <div class="col">

                                    <div class="form-group">
                                        <InputLabel for="name" value="Nome:" />
                                        <TextInput type="text" class="form-control" v-model="form.name" required autofocus
                                            autocomplete="name" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.name" />
                                    </div>

                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <InputLabel for="city" value="Cidade:" />

                                        <select class="form-control" id="city" :required="required">
                                            <option>.::Selecione::.</option>
                                            <option v-for="(option, index) in cities" :selected="option.name == form.city"
                                                :value="option.name">
                                                {{ option.name }} - {{ option.uf }}
                                            </option>
                                        </select>

                                        <InputError class="mt-2 text-danger" :message="form.errors.city" />
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <InputLabel for="contact" value="Contato:" />
                                        <TextInput type="text" class="form-control" v-model="form.contact" required
                                            autofocus autocomplete="contact" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.contact" />
                                    </div>

                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <InputLabel for="phone" value="Telefone:" />
                                        <TextInput id="phone" type="text" class="form-control phone" v-model="form.phone"
                                            required autofocus autocomplete="phone" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.phone" />
                                    </div>

                                </div>
                            </div>

                            <div class="row">

                                <div class="col-3">
                                    <div class="form-group">
                                        <InputLabel for="email" value="E-mail:" />
                                        <TextInput type="email" class="form-control" v-model="form.email" required autofocus
                                            autocomplete="email" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.email" />
                                    </div>
                                </div>

                                <div class="col-2">
                                    <div class="form-group">
                                        <InputLabel for="iss_percent" value="ISS:" />
                                        <TextInput type="number" class="form-control percent" v-model="form.iss_percent"
                                            required autofocus min="0" step=".1" autocomplete="iss_percent" />
                                    </div>
                                </div>

                                <div class="col-2">
                                    <div class="form-group">
                                        <InputLabel for="service_percent" value="Serviço:" />
                                        <TextInput type="number" class="form-control percent" v-model="form.service_percent"
                                            required autofocus min="0" step=".1" autocomplete="service_percent" />
                                    </div>
                                </div>

                                <div class="col-2">
                                    <div class="form-group">
                                        <InputLabel for="iva_percent" value="IVA:" />
                                        <TextInput type="number" class="form-control percent" v-model="form.iva_percent"
                                            required autofocus min="0" step=".1" autocomplete="iva_percent" />
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="form-group">
                                        <InputLabel for="national" value=" " />

                                        <div class="card">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" v-model="form.national" :value="true"
                                                        type="radio" id="autoSizingCheck1">
                                                    <label class="form-check-label" for="autoSizingCheck1">
                                                        Fornecedor Nacional
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" v-model="form.national" :value="false"
                                                        type="radio" id="autoSizingCheck">
                                                    <label class="form-check-label" for="autoSizingCheck">
                                                        Fornecedor Interacional
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4 rigth">
                                <PrimaryButton css-class="btn btn-primary float-right m-1"
                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status"
                                        aria-hidden="true"></span>
                                    Salvar
                                </PrimaryButton>
                                <PrimaryButton v-if="inEdition > 0" css-class="btn btn-info float-right m-1"
                                    v-on:click="newItem()" :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing">
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
                            <table class="table table-striped hotels-table" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Cidade</th>
                                        <th scope="col">Contato</th>
                                        <th scope="col">Telefone</th>
                                        <th scope="col">E-mail</th>
                                        <th scope="col">IVA</th>
                                        <th scope="col">ISS</th>
                                        <th scope="col">Serviço</th>
                                        <th scope="col">Tipo</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(hotel, index) in hotels" :class="{ 'table-info': inEdition == hotel.id }">
                                        <th scope="row">{{ hotel.id }}</th>
                                        <td>{{ hotel.name }}</td>
                                        <td>{{ hotel.city }}</td>
                                        <td>{{ hotel.contact }}</td>
                                        <td class="phone">{{ hotel.phone }}</td>
                                        <td>{{ hotel.email }}</td>
                                        <td>{{ hotel.iva_percent }}</td>
                                        <td>{{ hotel.iss_percent }}</td>
                                        <td>{{ hotel.service_percent }}</td>
                                        <td>{{ hotel.national ? "Nacional" : "Internacional" }}</td>
                                        <td>
                                            <button class="btn btn-info btn-icon-split mr-2" v-on:click="edit(hotel)">
                                                <span class="icon text-white-50">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                                <span class="text">Editar</span>
                                            </button>

                                            <Modal :key="index" :modal-title="'Confirmar Exclusão de ' + hotel.name"
                                                :ok-botton-callback="deleteHotel" :ok-botton-callback-param="hotel.id"
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
