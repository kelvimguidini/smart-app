<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted, ref, watch, defineExpose } from 'vue';
import Loader from '@/Components/Loader.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import TextInput from '@/Components/TextInput.vue';
import Datepicker from 'vue3-datepicker';

const props = defineProps({
    eventHall: {
        type: Object,
        default: null
    },
    brokers: {
        type: Array,
        default: [],
    },
    services: {
        type: Array,
        default: [],
    },
    purposes: {
        type: Array,
        default: [],
    }
});

const formOpt = useForm({
    event_hall_id: 0,
    id: 0,
    broker: 0,
    service: 0,
    purpose: 0,
    name: '',
    m2: '',
    pax: '',
    in: '',
    out: '',
    received_proposal: null,
    received_proposal_percent: 0.8,
    kickback: null,
    count: null,
});

const editOpt = (opt) => {
    formOpt.id = opt.id;

    duplicate(opt, true);
}

const duplicate = (opt, isEdit = false) => {
    formOpt.event_hall_id = props.eventHall.id;

    if (!isEdit) {
        formOpt.id = 0;
    }

    formOpt.broker = opt.broker_id;
    formOpt.service = opt.service_id;
    formOpt.purpose = opt.purpose_id;
    formOpt.name = opt.name;
    formOpt.m2 = opt.m2;
    formOpt.pax = opt.pax;
    formOpt.in = new Date(opt.in);
    formOpt.out = new Date(opt.out);
    formOpt.received_proposal = opt.received_proposal;
    formOpt.kickback = opt.kickback;
    formOpt.count = opt.count;

    $('#broker').val(opt.broker_id).trigger('change');
    $('#service').val(opt.service_id).trigger('change');
    $('#purpose').val(opt.purpose_id).trigger('change');


    $("#received_proposal").maskMoney('mask', opt.received_proposal);

    $('#tabs-hall').tabs({ active: 2 });
}

defineExpose({
    duplicate,
    editOpt
});


const submitOpt = () => {
    isLoader.value = true;

    formOpt.received_proposal = $('#received_proposal').maskMoney('unmasked')[0];

    if (formOpt.event_hall == 0) {
        formOpt.event_hall = props.eventHall.id;
    }

    formOpt.post(route('hall-opt-save'), {
        onFinish: () => {
            formOpt.reset();
            formOpt.hall_id = props.eventHall.hall_id;
            $('#broker').val('').trigger('change');
            $('#service').val('').trigger('change');
            $('#purpose').val('').trigger('change');

            $('#received_proposal').val('');

            isLoader.value = false;

            formOpt.id = 0;

            $('#tabs-hall').tabs({ active: 0 });
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

    $('#service').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.service = e.params.data.id;
    });

    $('#purpose').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.purpose = e.params.data.id;
    });

    //Hotel - Fim
    if (props.eventHall != null) {
        formOpt.event_hall_id = props.eventHall.id;
    }
    if (props.eventHall != null) {
        formOpt.event_hall_id = props.eventHall.id;
    }

    let symbol = 'R$ ';
    if (props.eventHall != null) {
        symbol = props.eventHall.currency.symbol + ' ';
        formOpt.hall_id = props.eventHall.hall_id;
    }
    $('.money').maskMoney({ prefix: symbol, allowNegative: false, thousands: '.', decimal: ',', affixesStay: true });
});


const isLoader = ref(false);
</script>


<template>
    <Loader v-bind:show="isLoader"></Loader>


    <form @submit.prevent="submitOpt">
        <div class="row">
            <div class="col-lg-4">

                <div class="form-group">
                    <InputLabel for="service" value="Serviços:" />
                    <select class="form-control" id="service" :required="required">
                        <option>.::Selecione::.</option>
                        <option v-for="(option, index) in services" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>

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
                    <InputLabel for="name" value="Nome do Salão:" />
                    <TextInput type="text" class="form-control" v-model="formOpt.name" required autofocus
                        autocomplete="name" />
                </div>

                <div class="form-group">
                    <InputLabel for="m2" value="M2:" />
                    <TextInput type="text" class="form-control" v-model="formOpt.m2" required autofocus autocomplete="m2" />
                </div>

            </div>

            <div class="col-lg-4">


                <div class="form-group">
                    <InputLabel for="purpose" value="Propósito:" />
                    <select class="form-control" id="purpose" :required="required">
                        <option>.::Selecione::.</option>
                        <option v-for="(option, index) in purposes" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <InputLabel for="pax" value="#Pax:" />
                    <TextInput type="text" class="form-control" v-model="formOpt.pax" required autofocus
                        autocomplete="pax" />
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <InputLabel for="in" value="IN:" />
                            <datepicker v-model="formOpt.in" class="form-control" :locale="ptBR" inputFormat="dd/MM/yyyy"
                                weekdayFormat="EEEEEE" />
                        </div>
                    </div>

                    <div class="col">
                        <div class="form-group">
                            <InputLabel for="out" value="OUT:" />
                            <datepicker v-model="formOpt.out" class="form-control" :locale="ptBR" inputFormat="dd/MM/yyyy"
                                weekdayFormat="EEEEEE" />
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">


                <div class="row">
                    <div class="col">

                        <div class="form-group">
                            <InputLabel for="count" value="QTD:" />
                            <TextInput type="number" class="form-control" v-model="formOpt.count" required autofocus min="0"
                                autocomplete="count" />
                        </div>
                    </div>

                    <div class="col">

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
                                v-model="formOpt.received_proposal_percent" :disabled="true" required autofocus min="0"
                                step=".1" autocomplete="received_proposal_percent" />
                        </div>

                    </div>
                </div>
                <div class="flex items-center justify-end mt-4 rigth">
                    <PrimaryButton css-class="btn btn-primary float-right m-1" :class="{ 'opacity-25': formOpt.processing }"
                        :disabled="formOpt.processing || eventHall == null || eventHall.id == 0">
                        <i class="fa fa-save" v-if="formOpt.id > 0"></i>
                        <i class="fa fa-plus" v-else></i>
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </form>
</template>
