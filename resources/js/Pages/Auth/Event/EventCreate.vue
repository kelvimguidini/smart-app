<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Loader from '@/Components/Loader.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { ref, onMounted } from 'vue';
import Datepicker from 'vue3-datepicker';
import { ptBR } from 'date-fns/locale';
import { Link } from '@inertiajs/inertia-vue3';


const props = defineProps({
    'crds': {
        type: Array,
        default: [],
    },
    'customers': {
        type: Array,
        default: [],
    },
    'users': {
        type: Array,
        default: [],
    },
    'event': {
        type: Object,
        default: {},
    },
    'hotels': {
        type: Array,
        default: [],
    },
    'eventHotels': {
        type: Array,
        default: [],
    },
    'eventHotel': {
        type: Object,
        default: {},
    },
    'tab': {
        default: 0
    },
    'brokers': {
        type: Array,
        default: [],
    },
    'currencies': {
        type: Array,
        default: [],
    },
    'regimes': {
        type: Array,
        default: [],
    },
    'purposes': {
        type: Array,
        default: [],
    },
    'catsHotel': {
        type: Array,
        default: [],
    },
    'aptosHotel': {
        type: Array,
        default: [],
    }
});

const edit = (event) => {
    if (event != null) {
        formHotel.event_id = event.id;
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

    if (props.eventHotel != null) {
        selectHotel(props.eventHotel.hotel_id);

        formOpt.event_hotel_id = props.eventHotel.id;
        formHotel.id = props.eventHotel.id;
        formHotel.event_id = props.eventHotel.event_id;
        formHotel.hotel_id = props.eventHotel.hotel_id;
        formHotel.currency = props.eventHotel.currency_id;
        formHotel.invoice = props.eventHotel.invoice == true;
        formHotel.iss_percent = props.eventHotel.iss_percent;
        formHotel.service_percent = props.eventHotel.service_percent;
        formHotel.iva_percent = props.eventHotel.iva_percent;

        formHotel.internal_observation = props.eventHotel.internal_observation;
        formHotel.customer_observation = props.eventHotel.customer_observation;

        $('#hotel-select').val(props.eventHotel.hotel_id).trigger('change');
        $('#currency').val(props.eventHotel.currency_id).trigger('change');
    }
};

const editOpt = (opt) => {
    formOpt.id = opt.id;

    duplicate(opt);
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

    //Hotel
    $('#hotel-select').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formHotel.hotel = e.params.data.id;
        selectHotel(e.params.data.id);
    });

    $('#currency').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formHotel.currency = e.params.data.id;
    });

    $('#broker').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.broker = e.params.data.id;
    });

    $('#regime').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.regime = e.params.data.id;
    });

    $('#purpose').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.purpose = e.params.data.id;
    });

    $('#cat').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.category_id = e.params.data.id;
    });

    $('#apto').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.apto_id = e.params.data.id;
    });
    //Hotel - Fim
    if (props.eventHotel != null) {
        formOpt.event_hotel_id = props.eventHotel.id;
    }
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

const formHotel = useForm({
    id: 0,
    event_id: 0,
    hotel_id: 0,
    city: '',
    currency: '',
    invoice: false,
    iss_percent: null,
    service_percent: null,
    iva_percent: null,

    internal_observation: '',
    customer_observation: ''
});

const formOpt = useForm({

    event_hotel_id: 0,
    id: 0,
    broker: 0,
    regime: 0,
    purpose: 0,
    category_id: 0,
    apto_id: 0,
    hotel_id: 0,
    in: '',
    out: '',
    received_proposal: null,
    received_proposal_percent: 0.8,
    kickback: null,
    count: null,
    compare_trivago: null,
    compare_website_htl: null,
    compare_omnibess: null,

});

const formDelete = useForm({
    id: 0
});
//FIM FORMS

//VARIAVEIS
const isLoader = ref(false);
const showDetails = ref(false);
//FIM VARIAVEIS

//FUNÇÕES HOTEL
const selectHotel = (id) => {

    var hotel = props.hotels.filter((item) => { return item.id == id })[0] || null;

    if (hotel) {
        formHotel.hotel_id = parseInt(id);
        formHotel.city = hotel.city;

        props.catsHotel = hotel.categories;
        props.aptosHotel = hotel.aptos;

        formHotel.iss_percent = hotel.iss_percent;
        formHotel.iva_percent = hotel.iva_percent;
        formHotel.service_percent = hotel.service_percent;

        formOpt.hotel_id = parseInt(id);
    }
}

const newEventHotel = () => {
    formHotel.reset();
    formHotel.event_id = props.event.id;

    $('#hotel-select').val('').trigger('change');
    $('#currency').val('').trigger('change');
}

const deleteEventHotel = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('event-hotel-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset()
            newEventHotel();
            mount();
        },
    });
};

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
    formOpt.event_hotel_id = props.eventHotel.id;

    formOpt.broker = opt.broker_id;
    formOpt.regime = opt.regime_id;
    formOpt.purpose = opt.purpose_id;
    formOpt.category_id = opt.category_hotel_id;
    formOpt.apto_id = opt.apto_hotel_id;
    formOpt.hotel_id = props.eventHotel.hotel_id;
    formOpt.in = new Date(opt.in);
    formOpt.out = new Date(opt.out);
    formOpt.received_proposal = opt.received_proposal;
    formOpt.kickback = opt.kickback;
    formOpt.count = opt.count;
    formOpt.compare_trivago = opt.compare_trivago;
    formOpt.compare_website_htl = opt.compare_website_htl;
    formOpt.compare_omnibess = opt.compare_omnibess;

    $('#broker').val(opt.broker_id).trigger('change');
    $('#regime').val(opt.regime_id).trigger('change');
    $('#purpose').val(opt.purpose_id).trigger('change');
    $('#cat').val(opt.apto_hotel_id).trigger('change');
    $('#apto').val(opt.category_hotel_id).trigger('change');


    $("#received_proposal").maskMoney('mask', opt.received_proposal);
    $('#compare_trivago').maskMoney('mask', opt.compare_trivago);
    $('#compare_website_htl').maskMoney('mask', opt.compare_website_htl);
    $('#compare_omnibess').maskMoney('mask', opt.compare_omnibess);


    $('#tabs-hotel').tabs({ active: 2 });
}

const submit = () => {

    form.post(route('event-save'), {
        onSuccess: () => {
            mount();
            $('#tabs').tabs({ active: 1 });
        }
    });
};

const submitHotel = () => {

    formHotel.post(route('hotel-event-save'), {
        onSuccess: (id) => {
            mount();
            $('#tabs-hotel').tabs({ active: 2 });
        },
    });
};

