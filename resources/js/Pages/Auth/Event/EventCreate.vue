<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Loader from '@/Components/Loader.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { ref, onMounted } from 'vue';
import Datepicker from 'vue3-datepicker';
import { ptBR } from 'date-fns/locale';
import ListHotelFull from '@/Components/ListHotelFull.vue';
import FormProvider from '@/Components/FormProvider.vue';
import FormProviderOpt from '@/Components/FormProviderOpt.vue';


const props = defineProps({
    crds: {
        type: Array,
        default: [],
    },
    customers: {
        type: Array,
        default: [],
    },
    users: {
        type: Array,
        default: [],
    },
    event: {
        type: Object,
        default: {},
    },
    hotels: {
        type: Array,
        default: [],
    },
    eventHotels: {
        type: Array,
        default: [],
    },
    eventHotel: {
        type: Object,
        default: {},
    },
    tab: {
        default: 0
    },
    brokers: {
        type: Array,
        default: [],
    },
    currencies: {
        type: Array,
        default: [],
    },
    regimes: {
        type: Array,
        default: [],
    },
    purposes: {
        type: Array,
        default: [],
    },
    catsHotel: {
        type: Array,
        default: [],
    },
    aptosHotel: {
        type: Array,
        default: [],
    }
});

const formProviderOptRef = ref(null);

const hotelSelected = ref(0);

const edit = (event) => {
    if (event != null) {
        form.id = event.id;
        form.name = event.name;
        form.customer = event.customer_id;
        form.code = event.code;
        form.requester = event.requester;
        form.sector = event.sector;
        form.paxBase = event.pax_base;
        form.cc = event.cost_center;
        form.date = new Date(event.date);
        form.date_final = new Date(event.date_final);
        form.crd_id = event.crd_id;
        form.hotel_operator = event.hotel_operator;
        form.air_operator = event.air_operator;
        form.land_operator = event.land_operator;

        props.crds = props.crds.filter((v, i) => { return v.customer.id == form.customer });

        $('#customer').val(event.customer_id).trigger('change');
        $('#crd_id').val(event.crd_id).trigger('change');
        $('#hotel_operator').val(event.hotel_operator).trigger('change');
        $('#air_operator').val(event.air_operator).trigger('change');
        $('#land_operator').val(event.land_operator).trigger('change');
    }
};

const editOpt = (opt) => {
    if (formProviderOptRef.value) {
        formProviderOptRef.value.editOpt(opt);
    }
};

const mount = () => {
    edit(props.event);

    if ($('#tabs').hasClass('ui-tabs')) {
        $("#tabs").tabs("destroy");
    }
    $("#tabs").tabs({ active: props.tab });

    if ($('#tabs-hotel').hasClass('ui-tabs')) {
        $("#tabs-hotel").tabs("destroy");
    }
    $("#tabs-hotel").tabs();

    if ($('#tabs-aandb').hasClass('ui-tabs')) {
        $("#tabs-aandb").tabs("destroy");
    }
    $("#tabs-aandb").tabs();
}

onMounted(() => {
    //Basico
    $('#customer').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        form.customer = e.params.data.id;
        props.crds = props.crds.filter((v, i) => { return v.customer.id == form.customer });
    });

    $('#crd_id').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        form.crd_id = e.params.data.id;
    });

    $('#hotel_operator').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        form.hotel_operator = e.params.data.id;
    });

    $('#air_operator').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        form.air_operator = e.params.data.id;
    });

    $('#land_operator').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        form.land_operator = e.params.data.id;
    });
    //Basico - Fim
    mount();

    let symbol = 'R$ ';
    if (props.eventHotel != null) {
        symbol = props.eventHotel.currency.symbol + ' ';
    }
    $('.money').maskMoney({ prefix: symbol, allowNegative: false, thousands: '.', decimal: ',', affixesStay: true });


});


const submit = () => {

    form.post(route('event-save'), {
        onSuccess: () => {
            mount();
            $('#tabs').tabs({ active: 1 });
        }
    });
};

//FORMS
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
    date_final: '',
    crd_id: '',
    hotel_operator: '',
    air_operator: '',
    land_operator: '',
});

const formDelete = useForm({
    id: 0
});
//FIM FORMS

//VARIAVEIS
const isLoader = ref(false);
//FIM VARIAVEIS

