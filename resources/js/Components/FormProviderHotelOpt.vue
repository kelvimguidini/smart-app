<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted, ref, watch, defineExpose } from 'vue';
import Loader from '@/Components/Loader.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import TextInput from '@/Components/TextInput.vue';
import Datepicker from 'vue3-datepicker';

const props = defineProps({
    eventHotel: {
        type: Object,
        default: null
    },

    brokers: {
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
    },
    selectHotelCallBack: {
        type: Function,
        default: null,
    },
    hotelSelected: {
        type: Number,
        default: 0,
    }
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

const editOpt = (opt) => {
    formOpt.id = opt.id;
    duplicate(opt, true);
}

const duplicate = (opt, edit = false) => {
    if (!edit) {
        formOpt.id = 0;
    }
    formOpt.event_hotel_id = props.eventHotel.id;

    formOpt.broker = opt.broker_id;
    formOpt.regime = opt.regime_id;
    formOpt.purpose = opt.purpose_id;
    formOpt.category_id = opt.category_hotel.category_id;
    formOpt.apto_id = opt.apto_hotel.apto_id;
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
    $('#cat').val(opt.category_hotel.category_id).trigger('change');
    $('#apto').val(opt.apto_hotel.apto_id).trigger('change');

    $("#received_proposal").maskMoney('mask', opt.received_proposal);
    $('#compare_trivago').maskMoney('mask', opt.compare_trivago);
    $('#compare_website_htl').maskMoney('mask', opt.compare_website_htl);
    $('#compare_omnibess').maskMoney('mask', opt.compare_omnibess);


    $('#tabs-hotel').tabs({ active: 2 });
}

defineExpose({
    duplicate,
    editOpt
});


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
            formOpt.hotel_id = props.eventHotel.hotel_id;
            $('#broker').val('').trigger('change');
            $('#regime').val('').trigger('change');
            $('#purpose').val('').trigger('change');
            $('#cat').val('').trigger('change');
            $('#apto').val('').trigger('change');

            $('#received_proposal').val('');
            $('#compare_trivago').val('');
            $('#compare_website_htl').val('');
            $('#compare_omnibess').val('');

            props.selectHotelCallBack(props.eventHotel.hotel_id);
            isLoader.value = false;

            $('#tabs-hotel').tabs({ active: 0 });
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
    if (props.eventHotel != null) {
        formOpt.event_hotel_id = props.eventHotel.id;
    }
    formOpt.hotel_id = props.hotelSelected;

    let symbol = 'R$ ';
    if (props.eventHotel != null) {
        symbol = props.eventHotel.currency.symbol + ' ';
    }
    $('.money').maskMoney({ prefix: symbol, allowNegative: false, thousands: '.', decimal: ',', affixesStay: true });

});


watch(
    () => ({
        hotelSelected: props.hotelSelected,
    }),
    (newValues, oldValues) => {
        if (newValues.hotelSelected != oldValues.hotelSelected) {
            formOpt.hotel_id = newValues.hotelSelected;
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
                        <option v-for="(option, index) in catsHotel" :value="option.id">
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
                        <option v-for="(option, index) in aptosHotel" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>

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
            </div>

            <div class="col-lg-4">

                <div class="form-group">
                    <InputLabel for="compare_trivago" value="Comparação Trivago:" />
                    <TextInput id="compare_trivago" type="text" class="form-control money" v-model="formOpt.compare_trivago"
                        required autofocus autocomplete="compare_trivago" />
                </div>

                <div class="form-group">
                    <InputLabel for="compare_website_htl" value="comparação Website Htl" />
                    <TextInput id="compare_website_htl" type="text" class="form-control money"
                        v-model="formOpt.compare_website_htl" required autofocus autocomplete="compare_website_htl" />
                </div>

                <div class="form-group">
                    <InputLabel for="compare_omnibess" value="comparação Omnibess:" />
                    <TextInput id="compare_omnibess" type="text" class="form-control money"
                        v-model="formOpt.compare_omnibess" required autofocus autocomplete="compare_omnibess" />
                </div>

                <div class="flex items-center justify-end mt-4 rigth">
                    <PrimaryButton css-class="btn btn-primary float-right m-1" :class="{ 'opacity-25': formOpt.processing }"
                        :disabled="formOpt.processing || eventHotel == null || eventHotel.id == 0">
                        <i class="fa fa-save" v-if="formOpt.id > 0"></i>
                        <i class="fa fa-plus" v-else></i>
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </form>
</template>