const submitOpt = () => {
    isLoader.value = true;

    formOpt.received_proposal = $('#received_proposal').maskMoney('unmasked')[0];
    formOpt.compare_trivago = $('#compare_trivago').maskMoney('unmasked')[0];
    formOpt.compare_website_htl = $('#compare_website_htl').maskMoney('unmasked')[0];
    formOpt.compare_omnibess = $('#compare_omnibess').maskMoney('unmasked')[0];

    if (formOpt.event_hotel_id == 0) {
        formOpt.event_hotel_id = props.eventHotel.id;
    }

    formOpt.post(route('hotel-opt-save'), {
        onFinish: () => {
            formOpt.reset();
            formOpt.hotel_id = formHotel.hotel_id;
            $('#broker').val('').trigger('change');
            $('#regime').val('').trigger('change');
            $('#purpose').val('').trigger('change');
            $('#cat').val('').trigger('change');
            $('#apto').val('').trigger('change');

            $('#received_proposal').val('');
            $('#compare_trivago').val('');
            $('#compare_website_htl').val('');
            $('#compare_omnibess').val('');
            selectHotel(formHotel.hotel_id);
            isLoader.value = false;
            mount();

            $('#tabs-hotel').tabs({ active: 0 });
        },
    });
};
//FIM FUNÇÕES HOTEL

//FUNÇÕES GERAIS
const daysBetween = (date1, date2) => {
    // Convert both dates to milliseconds
    var one = new Date(date1).getTime();
    var two = new Date(date2).getTime();
    // Calculate the difference in milliseconds
    var difference = Math.abs(one - two);
    // Convert back to days and return
    return Math.ceil(difference / (1000 * 60 * 60 * 24));
}

const formatCurrency = (value) => {
    value = Math.round(value * 100) / 100;
    let sigla = 'BRL';
    if (props.eventHotel != null) {
        sigla = props.eventHotel.currency.sigla;
    }
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: sigla,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
}

const unitCost = (opt) => {
    return opt.received_proposal - ((opt.received_proposal * opt.kickback) / 100);
}

const unitSale = (opt) => {
    return Math.ceil(unitCost(opt) / opt.received_proposal_percent)
}

const roomNights = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += (opt.count * daysBetween(opt.in, opt.out));
    }
    return sum;
}

const average = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += unitSale(opt);
    }
    return sum / evho.event_hotels_opt.length;
}

const sumCount = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += opt.count;
    }
    return sum;
}

const sumNts = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += daysBetween(opt.in, opt.out);
    }
    return sum;
}

const sumSale = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += unitSale(opt) * daysBetween(opt.in, opt.out) * opt.count;
    }
    return sum;
}

const sumCost = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += unitCost(opt) * daysBetween(opt.in, opt.out) * opt.count;
    }
    return sum;
}

