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
import ListABFull from '@/Components/ListABFull.vue';
import ListHallFull from '@/Components/ListHallFull.vue';
import ListAddFull from '@/Components/ListAddFull.vue';
import ListTransportFull from '@/Components/ListTransportFull.vue';
import FormProvider from '@/Components/FormProvider.vue';
import FormProviderHotelOpt from '@/Components/FormProviderHotelOpt.vue';
import FormProviderABOpt from '@/Components/FormProviderABOpt.vue';
import FormProviderHallOpt from '@/Components/FormProviderHallOpt.vue';
import FormProviderAddOpt from '@/Components/FormProviderAddOpt.vue';
import FormProviderTransportOpt from '@/Components/FormProviderTransportOpt.vue';

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
    providers: {
        type: Array,
        default: [],
    },
    providersService: {
        type: Array,
        default: [],
    },
    providersTranport: {
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
    eventTransport: {
        type: Object,
        default: {},
    },
    eventTransports: {
        type: Array,
        default: [],
    },
    eventABs: {
        type: Array,
        default: [],
    },
    eventAB: {
        type: Object,
        default: {},
    },
    eventHalls: {
        type: Array,
        default: [],
    },
    eventHall: {
        type: Object,
        default: {},
    },
    eventAdds: {
        type: Array,
        default: [],
    },
    eventAdd: {
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
    purposesHall: {
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
    },
    services: {
        type: Array,
        default: [],
    },
    servicesType: {
        type: Array,
        default: [],
    },
    locals: {
        type: Array,
        default: [],
    },
    servicesHall: {
        type: Array,
        default: [],
    },
    servicesAdd: {
        type: Array,
        default: [],
    },
    frequencies: {
        type: Array,
        default: [],
    },
    measures: {
        type: Array,
        default: [],
    },
    brokersT: {
        type: Array,
        default: [],
    },
    vehicles: {
        type: Array,
        default: [],
    },
    models: {
        type: Array,
        default: [],
    },
    servicesT: {
        type: Array,
        default: [],
    },
    brands: {
        type: Array,
        default: [],
    },
});

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
        form.countries = event.event_locals.length > 0 ? event.event_locals : form.countries;

        props.crds = props.crds.filter((v, i) => { return v.customer == null || v.customer.id == form.customer });

        $('#customer').val(event.customer_id).trigger('change');
        $('#crd_id').val(event.crd_id).trigger('change');
        $('#hotel_operator').val(event.hotel_operator).trigger('change');
        $('#air_operator').val(event.air_operator).trigger('change');
        $('#land_operator').val(event.land_operator).trigger('change');
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

    if ($('#tabs-hall').hasClass('ui-tabs')) {
        $("#tabs-hall").tabs("destroy");
    }
    $("#tabs-hall").tabs();

    if ($('#tabs-add').hasClass('ui-tabs')) {
        $("#tabs-add").tabs("destroy");
    }
    $("#tabs-add").tabs();

    if ($('#tabs-transport').hasClass('ui-transport')) {
        $("#tabs-transport").tabs("destroy");
    }
    $("#tabs-transport").tabs();
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
});

const addCountry = () => {
    form.countries.push({
        id: 0,
        pais: '',
        cidade: '',
        errors: '',
    });
};

const removeCountry = (index) => {
    if (form.countries.length > 1) {
        form.countries.splice(index, 1);
    }
};

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
    countries: [{
        id: 0,
        pais: '',
        cidade: '',
        errors: '',
    }]
});

const formDelete = useForm({
    id: 0
});
//FIM FORMS

//VARIAVEIS
const isLoader = ref(false);
//FIM VARIAVEIS

//FUNÇÕES HOTEL

const formProviderOptRef = ref(null);

const hotelSelected = ref(0);

const selectHotel = (id) => {
    var hotel = props.providers.filter((item) => { return item.id == id })[0] || null;

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

const editOpt = (opt) => {
    if (formProviderOptRef.value) {
        formProviderOptRef.value.editOpt(opt);
    }
};
//FIM FUNÇÕES HOTEL

//FUNÇÕES ALIMENTAÇÃO E BEBIDAS

const formProviderOptRefAB = ref(null);

const deleteOptAB = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('opt-ab-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
            mount();
        },
    });
};

const duplicateAB = (opt) => {
    if (formProviderOptRefAB.value) {
        formProviderOptRefAB.value.duplicate(opt);
    }
}

const editOptAB = (opt) => {
    if (formProviderOptRefAB.value) {
        formProviderOptRefAB.value.editOpt(opt);
    }
};

