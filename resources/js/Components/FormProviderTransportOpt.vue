<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted, ref, watch, defineExpose } from 'vue';
import Loader from '@/Components/Loader.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import TextInput from '@/Components/TextInput.vue';
import Datepicker from 'vue3-datepicker';

const props = defineProps({
    eventTransport: {
        type: Object,
        default: null
    },
    brokers: {
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
    services: {
        type: Array,
        default: [],
    },
    brands: {
        type: Array,
        default: [],
    },
    selectTransportCallBack: {
        type: Function,
        default: null,
    },
    transportSelected: {
        type: Number,
        default: 0,
    }
});

const formOpt = useForm({
    event_transport_id: 0,
    id: 0,
    broker: 0,
    vehicle: 0,
    model: 0,
    service: 0,
    brand: 0,
    observation: '',
    in: '',
    out: '',
    received_proposal: null,
    received_proposal_percent: 0.8,
    kickback: null,
    count: null
});

const editOpt = (opt) => {
    formOpt.id = opt.id;

    duplicate(opt, true);
}

const duplicate = (opt, edit = false) => {
    if (edit) {
        formOpt.id = 0;
    }
    formOpt.event_transport_id = props.eventTransport.id;
    formOpt.broker = opt.broker_id;
    formOpt.vehicle = opt.vehicle_id;
    formOpt.model = opt.model_id;
    formOpt.service = opt.service_id;
    formOpt.brand = opt.brand_id;
    formOpt.observation = opt.observation;
    formOpt.in = new Date(opt.in);
    formOpt.out = new Date(opt.out);
    formOpt.received_proposal = opt.received_proposal;
    formOpt.kickback = opt.kickback;
    formOpt.count = opt.count;

    $('#broker').val(opt.broker_id).trigger('change');
    $('#vehicle').val(opt.vehicle_id).trigger('change');
    $('#model').val(opt.model_id).trigger('change');
    $('#service').val(opt.service_id).trigger('change');
    $('#brand').val(opt.brand_id).trigger('change');

    $("#received_proposal").maskMoney('mask', opt.received_proposal);

    $('#tabs-transport').tabs({ active: 2 });
}

defineExpose({
    duplicate,
    editOpt
});


const submitOpt = () => {
    isLoader.value = true;

    formOpt.received_proposal = $('#received_proposal').maskMoney('unmasked')[0];

    if (formOpt.event_transport_id == 0) {
        formOpt.event_transport_id = props.eventTransport.id;
    }

    formOpt.post(route('transport-opt-save'), {
        onFinish: () => {
            formOpt.reset();
            formOpt.transport_id = props.eventTransport.transport_id;
            $('#broker').val('').trigger('change');
            $('#vehicle').val('').trigger('change');
            $('#model').val('').trigger('change');
            $('#service').val('').trigger('change');
            $('#brand').val('').trigger('change');

            $('#received_proposal').val('');

            props.selectTransportCallBack(props.eventTransport.transport_id);
            isLoader.value = false;

            $('#tabs-transport').tabs({ active: 0 });
        },
    });
};

onMounted(() => {
    //Hotel
    $('#broker').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.broker = e.params.data.id;
    });

    $('#vehicle').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.vehicle = e.params.data.id;
    });

    $('#model').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.model = e.params.data.id;
    });

    $('#service').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.service = e.params.data.id;
    });

    $('#brand').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.brand = e.params.data.id;
    });
    //Hotel - Fim
    if (props.eventTransport != null) {
        formOpt.event_transport_id = props.eventTransport.id;
    }
    if (props.eventTransport != null) {
        formOpt.event_transport_id = props.eventTransport.id;
    }
    formOpt.transport_id = props.transportSelected;

    let symbol = 'R$ ';
    if (props.eventTransport != null) {
        symbol = props.eventTransport.currency.symbol + ' ';
    }
    $('.money').maskMoney({ prefix: symbol, allowNegative: false, thousands: '.', decimal: ',', affixesStay: true });

});


watch(
    () => ({
        transportSelected: props.transportSelected,
    }),
    (newValues, oldValues) => {
        if (newValues.transportSelected != oldValues.transportSelected) {
            formOpt.transport_id = newValues.transportSelected;
        }
    }
);


const isLoader = ref(false);
</script>


<template>
    <Loader v-bind:show="isLoader"></Loader>

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
                    <InputLabel for="vehicle" value="Veículo:" />
                    <select class="form-control" id="vehicle" :required="required">
                        <option>.::Selecione::.</option>
                        <option v-for="(option, index) in vehicles" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <InputLabel for="model" value="Modelo:" />
                    <select class="form-control" id="model" :required="required">
                        <option>.::Selecione::.</option>
                        <option v-for="(option, index) in models" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-lg-4">

                <div class="form-group">
                    <InputLabel for="service" value="Serviço:" />
                    <select class="form-control" id="service" :required="required">
                        <option>.::Selecione::.</option>
                        <option v-for="(option, index) in services" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <InputLabel for="observation" value="OBS.:" />
                    <TextInput type="text" class="form-control" v-model="formOpt.observation" autofocus
                        autocomplete="observation" />
                </div>

                <div class="form-group">
                    <InputLabel for="brand" value="Marca:" />
                    <select class="form-control" id="brand" :required="required">
                        <option>.::Selecione::.</option>
                        <option v-for="(option, index) in brands" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <InputLabel for="in" value="IN:" />

                            <datepicker v-model="formOpt.in" class="form-control" :locale="ptBR" inputFormat="dd/MM/yyyy"
                                weekdayFormat="EEEEEE" />
                        </div>

                        <div class="form-group">
                            <InputLabel for="count" value="QTD:" />
                            <TextInput type="number" class="form-control" v-model="formOpt.count" required autofocus min="0"
                                autocomplete="count" />
                        </div>
                    </div>

                    <div class="col">
                        <div class="form-group">
                            <InputLabel for="out" value="OUT:" />

                            <datepicker v-model="formOpt.out" class="form-control" :locale="ptBR" inputFormat="dd/MM/yyyy"
                                weekdayFormat="EEEEEE" />
                        </div>

                        <div class="form-group">
                            <InputLabel for="kickback" id="kickback" value="Comissão (%):" />
                            <TextInput type="number" class="form-control" v-model="formOpt.kickback" required autofocus
                                min="0" step=".1" autocomplete="kickback" />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <InputLabel for="received_proposal" value="Proposta Recebida:" />
                            <TextInput type="text" id="received_proposal" class="form-control money"
                                v-model="formOpt.received_proposal" required autofocus autocomplete="received_proposal" />
                        </div>
                    </div>

                    <div class="col-lg-4">

                        <div class="form-group">
                            <InputLabel for="received_proposal_percent" value="(%):" />
                            <TextInput type="number" class="form-control percent"
                                v-model="formOpt.received_proposal_percent" required autofocus min="0" step=".1"
                                autocomplete="received_proposal_percent" />
                        </div>

                    </div>
                </div>

                <div class="flex items-center justify-end mt-4 rigth">
                    <PrimaryButton css-class="btn btn-primary float-right m-1" :class="{ 'opacity-25': formOpt.processing }"
                        :disabled="formOpt.processing || eventTransport == null || eventTransport.id == 0">
                        <i class="fa fa-save" v-if="formOpt.id > 0"></i>
                        <i class="fa fa-plus" v-else></i>
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </form>
</template>
