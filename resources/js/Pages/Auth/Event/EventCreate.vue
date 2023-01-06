<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Loader from '@/Components/Loader.vue';
// import SelectC from '@/Components/SelectC.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { ref, onMounted } from 'vue';
import Datepicker from 'vue3-datepicker';
import { ptBR } from 'date-fns/locale';
import '../../../vendor/select2/select2.min.css';
import '../../../vendor/select2/select2-bootstrap.css';
import '../../../vendor/select2/select2.min.js';

const props = defineProps({
    'crds': Array,
    'customers': Array,
    'users': Array,
    'event': Object
});

const form = useForm({
    id: 0,
    name: '',
    customer: '',
    code: '',
    requester: '',
    sector: '',
    paxBase: '',
    cc: '',
    date: '',
    crd_id: '',
    hotel_operator: '',
    air_operator: '',
    land_operator: '',
});

const isLoader = ref(false);

const edit = (event) => {
    form.id = event.id;
    form.name = event.name;
    form.customer = event.customer_id;
    form.code = event.code;
    form.requester = event.requester;
    form.sector = event.sector;
    form.paxBase = event.pax_base;
    form.cc = event.cost_center;
    form.date = new Date(event.date);
    form.crd_id = event.crd_id;
    form.hotel_operator = event.hotel_operator;
    form.air_operator = event.air_operator;
    form.land_operator = event.land_operator;
};

onMounted(() => {

    edit(props.event);
    $('#customer').select2({
        theme: "bootstrap4"
    }).on('select2:select', (e) => {
        form.customer = e.params.data.id;
    });

    $('#crd_id').select2({
        theme: "bootstrap4"
    }).on('select2:select', (e) => {
        form.crd_id = e.params.data.id;
    });

    $('#hotel_operator').select2({
        theme: "bootstrap4"
    }).on('select2:select', (e) => {
        form.hotel_operator = e.params.data.id;
    });

    $('#air_operator').select2({
        theme: "bootstrap4"
    }).on('select2:select', (e) => {
        form.air_operator = e.params.data.id;
    });

    $('#land_operator').select2({
        theme: "bootstrap4"
    }).on('select2:select', (e) => {
        form.land_operator = e.params.data.id;
    });

});

const submit = () => {
    form.post(route('event-save'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Loader v-bind:show="isLoader"></Loader>

        <Head title="Cadastro inicial" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Cadastro inicial</h1>
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
                                        <div class="col-lg-4">

                                            <div class="form-group">
                                                <InputLabel for="name" value="Nome do Evento:" />
                                                <TextInput type="text" class="form-control" v-model="form.name" required
                                                    autofocus autocomplete="name" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.name" />
                                            </div>
                                            <div class="form-group">
                                                <InputLabel for="code" value="Código do Evento:" />
                                                <TextInput type="text" class="form-control" v-model="form.code" required
                                                    autofocus autocomplete="code" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.code" />
                                            </div>

                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <InputLabel for="requester" value="Solicitante:" />
                                                <TextInput type="text" class="form-control" v-model="form.requester"
                                                    required autofocus autocomplete="requester" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.requester" />
                                            </div>

                                            <div class="form-group">
                                                <InputLabel for="customer" value="Empresa:" />

                                                <select class="form-control" id="customer" :required="required">
                                                    <option>.::Selecione::.</option>
                                                    <option v-for="(option, index) in customers"
                                                        :selected="option.id == form.customer" :value="option.id">
                                                        {{ option.name }}
                                                    </option>
                                                </select>

                                                <InputError class="mt-2 text-danger" :message="form.errors.customer" />
                                            </div>

                                            <div class="form-group">
                                                <InputLabel for="sector" value="Setor:" />
                                                <TextInput type="text" class="form-control" v-model="form.sector"
                                                    required autofocus autocomplete="sector" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.sector" />
                                            </div>

                                            <div class="form-group">
                                                <InputLabel for="paxBase" value="Base de Pax:" />
                                                <TextInput type="text" class="form-control" v-model="form.paxBase"
                                                    required autofocus autocomplete="paxBase" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.paxBase" />
                                            </div>

                                            <div class="form-group">
                                                <InputLabel for="cc" value="Centro de Custo:" />
                                                <TextInput type="text" class="form-control" v-model="form.cc" required
                                                    autofocus autocomplete="cc" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.cc" />
                                            </div>
                                        </div>
                                        <div class="col-lg-4">

                                            <div class="form-group">
                                                <InputLabel for="date" value="Data do Evento:" />
                                                <datepicker v-model="form.date" class="form-control" :locale="ptBR"
                                                    inputFormat="dd/MM/yyyy" weekdayFormat="EEEEEE" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.date" />
                                            </div>

                                            <div class="form-group">
                                                <InputLabel for="CRD" value="CRD:" />

                                                <select class="form-control" id="crd_id" :required="required">
                                                    <option>.::Selecione::.</option>
                                                    <option v-for="(option, index) in crds"
                                                        :selected="option.id == form.crd_id" :value="option.id">
                                                        {{ option.name }}
                                                    </option>
                                                </select>

                                                <InputError class="mt-2 text-danger" :message="form.errors.crd_id" />
                                            </div>

                                            <div class="form-group">
                                                <InputLabel for="hotel_operator" value="Operador - Hotel:" />
                                                <select class="form-control" id="hotel_operator" :required="required">
                                                    <option>.::Selecione::.</option>
                                                    <option v-for="(option, index) in users"
                                                        :selected="option.id == form.hotel_operator" :value="option.id">
                                                        {{ option.name }}
                                                    </option>
                                                </select>
                                                <InputError class="mt-2 text-danger"
                                                    :message="form.errors.hotel_operator" />
                                            </div>
                                            <div class="form-group">
                                                <InputLabel for="land_operator" value="Operador - Terrestre:" />
                                                <select class="form-control" id="land_operator" :required="required">
                                                    <option>.::Selecione::.</option>
                                                    <option v-for="(option, index) in users"
                                                        :selected="option.id == form.land_operator" :value="option.id">
                                                        {{ option.name }}
                                                    </option>
                                                </select>
                                                <InputError class="mt-2 text-danger"
                                                    :message="form.errors.land_operator" />
                                            </div>
                                            <div class="form-group">
                                                <InputLabel for="air_operator" value="Operador - Aéreo:" />
                                                <select class="form-control" id="air_operator" :required="required">
                                                    <option>.::Selecione::.</option>
                                                    <option v-for="(option, index) in users"
                                                        :selected="option.id == form.air_operator" :value="option.id">
                                                        {{ option.name }}
                                                    </option>
                                                </select>
                                                <InputError class="mt-2 text-danger"
                                                    :message="form.errors.air_operator" />
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
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