//FIM FUNÇÕES ALIMENTAÇÃO E BEBIDAS

//FUNÇÕES SALÕES

const formProviderOptRefHall = ref(null);


const deleteOptHall = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('opt-hall-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
            mount();
        },
    });
};

const duplicateHall = (opt) => {
    if (formProviderOptRefHall.value) {
        formProviderOptRefHall.value.duplicate(opt);
    }
}

const editOptHall = (opt) => {
    if (formProviderOptRefHall.value) {
        formProviderOptRefHall.value.editOpt(opt);
    }
};

//FIM FUNÇÕES SALÕES

//FUNÇÕES ADD

const formProviderOptRefAdd = ref(null);


const deleteOptAdd = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('opt-add-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
            mount();
        },
    });
};

const duplicateAdd = (opt) => {
    if (formProviderOptRefAdd.value) {
        formProviderOptRefAdd.value.duplicate(opt);
    }
}

const editOptAdd = (opt) => {
    if (formProviderOptRefAdd.value) {
        formProviderOptRefAdd.value.editOpt(opt);
    }
};

//FIM FUNÇÕES ADD


//FUNÇÕES TRANSPORTE

const formProviderOptRefTransport = ref(null);


const deleteOptTransport = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('opt-transport-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
            mount();
        },
    });
};

const duplicateTransport = (opt) => {
    if (formProviderOptRefTransport.value) {
        formProviderOptRefTransport.value.duplicate(opt);
    }
}

const editOptTransport = (opt) => {
    if (formProviderOptRefTransport.value) {
        formProviderOptRefTransport.value.editOpt(opt);
    }

};

//FIM FUNÇÕES ALIMENTAÇÃO E BEBIDAS

const formProviderHotelRef = ref(null);
const formProviderAbRef = ref(null);
const formProviderHallRef = ref(null);
const formProviderAddRef = ref(null);
const formProviderTransportRef = ref(null);
const newEventProv = (type) => {
    switch (props.type) {
        case 'hotel':
            if (formProviderHotelRef.value) {
                formProviderHotelRef.value.newEventProvider();
            }
            break;
        case 'ab':
            if (formProviderAbRef.value) {
                formProviderAbRef.value.newEventProvider();
            }
            break;
        case 'hall':
            if (formProviderHallRef.value) {
                formProviderHallRef.value.newEventProvider();
            }
            break;
        case 'add':
            if (formProviderAddRef.value) {
                formProviderAddRef.value.newEventProvider();
            }
            break;
        case 'transport':
            if (formProviderTransportRef.value) {
                formProviderTransportRef.value.newEventProvider();
            }
            break;
    }
};

