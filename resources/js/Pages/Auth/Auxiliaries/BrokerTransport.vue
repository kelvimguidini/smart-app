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
    brokers: Array,
    cities: Array
});

const inEdition = ref(0);

const form = useForm({
    id: 0,
    name: '',
    city: '',
    contact: '',
    phone: '',
    email: '',
    national: false,
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

    $('.phone').mask('(00) 00000-0000');

    $('table').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json',
        },
    });
});

const isLoader = ref(false);

const deleteBroker = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('broker-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
        },
    });
};

const edit = (broker) => {
    inEdition.value = broker.id;
    form.name = broker.name;
    form.id = broker.id;
    form.contact = broker.contact;
    form.phone = broker.phone;
    form.email = broker.email;
    form.national = broker.national == true || broker.national == 1;
    form.city = broker.city_id;

    $('#city').val(broker.city_id).trigger('change');
    $('.phone').val(form.phone).trigger('keyup');
};

const newItem = (() => {
    form.reset();
    $('#city').val('').trigger('change');
    inEdition.value = 0;
});

const submit = () => {
    form.post(route('broker-save'), {
        onSuccess: () => {
            newItem();

            isLoader.value = false;
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Loader v-bind:show="isLoader"></Loader>

        <Head title="Broker Transporte" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Broker Transporte</h1>
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
                                                <InputLabel for="city" value="Cidade:" />

                                                <select class="form-control" id="city" :required="required">
                                                    <option>.::Selecione::.</option>
                                                    <option v-for="(option, index) in cities" :value="option.id">
                                                        {{ option.name }} - {{ option.states ? option.states :
                                                            option.country }}
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
                                                <TextInput id="phone" type="text" class="form-control phone"
                                                    v-model="form.phone" required autofocus autocomplete="phone" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.phone" />
                                            </div>

                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="col-3">
                                            <div class="form-group">
                                                <InputLabel for="email" value="E-mail:" />
                                                <TextInput type="email" class="form-control" v-model="form.email" required
                                                    autofocus autocomplete="email" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.email" />
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="national" value=" " />

                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="form-check">
                                                            <input class="form-check-input" v-model="form.national"
                                                                :value="true" type="radio" id="autoSizingCheck1">
                                                            <label class="form-check-label" for="autoSizingCheck1">
                                                                Broker Nacional
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" v-model="form.national"
                                                                :value="false" type="radio" id="autoSizingCheck">
                                                            <label class="form-check-label" for="autoSizingCheck">
                                                                Broker Interacional
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
                                                <th scope="col">Cidade</th>
                                                <th scope="col">Contato</th>
                                                <th scope="col">Telefone</th>
                                                <th scope="col">E-mail</th>
                                                <th scope="col">Tipo</th>
                                                <th scope="col">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(broker, index) in brokers"
                                                :class="{ 'table-info': inEdition == broker.id }">
                                                <th scope="row">{{ broker.id }}</th>
                                                <td>{{ broker.name }}</td>
                                                <td>{{ broker.city?.name || ' - ' }} - {{ broker.city?.states ?
                                                    broker.city.states :
                                                    broker.city?.country || ' - ' }}</td>
                                                <td>{{ broker.contact }}</td>
                                                <td class="phone">{{ broker.phone }}</td>
                                                <td>{{ broker.email }}</td>
                                                <td>{{ broker.national ? "Nacional" : "Internacional" }}</td>
                                                <td>
                                                    <button class="btn btn-info btn-icon-split mr-2"
                                                        v-on:click="edit(broker)">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                        <span class="text">Editar</span>
                                                    </button>

                                                    <Modal :key="index"
                                                        :modal-title="'Confirmar Exclusão de ' + broker.name"
                                                        :ok-botton-callback="deleteBroker"
                                                        :ok-botton-callback-param="broker.id"
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