const sumTaxes = (evho, taxType) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        switch (taxType) {
            case 'iss':
                sum += ((unitSale(opt) * evho.iss_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
            case 'serv':
                sum += ((unitSale(opt) * evho.service_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
            case 'iva':
                sum += ((unitSale(opt) * evho.iva_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
        }

    }
    return sum;
}
//FIM FUNÇÕES GERAIS
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
                                <PrimaryButton css-class="btn btn-primary float-right"
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
                        <div class="row">
                            <PrimaryButton type="button" css-class="btn btn-success btn-sm btn-icon-split mr-2"
                                :title="showDetails ? 'Ocultar' : 'Exibir'" v-on:click="showDetails = !showDetails">
                                <span class="icon text-white-50">
                                    <i class="fas"
                                        v-bind:class="{ 'fa-eye-slash': showDetails, 'fa-eye': !showDetails }"></i>
                                </span>
                                <span class="text">{{ showDetails ? 'Ocultar' : 'Exibir' }} Detalhes</span>
                            </PrimaryButton>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered text-center" width="100%" cellspacing="0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="align-middle" rowspan="2" scope="col">Broker</th>
                                            <th class="align-middle" rowspan="2" scope="col">Regime</th>
                                            <th class="align-middle" rowspan="2" scope="col">Proposito</th>
                                            <th class="align-middle" rowspan="2" scope="col">CAT.</th>
                                            <th class="align-middle" rowspan="2" scope="col">APTO</th>
                                            <th class="align-middle" rowspan="2" scope="col">IN</th>
                                            <th class="align-middle" rowspan="2" scope="col">OUT</th>
                                            <th class="align-middle" rowspan="2" scope="col">QTD</th>
                                            <th class="align-middle" rowspan="2" scope="col">NTS</th>
                                            <th class="align-middle" rowspan="2" scope="col">Comissão (%)</th>
                                            <th colspan="2" class="  align-middle" scope="col">Valor de
                                                Venda</th>
                                            <th colspan="2" class="align-middle" scope="col">Valor de Custo</th>
                                            <th class="align-middle" rowspan="2" scope="col">Proposta Recebida</th>
                                            <th class="align-middle" rowspan="2" scope="col">%</th>
                                            <template v-if="showDetails">
                                                <th colspan="3" class="align-middle" scope="col">Comparativo
                                                </th>
                                                <th colspan="6" class="align-middle" scope="col">
                                                    IMPOSTOS DESTACADOS POR SERVIÇOS
                                                </th>
                                            </template>
                                            <th class="align-middle" rowspan="2" scope="col"></th>
                                        </tr>
                                        <tr>
                                            <th class="align-middle">Unidade</th>
                                            <th class="align-middle">Total</th>
                                            <th class="align-middle">Unidade</th>
                                            <th class="align-middle">Custo TTL</th>
                                            <template v-if="showDetails">
                                                <th class="align-middle">Trivago</th>
                                                <th class="align-middle">Website HTL</th>
                                                <th class="align-middle">Omnibess</th>
                                                <th class="align-middle">{{ formHotel.percentISS }}%</th>
                                                <th class="align-middle">ISS</th>
                                                <th class="align-middle">{{ formHotel.percentIService }}%</th>
                                                <th class="align-middle">Servico</th>
                                                <th class="align-middle">{{ formHotel.percentIVA }}%</th>
                                                <th class="align-middle">IVA</th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="(evho, index) in eventHotels" :key="evho.id">

                                            <tr class="bg-light text-dark">
                                                <th class="text-left" :colspan="showDetails ? 24 : 15">
                                                    Hotel {{ index + 1 }} || {{ evho.hotel.name }} || {{ evho.hotel.national
                                                        ?
                                                        "Nacional" : "Internacional" }}
                                                    {{ evho.hotel.city }}
                                                </th>
                                                <th class="align-middle text-right" colspan="2">
                                                    <Link class="btn btn-info btn-sm btn-icon-split mr-2"
                                                        :href="route('event-edit', { 'id': evho.event_id, 'tab': 1, 'ehotel': evho.id })">
                                                    <span class="icon text-white-50">
                                                        <i class="fas fa-edit"></i>
                                                    </span>
                                                    <span class="text">Editar</span>
                                                    </Link>
                                                </th>
                                            </tr>

                                            <!-- Opt TRs -->
                                            <tr v-for="opt in evho.event_hotels_opt">
                                                <td class="align-middle">{{ opt.broker.name }}</td>
                                                <td class="align-middle">{{ opt.regime.name }}</td>
                                                <td class="align-middle">{{ opt.purpose.name }}</td>
                                                <td class="align-middle">{{ opt.category_hotel.category.name }}
                                                </td>
                                                <td class="align-middle">{{ opt.apto_hotel.apto.name }}</td>
                                                <td class="align-middle">{{
                                                    new Date(opt.in).toLocaleDateString()
                                                }}
                                                </td>
                                                <td class="align-middle">{{
                                                    new Date(opt.out).toLocaleDateString()
                                                }}
                                                </td>
                                                <td class="align-middle">{{ opt.count }}</td>
                                                <td class="align-middle bg-secondary text-white">
                                                    {{ daysBetween(opt.in, opt.out) }}
                                                </td>
                                                <td class="align-middle bg-danger text-white">
                                                    {{ opt.kickback }}
                                                </td>
                                                <td class="align-middle bg-success text-white">
                                                    {{ formatCurrency(unitSale(opt)) }}
                                                </td>
                                                <td class="align-middle bg-success text-white">
                                                    {{ formatCurrency(unitSale(opt) * daysBetween(opt.in, opt.out) *
                                                        opt.count) }}
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{ formatCurrency(unitCost(opt)) }}
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{ formatCurrency(unitCost(opt) * daysBetween(opt.in, opt.out) *
                                                        opt.count) }}
                                                </td>
                                                <td class=" align-middle">{{
                                                    formatCurrency(opt.received_proposal)
                                                }}</td>
                                                <td class="align-middle bg-warning text-dark">{{
                                                    opt.received_proposal_percent
                                                }}
                                                </td>
                                                <template v-if="showDetails">
                                                    <td class=" align-middle bg-secondary text-white">{{
                                                        formatCurrency(opt.compare_trivago)
                                                    }}
                                                    </td>
                                                    <td class=" align-middle bg-secondary text-white">{{
                                                        formatCurrency(opt.compare_website_htl)
                                                    }}
                                                    </td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        {{ formatCurrency(opt.compare_omnibess) }}
                                                    </td>
                                                    <td class="align-middle bg-secondary text-white">
                                                        {{ evho.iss_percent }}</td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        {{ formatCurrency((unitSale(opt) * evho.iss_percent) / 100) }}
                                                    </td>
                                                    <td class="align-middle bg-secondary text-white">
                                                        {{ evho.service_percent }}
                                                    </td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        {{ formatCurrency(((unitSale(opt)) * evho.service_percent) /
                                                            100) }}
                                                    </td>
                                                    <td class="align-middle bg-secondary text-white">{{
                                                        evho.iva_percent
                                                    }}</td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        {{ formatCurrency(((unitSale(opt)) * evho.iva_percent) / 100) }}
                                                    </td>
                                                </template>
                                                <td class="align-middle">
                                                    <div class="d-flex">
                                                        <PrimaryButton type="button"
                                                            :disabled="!(eventHotel != null && eventHotel.id > 0 && eventHotel.id == opt.event_hotel_id)"
                                                            css-class="btn btn-info btn-circle btn-sm text-white"
                                                            title="Editar" v-on:click="editOpt(opt)">
                                                            <i class="fas fa-edit"></i>
                                                        </PrimaryButton>

                                                        <PrimaryButton type="button"
                                                            :disabled="!(eventHotel != null && eventHotel.id > 0 && eventHotel.id == opt.event_hotel_id)"
                                                            css-class="btn btn-info btn-circle btn-sm text-white"
                                                            title="Duplicar" v-on:click="duplicate(opt)">
                                                            <i class="fas fa-clone"></i>
                                                        </PrimaryButton>

                                                        <Modal :key="index" :modal-title="'Confirmar Remoção'"
                                                            :ok-botton-callback="deleteOpt"
                                                            :ok-botton-callback-param="opt.id"
                                                            btn-class="btn btn-danger btn-circle btn-sm text-white">
                                                            <template v-slot:button>
                                                                <i class="fas fa-trash"></i>
                                                            </template>
                                                            <template v-slot:content>
                                                                Tem certeza que deseja remover esse registro?
                                                            </template>
                                                        </Modal>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- FIM Opt TRs -->
                                            <tr>
                                                <td class="align-middle bg-warning text-dark">
                                                    Diária Média:
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{ formatCurrency(average(evho)) }}
                                                </td>
                                                <td class="align-middle"></td>
                                                <td class="align-middle bg-warning text-dark" colspan="2">
                                                    Room Nights:
                                                </td>
                                                <td class="align-middle">{{ roomNights(evho) }}</td>
                                                <td class="align-middle"># Aptos:</td>
                                                <td class="align-middle">{{ sumCount(evho) }}</td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{ sumNts(evho) }}
                                                </td>
                                                <td class="align-middle bg-success text-white" colspan="2">
                                                    Total venda:
                                                </td>
                                                <td class="align-middle bg-success text-white">
                                                    {{ formatCurrency(sumSale(evho)) }}
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    Total Custo
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{ formatCurrency(sumCost(evho)) }}
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    Média %
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{
                                                        new Intl.NumberFormat({
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2
                                                        }).format((1 - (sumCost(evho) / sumSale(evho))) * 100)
                                                    }}
                                                </td>
                                                <template v-if="showDetails">
                                                    <td class="align-middle" colspan="3"></td>
                                                    <td class="align-middle bg-secondary text-white">
                                                        ISS CLIENTE
                                                    </td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        ISS CUSTO
                                                    </td>
                                                    <td class="align-middle bg-secondary text-white">
                                                        SERV CLIENTE
                                                    </td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        SERV CUSTO
                                                    </td>
                                                    <td class="align-middle bg-secondary text-white">IVA CLIENTE</td>
                                                    <td class=" align-middle bg-secondary text-white">IVA CUSTO</td>
                                                </template>
                                                <td class="align-middle"></td>
                                            </tr>

                                            <tr>
                                                <td class="align-middle text-dark" colspan="3">
                                                    OBSERVAÇÃO INTERNA:
                                                </td>
                                                <td class="align-middle text-dark text-left" colspan="13">
                                                    {{ evho.internal_observation }}
                                                </td>
                                                <template v-if="showDetails">
                                                    <td class="align-middle" colspan="3"></td>
                                                    <td class="align-middle bg-success text-white">
                                                        {{ formatCurrency(sumTaxes(evho, 'iss')) }}
                                                    </td>
                                                    <td class="align-middle">
                                                        {{ formatCurrency((sumCost(evho) * evho.iss_percent) / 100) }}
                                                    </td>
                                                    <td class="align-middle bg-success text-white">
                                                        {{ formatCurrency(sumTaxes(evho, 'serv')) }}
                                                    </td>
                                                    <td class="align-middle">
                                                        {{ formatCurrency((sumCost(evho) * evho.service_percent) / 100) }}
                                                    </td>
                                                    <td class="align-middle bg-success text-white">
                                                        {{ formatCurrency(sumTaxes(evho, 'iva')) }}
                                                    </td>
                                                    <td class="align-middle">
                                                        {{ formatCurrency((sumCost(evho) * evho.iva_percent) / 100) }}
                                                    </td>
                                                </template>
                                                <td class="align-middle"></td>
                                            </tr>

                                            <tr>
                                                <td class="align-middle text-dark" colspan="3">
                                                    OBSERVAÇÃO CLIENTE:
                                                </td>
                                                <td class="align-middle text-dark text-left" colspan="13">
                                                    {{ evho.customer_observation }}
                                                </td>

                                                <template v-if="showDetails">
                                                    <td class="align-middle" colspan="3"></td>
                                                    <td class="align-middle bg-success text-white">
                                                        Venda
                                                    </td>
                                                    <td class="align-middle bg-success text-white" colspan="2">
                                                        {{ formatCurrency(sumSale(evho) + sumTaxes(evho, 'iss') +
                                                            sumTaxes(evho, 'serv') + sumTaxes(evho, 'iva')) }}
                                                    </td>
                                                    <td class="align-middle bg-warning text-white">
                                                        Custo
                                                    </td>
                                                    <td class="align-middle bg-warning text-white" colspan="2">
                                                        {{ formatCurrency(((sumCost(evho) * evho.iss_percent) / 100) +
                                                            ((sumCost(evho) * evho.service_percent) / 100) + ((sumCost(evho) *
                                                                evho.iva_percent) / 100) + sumCost(evho)) }}
                                                    </td>
                                                </template>
                                                <td></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="form-hotel" class="card mb-4 py-3 border-left-primary">
                        <div class="card-body">
                            <form @submit.prevent="submitHotel">
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <InputLabel for="hotel" value="Hotel:" />

                                            <select class="form-control" id="hotel-select" :required="required">
                                                <option>.::Selecione::.</option>
                                                <option v-for="(option, index) in hotels"
                                                    :selected="option.id == formHotel.hotel_id" :value="option.id">
                                                    {{ option.name }}
                                                </option>
                                            </select>

                                            <InputError class="mt-2 text-danger" :message="formHotel.errors.hotel" />
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <InputLabel for="city" value="Cidade:" />
                                            <TextInput type="text" class="form-control" v-model="formHotel.city"
                                                disabled="true" />
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col">
                                                <div class="form-group">
                                                    <InputLabel for="iss_percent" value="ISS:" />
                                                    <TextInput type="number" class="form-control percent"
                                                        v-model="formHotel.iss_percent" required autofocus min="0" step=".1"
                                                        autocomplete="iss_percent" />
                                                </div>
                                            </div>

                                            <div class="col">
                                                <div class="form-group">
                                                    <InputLabel for="service_percent" value="Serviço:" />
                                                    <TextInput type="number" class="form-control percent"
                                                        v-model="formHotel.service_percent" required autofocus min="0"
                                                        step=".1" autocomplete="service_percent" />
                                                </div>
                                            </div>

                                            <div class="col">
                                                <div class="form-group">
                                                    <InputLabel for="iva_percent" value="IVA:" />
                                                    <TextInput type="number" class="form-control percent"
                                                        v-model="formHotel.iva_percent" required autofocus min="0" step=".1"
                                                        autocomplete="iva_percent" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col">

                                                <div class="form-group">
                                                    <InputLabel for="currencies" value="Moeda:" />

                                                    <select class="form-control" id="currency" :required="required">
                                                        <option>.::Selecione::.</option>
                                                        <option v-for="(option, index) in currencies"
                                                            :selected="option.id == formHotel.currency" :value="option.id">
                                                            {{ option.name }}
                                                        </option>
                                                    </select>

                                                    <InputError class="mt-2 text-danger"
                                                        :message="formHotel.errors.currency" />
                                                </div>
                                            </div>

                                            <div class="col">

                                                <div class="form-group">
                                                    <InputLabel for="invoice" value="Nota Fiscal" />
                                                    <select class="form-control" v-model="formHotel.invoice">
                                                        <option :value="false">Não</option>
                                                        <option :value="true">Sim</option>
                                                    </select>
                                                </div>

                                            </div>
                                        </div>

                                    </div>


                                </div>

                                <div class="row">
                                    <div class="col-lg-3">

                                        <div class="form-group">
                                            <InputLabel for="internal_observation" value="Observação Interna:" />
                                            <textarea class="form-control"
                                                v-model="formHotel.internal_observation"></textarea>
                                        </div>

                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <InputLabel for="customer_observation" value="Observação Cliente:" />
                                            <textarea class="form-control"
                                                v-model="formHotel.customer_observation"></textarea>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div class="items-center justify-end mt-4 rigth">
                                            <PrimaryButton css-class="btn btn-primary float-right"
                                                :class="{ 'opacity-25': formHotel.processing }"
                                                :disabled="formHotel.processing">
                                                <span v-if="formHotel.processing" class="spinner-border spinner-border-sm"
                                                    role="status" aria-hidden="true"></span>
                                                Salvar
                                            </PrimaryButton>


                                            <PrimaryButton type="button" v-if="eventHotel != null && eventHotel.id > 0"
                                                css-class="btn btn-info float-right m-1"
                                                v-on:click="form.reset(); newEventHotel();">
                                                Novo
                                            </PrimaryButton>

                                            <Modal v-if="eventHotel != null && eventHotel.id > 0" :key="index"
                                                :modal-title="'Confirmar Remoção'" :ok-botton-callback="deleteEventHotel"
                                                :ok-botton-callback-param="eventHotel.id"
                                                btn-class="btn btn-danger btn-icon-split">
                                                <template v-slot:button>
                                                    <span class="icon text-white-50">
                                                        <i class="fas fa-trash"></i>
                                                    </span>
                                                    <span class="text">Excluir</span>
                                                </template>
                                                <template v-slot:content>
                                                    Tem certeza que deseja remover o hotel {{
                                                        eventHotel.hotel.name
                                                    }}
                                                    do evento {{ eventHotel.event.name }}
                                                </template>
                                            </Modal>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div v-if="eventHotel != null && eventHotel.id > 0" id="hotel-opt">
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <form @submit.prevent="submitOpt">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <InputLabel for="broker" value="Broker:" />
                                                        <select class="form-control" id="broker" :required="required">
                                                            <option>.::Selecione::.</option>
                                                            <option v-for="(option, index) in brokers" :value="option.id">
                                                                {{ option.name }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <InputLabel for="regime" value="Regime:" />
                                                        <select class="form-control" id="regime" :required="required">
                                                            <option>.::Selecione::.</option>
                                                            <option v-for="(option, index) in regimes" :value="option.id">
                                                                {{ option.name }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <InputLabel for="purpose" value="Proposito:" />
                                                        <select class="form-control" id="purpose" :required="required">
                                                            <option>.::Selecione::.</option>
                                                            <option v-for="(option, index) in purposes" :value="option.id">
                                                                {{ option.name }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <InputLabel for="cat" value="CAT.:" />
                                                        <select class="form-control" id="cat" :required="required">
                                                            <option>.::Selecione::.</option>
                                                            <option v-for="(option, index) in catsHotel"
                                                                :value="option.pivot.category_id">
                                                                {{ option.name }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                </div>

                                                <div class="col-lg-4">

                                                    <div class="form-group">
                                                        <InputLabel for="apto" value="APTO:" />
                                                        <select class="form-control" id="apto" :required="required">
                                                            <option>.::Selecione::.</option>
                                                            <option v-for="(option, index) in aptosHotel"
                                                                :value="option.pivot.apto_id">
                                                                {{ option.name }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col">
                                                            <div class="form-group">
                                                                <InputLabel for="in" value="IN:" />

                                                                <datepicker v-model="formOpt.in" class="form-control"
                                                                    :locale="ptBR" inputFormat="dd/MM/yyyy"
                                                                    weekdayFormat="EEEEEE" />
                                                            </div>

                                                            <div class="form-group">
                                                                <InputLabel for="count" value="QTD:" />
                                                                <TextInput type="number" class="form-control"
                                                                    v-model="formOpt.count" required autofocus min="0"
                                                                    autocomplete="count" />
                                                            </div>
                                                        </div>

                                                        <div class="col">
                                                            <div class="form-group">
                                                                <InputLabel for="out" value="OUT:" />

                                                                <datepicker v-model="formOpt.out" class="form-control"
                                                                    :locale="ptBR" inputFormat="dd/MM/yyyy"
                                                                    weekdayFormat="EEEEEE" />
                                                            </div>

                                                            <div class="form-group">
                                                                <InputLabel for="kickback" id="kickback"
                                                                    value="Comissão (%):" />
                                                                <TextInput type="number" class="form-control"
                                                                    v-model="formOpt.kickback" required autofocus min="0"
                                                                    step=".1" autocomplete="kickback" />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col">
                                                            <div class="form-group">
                                                                <InputLabel for="received_proposal"
                                                                    value="Proposta Recebida:" />
                                                                <TextInput type="text" id="received_proposal"
                                                                    class="form-control money"
                                                                    v-model="formOpt.received_proposal" required autofocus
                                                                    autocomplete="received_proposal" />
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-4">

                                                            <div class="form-group">
                                                                <InputLabel for="received_proposal_percent" value="(%):" />
                                                                <TextInput type="number" class="form-control percent"
                                                                    v-model="formOpt.received_proposal_percent"
                                                                    :disabled="true" required autofocus min="0" step=".1"
                                                                    autocomplete="received_proposal_percent" />
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">

                                                    <div class="form-group">
                                                        <InputLabel for="compare_trivago" value="Comparação Trivago:" />
                                                        <TextInput id="compare_trivago" type="text"
                                                            class="form-control money" v-model="formOpt.compare_trivago"
                                                            required autofocus autocomplete="compare_trivago" />
                                                    </div>

                                                    <div class="form-group">
                                                        <InputLabel for="compare_website_htl"
                                                            value="comparação Website Htl" />
                                                        <TextInput id="compare_website_htl" type="text"
                                                            class="form-control money" v-model="formOpt.compare_website_htl"
                                                            required autofocus autocomplete="compare_website_htl" />
                                                    </div>

                                                    <div class="form-group">
                                                        <InputLabel for="compare_omnibess" value="comparação Omnibess:" />
                                                        <TextInput id="compare_omnibess" type="text"
                                                            class="form-control money" v-model="formOpt.compare_omnibess"
                                                            required autofocus autocomplete="compare_omnibess" />
                                                    </div>

                                                    <div class="flex items-center justify-end mt-4 rigth">
                                                        <PrimaryButton css-class="btn btn-primary float-right"
                                                            :class="{ 'opacity-25': formOpt.processing }"
                                                            :disabled="formOpt.processing || eventHotel == null || eventHotel.id == 0">
                                                            <i class="fa fa-save" v-if="formOpt.id > 0"></i>
                                                            <i class="fa fa-plus" v-else></i>
                                                        </PrimaryButton>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
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
                                href="#hotel-opt">Cadastro Detalhe</a>
                        </li>
                    </ul>
                    <div id="table">
                        <div class="row">
                            <PrimaryButton type="button" css-class="btn btn-success btn-sm btn-icon-split mr-2"
                                :title="showDetails ? 'Ocultar' : 'Exibir'" v-on:click="showDetails = !showDetails">
                                <span class="icon text-white-50">
                                    <i class="fas"
                                        v-bind:class="{ 'fa-eye-slash': showDetails, 'fa-eye': !showDetails }"></i>
                                </span>
                                <span class="text">{{ showDetails ? 'Ocultar' : 'Exibir' }} Detalhes</span>
                            </PrimaryButton>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered text-center" width="100%" cellspacing="0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="align-middle" rowspan="2" scope="col">Broker</th>
                                            <th class="align-middle" rowspan="2" scope="col">Regime</th>
                                            <th class="align-middle" rowspan="2" scope="col">Proposito</th>
                                            <th class="align-middle" rowspan="2" scope="col">CAT.</th>
                                            <th class="align-middle" rowspan="2" scope="col">APTO</th>
                                            <th class="align-middle" rowspan="2" scope="col">IN</th>
                                            <th class="align-middle" rowspan="2" scope="col">OUT</th>
                                            <th class="align-middle" rowspan="2" scope="col">QTD</th>
                                            <th class="align-middle" rowspan="2" scope="col">NTS</th>
                                            <th class="align-middle" rowspan="2" scope="col">Comissão (%)</th>
                                            <th colspan="2" class="  align-middle" scope="col">Valor de
                                                Venda</th>
                                            <th colspan="2" class="align-middle" scope="col">Valor de Custo</th>
                                            <th class="align-middle" rowspan="2" scope="col">Proposta Recebida</th>
                                            <th class="align-middle" rowspan="2" scope="col">%</th>
                                            <template v-if="showDetails">
                                                <th colspan="3" class="align-middle" scope="col">Comparativo
                                                </th>
                                                <th colspan="6" class="align-middle" scope="col">
                                                    IMPOSTOS DESTACADOS POR SERVIÇOS
                                                </th>
                                            </template>
                                            <th class="align-middle" rowspan="2" scope="col"></th>
                                        </tr>
                                        <tr>
                                            <th class="align-middle">Unidade</th>
                                            <th class="align-middle">Total</th>
                                            <th class="align-middle">Unidade</th>
                                            <th class="align-middle">Custo TTL</th>
                                            <template v-if="showDetails">
                                                <th class="align-middle">Trivago</th>
                                                <th class="align-middle">Website HTL</th>
                                                <th class="align-middle">Omnibess</th>
                                                <th class="align-middle">{{ formHotel.percentISS }}%</th>
                                                <th class="align-middle">ISS</th>
                                                <th class="align-middle">{{ formHotel.percentIService }}%</th>
                                                <th class="align-middle">Servico</th>
                                                <th class="align-middle">{{ formHotel.percentIVA }}%</th>
                                                <th class="align-middle">IVA</th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="(evho, index) in eventHotels" :key="evho.id">

                                            <tr class="bg-light text-dark">
                                                <th class="text-left" :colspan="showDetails ? 24 : 15">
                                                    Hotel {{ index + 1 }} || {{ evho.hotel.name }} || {{ evho.hotel.national
                                                        ?
                                                        "Nacional" : "Internacional" }}
                                                    {{ evho.hotel.city }}
                                                </th>
                                                <th class="align-middle text-right" colspan="2">
                                                    <Link class="btn btn-info btn-sm btn-icon-split mr-2"
                                                        :href="route('event-edit', { 'id': evho.event_id, 'tab': 1, 'ehotel': evho.id })">
                                                    <span class="icon text-white-50">
                                                        <i class="fas fa-edit"></i>
                                                    </span>
                                                    <span class="text">Editar</span>
                                                    </Link>
                                                </th>
                                            </tr>

                                            <!-- Opt TRs -->
                                            <tr v-for="opt in evho.event_hotels_opt">
                                                <td class="align-middle">{{ opt.broker.name }}</td>
                                                <td class="align-middle">{{ opt.regime.name }}</td>
                                                <td class="align-middle">{{ opt.purpose.name }}</td>
                                                <td class="align-middle">{{ opt.category_hotel.category.name }}
                                                </td>
                                                <td class="align-middle">{{ opt.apto_hotel.apto.name }}</td>
                                                <td class="align-middle">{{
                                                    new Date(opt.in).toLocaleDateString()
                                                }}
                                                </td>
                                                <td class="align-middle">{{
                                                    new Date(opt.out).toLocaleDateString()
                                                }}
                                                </td>
                                                <td class="align-middle">{{ opt.count }}</td>
                                                <td class="align-middle bg-secondary text-white">
                                                    {{ daysBetween(opt.in, opt.out) }}
                                                </td>
                                                <td class="align-middle bg-danger text-white">
                                                    {{ opt.kickback }}
                                                </td>
                                                <td class="align-middle bg-success text-white">
                                                    {{ formatCurrency(unitSale(opt)) }}
                                                </td>
                                                <td class="align-middle bg-success text-white">
                                                    {{ formatCurrency(unitSale(opt) * daysBetween(opt.in, opt.out) *
                                                        opt.count) }}
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{ formatCurrency(unitCost(opt)) }}
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{ formatCurrency(unitCost(opt) * daysBetween(opt.in, opt.out) *
                                                        opt.count) }}
                                                </td>
                                                <td class=" align-middle">{{
                                                    formatCurrency(opt.received_proposal)
                                                }}</td>
                                                <td class="align-middle bg-warning text-dark">{{
                                                    opt.received_proposal_percent
                                                }}
                                                </td>
                                                <template v-if="showDetails">
                                                    <td class=" align-middle bg-secondary text-white">{{
                                                        formatCurrency(opt.compare_trivago)
                                                    }}
                                                    </td>
                                                    <td class=" align-middle bg-secondary text-white">{{
                                                        formatCurrency(opt.compare_website_htl)
                                                    }}
                                                    </td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        {{ formatCurrency(opt.compare_omnibess) }}
                                                    </td>
                                                    <td class="align-middle bg-secondary text-white">
                                                        {{ evho.iss_percent }}</td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        {{ formatCurrency((unitSale(opt) * evho.iss_percent) / 100) }}
                                                    </td>
                                                    <td class="align-middle bg-secondary text-white">
                                                        {{ evho.service_percent }}
                                                    </td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        {{ formatCurrency(((unitSale(opt)) * evho.service_percent) /
                                                            100) }}
                                                    </td>
                                                    <td class="align-middle bg-secondary text-white">{{
                                                        evho.iva_percent
                                                    }}</td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        {{ formatCurrency(((unitSale(opt)) * evho.iva_percent) / 100) }}
                                                    </td>
                                                </template>
                                                <td class="align-middle">
                                                    <div class="d-flex">
                                                        <PrimaryButton type="button"
                                                            :disabled="!(eventHotel != null && eventHotel.id > 0 && eventHotel.id == opt.event_hotel_id)"
                                                            css-class="btn btn-info btn-circle btn-sm text-white"
                                                            title="Editar" v-on:click="editOpt(opt)">
                                                            <i class="fas fa-edit"></i>
                                                        </PrimaryButton>

                                                        <PrimaryButton type="button"
                                                            :disabled="!(eventHotel != null && eventHotel.id > 0 && eventHotel.id == opt.event_hotel_id)"
                                                            css-class="btn btn-info btn-circle btn-sm text-white"
                                                            title="Duplicar" v-on:click="duplicate(opt)">
                                                            <i class="fas fa-clone"></i>
                                                        </PrimaryButton>

                                                        <Modal :key="index" :modal-title="'Confirmar Remoção'"
                                                            :ok-botton-callback="deleteOpt"
                                                            :ok-botton-callback-param="opt.id"
                                                            btn-class="btn btn-danger btn-circle btn-sm text-white">
                                                            <template v-slot:button>
                                                                <i class="fas fa-trash"></i>
                                                            </template>
                                                            <template v-slot:content>
                                                                Tem certeza que deseja remover esse registro?
                                                            </template>
                                                        </Modal>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- FIM Opt TRs -->
                                            <tr>
                                                <td class="align-middle bg-warning text-dark">
                                                    Diária Média:
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{ formatCurrency(average(evho)) }}
                                                </td>
                                                <td class="align-middle"></td>
                                                <td class="align-middle bg-warning text-dark" colspan="2">
                                                    Room Nights:
                                                </td>
                                                <td class="align-middle">{{ roomNights(evho) }}</td>
                                                <td class="align-middle"># Aptos:</td>
                                                <td class="align-middle">{{ sumCount(evho) }}</td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{ sumNts(evho) }}
                                                </td>
                                                <td class="align-middle bg-success text-white" colspan="2">
                                                    Total venda:
                                                </td>
                                                <td class="align-middle bg-success text-white">
                                                    {{ formatCurrency(sumSale(evho)) }}
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    Total Custo
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{ formatCurrency(sumCost(evho)) }}
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    Média %
                                                </td>
                                                <td class="align-middle bg-warning text-dark">
                                                    {{
                                                        new Intl.NumberFormat({
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2
                                                        }).format((1 - (sumCost(evho) / sumSale(evho))) * 100)
                                                    }}
                                                </td>
                                                <template v-if="showDetails">
                                                    <td class="align-middle" colspan="3"></td>
                                                    <td class="align-middle bg-secondary text-white">
                                                        ISS CLIENTE
                                                    </td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        ISS CUSTO
                                                    </td>
                                                    <td class="align-middle bg-secondary text-white">
                                                        SERV CLIENTE
                                                    </td>
                                                    <td class=" align-middle bg-secondary text-white">
                                                        SERV CUSTO
                                                    </td>
                                                    <td class="align-middle bg-secondary text-white">IVA CLIENTE</td>
                                                    <td class=" align-middle bg-secondary text-white">IVA CUSTO</td>
                                                </template>
                                                <td class="align-middle"></td>
                                            </tr>

                                            <tr>
                                                <td class="align-middle text-dark" colspan="3">
                                                    OBSERVAÇÃO INTERNA:
                                                </td>
                                                <td class="align-middle text-dark text-left" colspan="13">
                                                    {{ evho.internal_observation }}
                                                </td>
                                                <template v-if="showDetails">
                                                    <td class="align-middle" colspan="3"></td>
                                                    <td class="align-middle bg-success text-white">
                                                        {{ formatCurrency(sumTaxes(evho, 'iss')) }}
                                                    </td>
                                                    <td class="align-middle">
                                                        {{ formatCurrency((sumCost(evho) * evho.iss_percent) / 100) }}
                                                    </td>
                                                    <td class="align-middle bg-success text-white">
                                                        {{ formatCurrency(sumTaxes(evho, 'serv')) }}
                                                    </td>
                                                    <td class="align-middle">
                                                        {{ formatCurrency((sumCost(evho) * evho.service_percent) / 100) }}
                                                    </td>
                                                    <td class="align-middle bg-success text-white">
                                                        {{ formatCurrency(sumTaxes(evho, 'iva')) }}
                                                    </td>
                                                    <td class="align-middle">
                                                        {{ formatCurrency((sumCost(evho) * evho.iva_percent) / 100) }}
                                                    </td>
                                                </template>
                                                <td class="align-middle"></td>
                                            </tr>

                                            <tr>
                                                <td class="align-middle text-dark" colspan="3">
                                                    OBSERVAÇÃO CLIENTE:
                                                </td>
                                                <td class="align-middle text-dark text-left" colspan="13">
                                                    {{ evho.customer_observation }}
                                                </td>

                                                <template v-if="showDetails">
                                                    <td class="align-middle" colspan="3"></td>
                                                    <td class="align-middle bg-success text-white">
                                                        Venda
                                                    </td>
                                                    <td class="align-middle bg-success text-white" colspan="2">
                                                        {{ formatCurrency(sumSale(evho) + sumTaxes(evho, 'iss') +
                                                            sumTaxes(evho, 'serv') + sumTaxes(evho, 'iva')) }}
                                                    </td>
                                                    <td class="align-middle bg-warning text-white">
                                                        Custo
                                                    </td>
                                                    <td class="align-middle bg-warning text-white" colspan="2">
                                                        {{ formatCurrency(((sumCost(evho) * evho.iss_percent) / 100) +
                                                            ((sumCost(evho) * evho.service_percent) / 100) + ((sumCost(evho) *
                                                                evho.iva_percent) / 100) + sumCost(evho)) }}
                                                    </td>
                                                </template>
                                                <td></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="form-hotel" class="card mb-4 py-3 border-left-primary">
                        <div class="card-body">
                            <form @submit.prevent="submitHotel">
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <InputLabel for="hotel" value="Hotel:" />

                                            <select class="form-control" id="hotel-select" :required="required">
                                                <option>.::Selecione::.</option>
                                                <option v-for="(option, index) in hotels"
                                                    :selected="option.id == formHotel.hotel_id" :value="option.id">
                                                    {{ option.name }}
                                                </option>
                                            </select>

                                            <InputError class="mt-2 text-danger" :message="formHotel.errors.hotel" />
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <InputLabel for="city" value="Cidade:" />
                                            <TextInput type="text" class="form-control" v-model="formHotel.city"
                                                disabled="true" />
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col">
                                                <div class="form-group">
                                                    <InputLabel for="iss_percent" value="ISS:" />
                                                    <TextInput type="number" class="form-control percent"
                                                        v-model="formHotel.iss_percent" required autofocus min="0" step=".1"
                                                        autocomplete="iss_percent" />
                                                </div>
                                            </div>

                                            <div class="col">
                                                <div class="form-group">
                                                    <InputLabel for="service_percent" value="Serviço:" />
                                                    <TextInput type="number" class="form-control percent"
                                                        v-model="formHotel.service_percent" required autofocus min="0"
                                                        step=".1" autocomplete="service_percent" />
                                                </div>
                                            </div>

                                            <div class="col">
                                                <div class="form-group">
                                                    <InputLabel for="iva_percent" value="IVA:" />
                                                    <TextInput type="number" class="form-control percent"
                                                        v-model="formHotel.iva_percent" required autofocus min="0" step=".1"
                                                        autocomplete="iva_percent" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col">

                                                <div class="form-group">
                                                    <InputLabel for="currencies" value="Moeda:" />

                                                    <select class="form-control" id="currency" :required="required">
                                                        <option>.::Selecione::.</option>
                                                        <option v-for="(option, index) in currencies"
                                                            :selected="option.id == formHotel.currency" :value="option.id">
                                                            {{ option.name }}
                                                        </option>
                                                    </select>

                                                    <InputError class="mt-2 text-danger"
                                                        :message="formHotel.errors.currency" />
                                                </div>
                                            </div>

                                            <div class="col">

                                                <div class="form-group">
                                                    <InputLabel for="invoice" value="Nota Fiscal" />
                                                    <select class="form-control" v-model="formHotel.invoice">
                                                        <option :value="false">Não</option>
                                                        <option :value="true">Sim</option>
                                                    </select>
                                                </div>

                                            </div>
                                        </div>

                                    </div>


                                </div>

                                <div class="row">
                                    <div class="col-lg-3">

                                        <div class="form-group">
                                            <InputLabel for="internal_observation" value="Observação Interna:" />
                                            <textarea class="form-control"
                                                v-model="formHotel.internal_observation"></textarea>
                                        </div>

                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <InputLabel for="customer_observation" value="Observação Cliente:" />
                                            <textarea class="form-control"
                                                v-model="formHotel.customer_observation"></textarea>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div class="items-center justify-end mt-4 rigth">
                                            <PrimaryButton css-class="btn btn-primary float-right"
                                                :class="{ 'opacity-25': formHotel.processing }"
                                                :disabled="formHotel.processing">
                                                <span v-if="formHotel.processing" class="spinner-border spinner-border-sm"
                                                    role="status" aria-hidden="true"></span>
                                                Salvar
                                            </PrimaryButton>


                                            <PrimaryButton type="button" v-if="eventHotel != null && eventHotel.id > 0"
                                                css-class="btn btn-info float-right m-1"
                                                v-on:click="form.reset(); newEventHotel();">
                                                Novo
                                            </PrimaryButton>

                                            <Modal v-if="eventHotel != null && eventHotel.id > 0" :key="index"
                                                :modal-title="'Confirmar Remoção'" :ok-botton-callback="deleteEventHotel"
                                                :ok-botton-callback-param="eventHotel.id"
                                                btn-class="btn btn-danger btn-icon-split">
                                                <template v-slot:button>
                                                    <span class="icon text-white-50">
                                                        <i class="fas fa-trash"></i>
                                                    </span>
                                                    <span class="text">Excluir</span>
                                                </template>
                                                <template v-slot:content>
                                                    Tem certeza que deseja remover o hotel {{
                                                        eventHotel.hotel.name
                                                    }}
                                                    do evento {{ eventHotel.event.name }}
                                                </template>
                                            </Modal>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div v-if="eventHotel != null && eventHotel.id > 0" id="hotel-opt">
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <form @submit.prevent="submitOpt">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <InputLabel for="broker" value="Broker:" />
                                                        <select class="form-control" id="broker" :required="required">
                                                            <option>.::Selecione::.</option>
                                                            <option v-for="(option, index) in brokers" :value="option.id">
                                                                {{ option.name }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <InputLabel for="regime" value="Regime:" />
                                                        <select class="form-control" id="regime" :required="required">
                                                            <option>.::Selecione::.</option>
                                                            <option v-for="(option, index) in regimes" :value="option.id">
                                                                {{ option.name }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <InputLabel for="purpose" value="Proposito:" />
                                                        <select class="form-control" id="purpose" :required="required">
                                                            <option>.::Selecione::.</option>
                                                            <option v-for="(option, index) in purposes" :value="option.id">
                                                                {{ option.name }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <InputLabel for="cat" value="CAT.:" />
                                                        <select class="form-control" id="cat" :required="required">
                                                            <option>.::Selecione::.</option>
                                                            <option v-for="(option, index) in catsHotel"
                                                                :value="option.pivot.category_id">
                                                                {{ option.name }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                </div>

                                                <div class="col-lg-4">

                                                    <div class="form-group">
                                                        <InputLabel for="apto" value="APTO:" />
                                                        <select class="form-control" id="apto" :required="required">
                                                            <option>.::Selecione::.</option>
                                                            <option v-for="(option, index) in aptosHotel"
                                                                :value="option.pivot.apto_id">
                                                                {{ option.name }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col">
                                                            <div class="form-group">
                                                                <InputLabel for="in" value="IN:" />

                                                                <datepicker v-model="formOpt.in" class="form-control"
                                                                    :locale="ptBR" inputFormat="dd/MM/yyyy"
                                                                    weekdayFormat="EEEEEE" />
                                                            </div>

                                                            <div class="form-group">
                                                                <InputLabel for="count" value="QTD:" />
                                                                <TextInput type="number" class="form-control"
                                                                    v-model="formOpt.count" required autofocus min="0"
                                                                    autocomplete="count" />
                                                            </div>
                                                        </div>

                                                        <div class="col">
                                                            <div class="form-group">
                                                                <InputLabel for="out" value="OUT:" />

                                                                <datepicker v-model="formOpt.out" class="form-control"
                                                                    :locale="ptBR" inputFormat="dd/MM/yyyy"
                                                                    weekdayFormat="EEEEEE" />
                                                            </div>

                                                            <div class="form-group">
                                                                <InputLabel for="kickback" id="kickback"
                                                                    value="Comissão (%):" />
                                                                <TextInput type="number" class="form-control"
                                                                    v-model="formOpt.kickback" required autofocus min="0"
                                                                    step=".1" autocomplete="kickback" />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col">
                                                            <div class="form-group">
                                                                <InputLabel for="received_proposal"
                                                                    value="Proposta Recebida:" />
                                                                <TextInput type="text" id="received_proposal"
                                                                    class="form-control money"
                                                                    v-model="formOpt.received_proposal" required autofocus
                                                                    autocomplete="received_proposal" />
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-4">

                                                            <div class="form-group">
                                                                <InputLabel for="received_proposal_percent" value="(%):" />
                                                                <TextInput type="number" class="form-control percent"
                                                                    v-model="formOpt.received_proposal_percent"
                                                                    :disabled="true" required autofocus min="0" step=".1"
                                                                    autocomplete="received_proposal_percent" />
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">

                                                    <div class="form-group">
                                                        <InputLabel for="compare_trivago" value="Comparação Trivago:" />
                                                        <TextInput id="compare_trivago" type="text"
                                                            class="form-control money" v-model="formOpt.compare_trivago"
                                                            required autofocus autocomplete="compare_trivago" />
                                                    </div>

                                                    <div class="form-group">
                                                        <InputLabel for="compare_website_htl"
                                                            value="comparação Website Htl" />
                                                        <TextInput id="compare_website_htl" type="text"
                                                            class="form-control money" v-model="formOpt.compare_website_htl"
                                                            required autofocus autocomplete="compare_website_htl" />
                                                    </div>

                                                    <div class="form-group">
                                                        <InputLabel for="compare_omnibess" value="comparação Omnibess:" />
                                                        <TextInput id="compare_omnibess" type="text"
                                                            class="form-control money" v-model="formOpt.compare_omnibess"
                                                            required autofocus autocomplete="compare_omnibess" />
                                                    </div>

                                                    <div class="flex items-center justify-end mt-4 rigth">
                                                        <PrimaryButton css-class="btn btn-primary float-right"
                                                            :class="{ 'opacity-25': formOpt.processing }"
                                                            :disabled="formOpt.processing || eventHotel == null || eventHotel.id == 0">
                                                            <i class="fa fa-save" v-if="formOpt.id > 0"></i>
                                                            <i class="fa fa-plus" v-else></i>
                                                        </PrimaryButton>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
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