</script>
<style>
.v3dp__popout[data-v-2e128338] {
    left: auto;
    right: 0px;
}
</style>
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
                        v-bind:class="{ 'disabled': event == null || !$page.props.auth.permissions.some((p) => p.name === 'hotel_operator' || p.name === 'event_admin') }"
                        href="#aandb">A&B</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                        v-bind:class="{ 'disabled': event == null || !$page.props.auth.permissions.some((p) => p.name === 'hotel_operator' || p.name === 'event_admin') }"
                        href="#hall">Salões</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                        v-bind:class="{ 'disabled': event == null || !$page.props.auth.permissions.some((p) => p.name === 'hotel_operator' || p.name === 'event_admin') }"
                        href="#additional">Adicionais</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                        v-bind:class="{ 'disabled': event == null || !$page.props.auth.permissions.some((p) => p.name === 'land_operator' || p.name === 'event_admin') }"
                        href="#transport">Transporte</a>
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
                                        <InputLabel for="code" value="Código do Zendesk:" />
                                        <TextInput type="text" class="form-control" v-model="form.code" required autofocus
                                            autocomplete="code" />
                                        <InputError class="mt-2 text-danger" :message="form.errors.code" />
                                    </div>

                                    <div class="border rounded p-3">
                                        <template v-for="(country, index) in form.countries" :key="index">
                                            <div class="form-group">
                                                <InputLabel :for="'country-' + index" value="País:" />
                                                <TextInput :id="'country-' + index" type="text" class="form-control"
                                                    v-model="country.pais" required autofocus
                                                    :autocomplete="'country-' + index" />
                                                <InputError class="mt-2 text-danger" :message="country.errors" />
                                            </div>

                                            <div class="form-group">
                                                <InputLabel :for="'event_city-' + index" value="Cidade:" />
                                                <TextInput :id="'event_city-' + index" type="text" class="form-control"
                                                    v-model="country.cidade" required
                                                    :autocomplete="'event_city-' + index" />
                                                <InputError class="mt-2 text-danger" :message="country.errors" />
                                            </div>

                                            <div class="form-group">
                                                <button v-if="form.countries.length > 1"
                                                    @click.prevent="removeCountry(index)"
                                                    class="btn-sm btn-danger btn-icon-split">
                                                    <span class="icon text-white-50">
                                                        <i class="fas fa-trash"></i>
                                                    </span>
                                                </button>
                                            </div>

                                            <hr />
                                        </template>
                                        <button @click.prevent="addCountry"
                                            class="btn-sm btn-info btn-icon-split float-right m-1">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-plus"></i>
                                            </span>
                                            <span class="text">Adicionar Local</span>
                                        </button>
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
                                        <TextInput type="text" class="form-control" v-model="form.cc" autofocus
                                            autocomplete="cc" required />
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
                                                <datepicker v-model="form.date_final" class="form-control custom-datepicker"
                                                    :locale="ptBR" inputFormat="dd/MM/yyyy" weekdayFormat="EEEEEE" />
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
                                                {{ option.number }}
                                            </option>
                                        </select>

                                        <InputError class="mt-2 text-danger" :message="form.errors.crd_id" />
                                    </div>

                                    <div class="form-group">
                                        <InputLabel for="hotel_operator" value="Operador - Hotel:" />
                                        <select class="form-control" id="hotel_operator">
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
                                        <select class="form-control" id="land_operator">
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
                                        <select class="form-control" id="air_operator">
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
                                <PrimaryButton css-class="btn-sm btn-primary float-right m-1"
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
                            :duplicate="duplicate" :delete-opt="deleteOpt" :mount-call-back="mount"
                            :new-event-hotel="newEventProv"></ListHotelFull>
                    </div>

                    <div id="form-hotel" class="card mb-4 py-3 border-left-primary">
                        <div class="card-body">
                            <FormProvider :event-provider="eventHotel" :currencies="currencies" :providers="providers"
                                type="hotel" :select-call-back="selectHotel" :event-id="event.id" :mount-call-back="mount"
                                ref="formProviderHotelRef">
                            </FormProvider>
                        </div>
                    </div>

                    <div v-if="eventHotel != null && eventHotel.id > 0" id="hotel-opt">
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <FormProviderHotelOpt :event-hotel="eventHotel" :brokers="brokers"
                                            :regimes="regimes" :purposes="purposes" :cats-hotel="catsHotel"
                                            :aptos-hotel="aptosHotel" :select-hotel-call-back="selectHotel"
                                            ref="formProviderOptRef" :hotel-selected="hotelSelected">
                                        </FormProviderHotelOpt>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- FIM ABA HOTEL -->

            <!-- ABA A&B -->
            <div v-if="event != null && $page.props.auth.permissions.some((p) => p.name === 'hotel_operator' || p.name === 'event_admin')"
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
                            <a class="nav-link" v-bind:class="{ 'disabled': !(eventAB != null && eventAB.id > 0) }"
                                href="#ab-opt">Cadastro Detalhe</a>
                        </li>
                    </ul>
                    <div id="table">
                        <ListABFull :event-a-b="eventAB" :event-a-bs="eventABs" :edit-opt="editOptAB"
                            :duplicate="duplicateAB" :delete-opt="deleteOptAB" :mount-call-back="mount"
                            :new-event-hotel="newEventProv"></ListABFull>
                    </div>

                    <div id="form-ab" class="card mb-4 py-3 border-left-primary">
                        <div class="card-body">
                            <FormProvider key="ab" :event-provider="eventAB" :currencies="currencies"
                                :providers="providers.filter(obj => !obj.is_transport)" type="ab" :event-id="event.id"
                                :mount-call-back="mount" ref="formProviderAbRef">
                            </FormProvider>
                        </div>
                    </div>

                    <div v-if="eventAB != null && eventAB.id > 0" id="ab-opt">
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <FormProviderABOpt :event-ab="eventAB" :brokers="brokers" :services="services"
                                            :services-type="servicesType" :locals="locals" ref="formProviderOptRefAB">
                                        </FormProviderABOpt>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- FIM ABA A&B -->

            <!-- ABA HALL -->
            <div v-if="event != null && $page.props.auth.permissions.some((p) => p.name === 'hotel_operator' || p.name === 'event_admin')"
                id="hall">

                <div id="tabs-hall">

                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link" href="#table">Lista</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#form-hall">Cadastro</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" v-bind:class="{ 'disabled': !(eventHall != null && eventHall.id > 0) }"
                                href="#hall-opt">Cadastro Detalhe</a>
                        </li>
                    </ul>
                    <div id="table">
                        <ListHallFull :event-hall="eventHall" :event-halls="eventHalls" :edit-opt="editOptHall"
                            :duplicate="duplicateHall" :delete-opt="deleteOptHall" :mount-call-back="mount"
                            :new-event-hall="newEventProv"></ListHallFull>
                    </div>

                    <div id="form-hall" class="card mb-4 py-3 border-left-primary">
                        <div class="card-body">
                            <FormProvider key="hall" :event-provider="eventHall" :currencies="currencies"
                                :providers="providers" type="hall" :event-id="event.id" :mount-call-back="mount"
                                ref="formProviderHallRef">
                            </FormProvider>
                        </div>
                    </div>

                    <div v-if="eventHall != null && eventHall.id > 0" id="hall-opt">
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <FormProviderHallOpt :event-hall="eventHall" :brokers="brokers"
                                            :purposes="purposesHall" :services="servicesHall" ref="formProviderOptRefHall">
                                        </FormProviderHallOpt>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- FIM ABA HALL -->

            <div v-if="event != null && $page.props.auth.permissions.some((p) => p.name === 'hotel_operator' || p.name === 'event_admin')"
                id="additional">

                <div id="tabs-add">

                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link" href="#table">Lista</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#form-add">Cadastro</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" v-bind:class="{ 'disabled': !(eventAdd != null && eventAdd.id > 0) }"
                                href="#add-opt">Cadastro Detalhe</a>
                        </li>
                    </ul>
                    <div id="table">
                        <ListAddFull :event-add="eventAdd" :event-adds="eventAdds" :edit-opt="editOptAdd"
                            :duplicate="duplicateAdd" :delete-opt="deleteOptAdd" :mount-call-back="mount"
                            :new-event-add="newEventProv"></ListAddFull>
                    </div>

                    <div id="form-add" class="card mb-4 py-3 border-left-primary">
                        <div class="card-body">
                            <FormProvider key="add" :event-provider="eventAdd" :currencies="currencies"
                                :providers="providersService" type="add" :event-id="event.id" :mount-call-back="mount"
                                ref="formProviderAddRef">
                            </FormProvider>
                        </div>
                    </div>
                    <div v-if="eventAdd != null && eventAdd.id > 0" id="add-opt">
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <FormProviderAddOpt :event-add="eventAdd" :frequencies="frequencies"
                                            :measures="measures" :services="servicesAdd" ref="formProviderOptRefAdd">
                                        </FormProviderAddOpt>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>


            <!-- ABA TRANSPORTE -->
            <div v-if="event != null && $page.props.auth.permissions.some((p) => p.name === 'land_operator' || p.name === 'event_admin')"
                id="transport">
                <div id="tabs-transport">

                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link" href="#table-trans">Lista</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#form-trans">Cadastro Transporte</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                                v-bind:class="{ 'disabled': !(eventTransport != null && eventTransport.id > 0) }"
                                href="#trans-opt">Cadastro Detalhe</a>
                        </li>
                    </ul>
                    <div id="table-trans">
                        <ListTransportFull :event-transport="eventTransport" :event-transports="eventTransports"
                            :edit-opt="editOptTransport" :duplicate="duplicateTransport" :delete-opt="deleteOptTransport"
                            :mount-call-back="mount" :new-event-transport="newEventProv"></ListTransportFull>
                    </div>

                    <div id="form-trans" class="card mb-4 py-3 border-left-primary">
                        <div class="card-body">
                            <FormProvider :event-provider="eventTransport" :currencies="currencies"
                                :providers="providersTranport" type="transport" :select-call-back="selectTransport"
                                :event-id="event.id" :mount-call-back="mount" ref="formProviderTransportRef">
                            </FormProvider>
                        </div>
                    </div>

                    <div v-if="eventTransport != null && eventTransport.id > 0" id="trans-opt">
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <FormProviderTransportOpt :event-transport="eventTransport" :brokers="brokersT"
                                            :vehicles="vehicles" :models="models" :services="servicesT" :brands="brands"
                                            :select-transport-call-back="selectHotel" ref="formProviderOptRefTransport"
                                            :transport-selected="hotelSelected">
                                        </FormProviderTransportOpt>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- FIM ABA TRANSPORTE -->

        </div>
    </AuthenticatedLayout>
</template>
