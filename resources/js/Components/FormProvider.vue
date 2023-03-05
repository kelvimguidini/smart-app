<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import Loader from '@/Components/Loader.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    eventHotel: {
        type: Object,
        default: null
    },
    currencies: {
        type: Array,
        default: [],
    },
    hotels: {
        type: Array,
        default: [],
    },
    selectHotelCallBack: {
        type: Function,
        default: null,
    },
    eventId: {
        type: Number,
        default: 0
    },
    mountCallBack: {
        type: Function,
        default: null,
    },
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

const formDelete = useForm({
    id: 0
});

const submitHotel = () => {

    formHotel.post(route('hotel-event-save'), {
        onSuccess: (id) => {
            props.mountCallBack();
            $('#tabs-hotel').tabs({ active: 2 });
        },
    });
};

const selectHotel = (id) => {

    var hotel = props.hotels.filter((item) => { return item.id == id })[0] || null;

    if (hotel) {
        formHotel.hotel_id = parseInt(id);
        formHotel.city = hotel.city;

        formHotel.iss_percent = hotel.iss_percent;
        formHotel.iva_percent = hotel.iva_percent;
        formHotel.service_percent = hotel.service_percent;

        props.selectHotelCallBack(id);
    }
}

const newEventHotel = () => {
    formHotel.reset();
    formHotel.event_id = props.eventId;

    $('#hotel-select').val('').trigger('change');
    $('#currency').val('').trigger('change');
}


const edit = () => {

    if (props.eventHotel != null) {
        selectHotel(props.eventHotel.hotel_id);

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

onMounted(() => {

    formHotel.event_id = props.eventId;
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

    edit();
    props.mountCallBack();
});

const isLoader = ref(false);
</script>


<template>
    <Loader v-bind:show="isLoader"></Loader>

    <form @submit.prevent="submitHotel">
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <InputLabel for="hotel" value="Hotel:" />

                    <select class="form-control" id="hotel-select" :required="required">
                        <option>.::Selecione::.</option>
                        <option v-for="(option, index) in hotels" :selected="option.id == formHotel.hotel_id"
                            :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>

                    <InputError class="mt-2 text-danger" :message="formHotel.errors.hotel" />
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <InputLabel for="city" value="Cidade:" />
                    <TextInput type="text" class="form-control" v-model="formHotel.city" disabled="true" />
                </div>
            </div>
            <div class="col">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <InputLabel for="iss_percent" value="ISS:" />
                            <TextInput type="number" class="form-control percent" v-model="formHotel.iss_percent" required
                                autofocus min="0" step=".1" autocomplete="iss_percent" />
                        </div>
                    </div>

                    <div class="col">
                        <div class="form-group">
                            <InputLabel for="service_percent" value="Serviço:" />
                            <TextInput type="number" class="form-control percent" v-model="formHotel.service_percent"
                                required autofocus min="0" step=".1" autocomplete="service_percent" />
                        </div>
                    </div>

                    <div class="col">
                        <div class="form-group">
                            <InputLabel for="iva_percent" value="IVA:" />
                            <TextInput type="number" class="form-control percent" v-model="formHotel.iva_percent" required
                                autofocus min="0" step=".1" autocomplete="iva_percent" />
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
                                <option v-for="(option, index) in currencies" :selected="option.id == formHotel.currency"
                                    :value="option.id">
                                    {{ option.name }}
                                </option>
                            </select>

                            <InputError class="mt-2 text-danger" :message="formHotel.errors.currency" />
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
                    <textarea class="form-control" v-model="formHotel.internal_observation"></textarea>
                </div>

            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    <InputLabel for="customer_observation" value="Observação Cliente:" />
                    <textarea class="form-control" v-model="formHotel.customer_observation"></textarea>
                </div>
            </div>

            <div class="col">
                <div class="items-center justify-end mt-4 rigth">
                    <PrimaryButton css-class="btn btn-primary float-right m-1"
                        :class="{ 'opacity-25': formHotel.processing }" :disabled="formHotel.processing">
                        <span v-if="formHotel.processing" class="spinner-border spinner-border-sm" role="status"
                            aria-hidden="true"></span>
                        Salvar
                    </PrimaryButton>


                    <PrimaryButton type="button" v-if="eventHotel != null && eventHotel.id > 0"
                        css-class="btn btn-info float-right m-1" v-on:click="newEventHotel();">
                        Novo
                    </PrimaryButton>

                </div>
            </div>
        </div>
    </form>
</template>