//FUNÇÕES HOTEL
const selectHotel = (id) => {
    var hotel = props.hotels.filter((item) => { return item.id == id })[0] || null;

    props.catsHotel = hotel.categories;
    props.aptosHotel = hotel.aptos;

    hotelSelected.value = hotel.id;
}

const deleteOpt = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('opt-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
            mount();
        },
    });
};

const duplicate = (opt) => {
    if (formProviderOptRef.value) {
        formProviderOptRef.value.duplicate(opt);
    }
}

//FIM FUNÇÕES HOTEL

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

        <div id="tabs">

            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="#basic">Basico</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                        v-bind:class="{ 'disabled': event == null || !$page.props.auth.permissions.some((p) => p.name === 'hotel_operator' || p.name === 'event_admin') }"
                        href="#hotel">Hotel</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                        v-bind:class="{ 'disabled': event == null || !$page.props.auth.permissions.some((p) => p.name === 'land_operator' || p.name === 'event_admin') }"
                        href="#aandb">A&B</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                        v-bind:class="{ 'disabled': event == null || !$page.props.auth.permissions.some((p) => p.name === 'land_operator' || p.name === 'event_admin') }"
                        href="#hall">Salões</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                        v-bind:class="{ 'disabled': event == null || !$page.props.auth.permissions.some((p) => p.name === 'land_operator' || p.name === 'event_admin') }"
                        href="#additional">Adicionais</a>
                </li>
            </ul>

            <!-- ABA EVENTO -->
            <div id="basic">

                <div class="card mb-4 py-3 border-left-primary">
                    <div class="card-body">
                        <form @submit.prevent="submit">

                            <div class="row">
                                <div class="col-lg-4">

                                    <div class="form-group">
                                        <InputLabel for="name" value="Nome do Evento:" />
                                        <TextInput type="text" class="form-control" v-model="form.name" required autofocus
                                            autocomplete="name" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.name" />
                                    </div>
                                    <div class="form-group">
                                        <InputLabel for="code" value="Código do Evento:" />
                                        <TextInput type="text" class="form-control" v-model="form.code" required autofocus
                                            autocomplete="code" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.code" />
                                    </div>

                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <InputLabel for="requester" value="Solicitante:" />
                                        <TextInput type="text" class="form-control" v-model="form.requester" required
                                            autofocus autocomplete="requester" />
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
                                        <TextInput type="text" class="form-control" v-model="form.sector" required autofocus
                                            autocomplete="sector" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.sector" />
                                    </div>

                                    <div class="form-group">
                                        <InputLabel for="paxBase" value="Base de Pax:" />
                                        <TextInput type="text" class="form-control" v-model="form.paxBase" required
                                            autofocus autocomplete="paxBase" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.paxBase" />
                                    </div>

                                    <div class="form-group">
                                        <InputLabel for="cc" value="Centro de Custo:" />
                                        <TextInput type="text" class="form-control" v-model="form.cc" required autofocus
                                            autocomplete="cc" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.cc" />
                                    </div>
                                </div>
                                <div class="col-lg-4">

                                    <div class="row">

                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="date" value="Data do Evento:" />
                                                <datepicker v-model="form.date" class="form-control" :locale="ptBR"
                                                    inputFormat="dd/MM/yyyy" weekdayFormat="EEEEEE" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.date" />
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="date_final" value="Data do Evento Fim:" />
                                                <datepicker v-model="form.date_final" class="form-control" :locale="ptBR"
                                                    inputFormat="dd/MM/yyyy" weekdayFormat="EEEEEE" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.date_final" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <InputLabel for="CRD" value="CRD:" />

                                        <select class="form-control" id="crd_id" :required="required">
                                            <option>.::Selecione::.</option>
                                            <option v-for="(option, index) in crds" :selected="option.id == form.crd_id"
                                                :value="option.id">
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
                                        <InputError class="mt-2 text-danger" :message="form.errors.hotel_operator" />
                                    </div>
                                    <div class="form-group">
                                        <InputLabel for="land_operator" value="Operador - Terrestre:" />
                                        <select class="form-control" id="land_operator" required="required">
                                            <option>.::Selecione::.</option>
                                            <option v-for="(option, index) in users"
                                                :selected="option.id == form.land_operator" :value="option.id">
                                                {{ option.name }}
                                            </option>
                                        </select>
                                        <InputError class="mt-2 text-danger" :message="form.errors.land_operator" />
                                    </div>
                                    <div class="form-group">
                                        <InputLabel for="air_operator" value="Operador - Aéreo:" />
                                        <select class="form-control" id="air_operator" required="required">
                                            <option>.::Selecione::.</option>
                                            <option v-for="(option, index) in users"
                                                :selected="option.id == form.air_operator" :value="option.id">
                                                {{ option.name }}
                                            </option>
                                        </select>
                                        <InputError class="mt-2 text-danger" :message="form.errors.air_operator" />
                                    </div>

                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-4 rigth">
                                <PrimaryButton css-class="btn btn-primary float-right m-1"
                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status"
                                        aria-hidden="true"></span>
                                    Avançar
                                </PrimaryButton>

                            </div>
                        </form>
                    </div>
                </div>

            </div>
            <!-- FIM ABA EVENTO -->

            <!-- ABA HOTEL -->
            <div v-if="event != null && $page.props.auth.permissions.some((p) => p.name === 'hotel_operator' || p.name === 'event_admin')"
                id="hotel">
                <div id="tabs-hotel">

                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link" href="#table">Lista</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#form-hotel">Cadastro Hotel</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" v-bind:class="{ 'disabled': !(eventHotel != null && eventHotel.id > 0) }"
                                href="#hotel-opt">Cadastro Detalhe</a>
                        </li>
                    </ul>
                    <div id="table">
                        <ListHotelFull :event-hotel="eventHotel" :event-hotels="eventHotels" :edit-opt="editOpt"
                            :duplicate="duplicate" :delete-opt="deleteOpt"></ListHotelFull>
                    </div>

                    <div id="form-hotel" class="card mb-4 py-3 border-left-primary">
                        <div class="card-body">
                            <FormProvider :event-hotel="eventHotel" :currencies="currencies" :hotels="hotels"
                                :select-hotel-call-back="selectHotel" :event-id="event.id" :mount-call-back="mount">
                            </FormProvider>
                        </div>
                    </div>

                    <div v-if="eventHotel != null && eventHotel.id > 0" id="hotel-opt">
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <FormProviderOpt :event-hotel="eventHotel" :brokers="brokers" :regimes="regimes"
                                            :purposes="purposes" :cats-hotel="catsHotel" :aptos-hotel="aptosHotel"
                                            :select-hotel-call-back="selectHotel" ref="formProviderOptRef"
                                            :hotel-selected="hotelSelected">
                                        </FormProviderOpt>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- FIM ABA HOTEL -->

            <!-- ABA A&B -->
            <div v-if="event != null && $page.props.auth.permissions.some((p) => p.name === 'land_operator' || p.name === 'event_admin')"
                id="aandb">
                <div id="tabs-aandb">

                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link" href="#table">Lista</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#form-ab">Cadastro A&B</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" v-bind:class="{ 'disabled': !(eventHotel != null && eventHotel.id > 0) }"
                                href="#ab-opt">Cadastro Detalhe</a>
                        </li>
                    </ul>
                    <div id="table">
                        <ListHotelFull :event-hotel="eventHotel" :event-hotels="eventHotels" :edit-opt="editOpt"
                            :duplicate="duplicate" :delete-opt="deleteOpt"></ListHotelFull>
                    </div>

                    <div id="form-ab" class="card mb-4 py-3 border-left-primary">
                        <div class="card-body">
                            <FormProvider :event-hotel="eventHotel" :currencies="currencies" :hotels="hotels"
                                :select-hotel-call-back="selectHotel" :event-id="event.id" :mount-call-back="mount">
                            </FormProvider>
                        </div>
                    </div>

                    <div v-if="eventHotel != null && eventHotel.id > 0" id="ab-opt">
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <FormProviderOpt :event-hotel="eventHotel" :brokers="brokers" :regimes="regimes"
                                            :purposes="purposes" :cats-hotel="catsHotel" :aptos-hotel="aptosHotel"
                                            :select-hotel-call-back="selectHotel" ref="formProviderOptRef"
                                            :hotel-selected="hotelSelected">
                                        </FormProviderOpt>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- FIM ABA A&B -->


            <div v-if="event != null && $page.props.auth.permissions.some((p) => p.name === 'land_operator' || p.name === 'event_admin')"
                id="hall">
                <p>Conteudo da aba follow Up.</p>
            </div>

            <div v-if="event != null && $page.props.auth.permissions.some((p) => p.name === 'land_operator' || p.name === 'event_admin')"
                id="additional">
                <p>Conteudo da aba follow Up.</p>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
