<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted, ref, defineExpose, watch } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import Loader from '@/Components/Loader.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    eventProvider: {
        type: Object,
        default: null
    },
    currencies: {
        type: Array,
        default: [],
    },
    providers: {
        type: Array,
        default: [],
    },
    selectCallBack: {
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
    type: {
        type: String,
        default: null,
    },

});

const form = useForm({
    id: 0,
    event_id: 0,
    provider_id: 0,
    city: '',
    currency: '',
    invoice: false,
    iss_percent: null,
    service_percent: null,
    iva_percent: null,
    service_charge: null,
    iof: null,
    deadline: '',
    internal_observation: '',
    customer_observation: '',
    type: '',
    taxa_4bts: null, // Novo campo
});

const submit = () => {
    form.type = props.type;
    form.event_id = props.eventId;

    switch (props.type) {

        case 'add':
            var url = route('provider-service-event-save');
            break;
        case 'transport':
            var url = route('provider-transport-event-save');
            break;
        default:
            var url = route('hotel-event-save');
            break;
    }


    form.post(url, {
        onSuccess: (id) => {
            selectProvider(form.provider_id);
            props.mountCallBack();
            switch (props.type) {
                case 'hotel':
                    $('#tabs-hotel').tabs({ active: 2 });
                    break;
                case 'ab':
                    $('#tabs-aandb').tabs({ active: 2 });
                    break;
                case 'hall':
                    $('#tabs-hall').tabs({ active: 2 });
                    break;
                case 'add':
                    $('#tabs-add').tabs({ active: 2 });
                    break;
                case 'transport':
                    $('#tabs-transport').tabs({ active: 2 });
                    break;
            }
        },
    });
};

const selectProvider = (id) => {
    var prov = props.providers.filter((item) => { return item.id == id })[0] || null;

    if (prov) {
        form.provider_id = parseInt(id);
        form.city = prov.city?.name + ' - ' + (prov.city?.states ? prov.city?.states : prov.city?.country);

        form.iss_percent = prov.iss_percent;
        form.iva_percent = prov.iva_percent;
        form.service_percent = prov.service_percent;


        if (props.selectCallBack && typeof props.selectCallBack === 'function') {
            props.selectCallBack(id);
        }
    }
}

const newEventProvider = () => {
    form.reset();
    form.event_id = props.eventId;

    $('#hotel-select' + props.type).val('').trigger('change');
    $('#currency' + props.type).val('').trigger('change');
}

defineExpose({
    newEventProvider
});
const date = ref(new Date());


const edit = () => {

    if (props.eventProvider != null) {

        switch (props.type) {
            case 'hotel':
                selectProvider(props.eventProvider.hotel_id);
                $('#hotel-select' + props.type).val(props.eventProvider.hotel_id).trigger('change');
                break;
            case 'ab':
                selectProvider(props.eventProvider.ab_id);
                $('#hotel-select' + props.type).val(props.eventProvider.ab_id).trigger('change');
                break;
            case 'hall':
                selectProvider(props.eventProvider.hall_id);
                $('#hotel-select' + props.type).val(props.eventProvider.hall_id).trigger('change');
                break;
            case 'add':
                selectProvider(props.eventProvider.add_id);
                $('#hotel-select' + props.type).val(props.eventProvider.add_id).trigger('change');
                break;
            case 'transport':
                selectProvider(props.eventProvider.transport_id);
                $('#hotel-select' + props.type).val(props.eventProvider.transport_id).trigger('change');
                break;
        }

        form.id = props.eventProvider.id;
        form.event_id = props.eventProvider.event_id;

        form.currency = props.eventProvider.currency_id;
        form.invoice = props.eventProvider.invoice == true;
        form.iss_percent = props.eventProvider.iss_percent;
        form.service_percent = props.eventProvider.service_percent;
        form.iva_percent = props.eventProvider.iva_percent;

        form.iof = props.eventProvider.iof;
        form.service_charge = props.eventProvider.service_charge;
        const parts = b.eventProvider.deadline_date.split('-');
        const deadline = new Date(parts[0], parts[1] - 1, parts[2]);
        form.deadline = deadline;
        date.value = deadline;

        form.internal_observation = props.eventProvider.internal_observation;
        form.customer_observation = props.eventProvider.customer_observation;

        form.taxa_4bts = props.eventProvider.taxa_4bts;

        $('#currency' + props.type).val(props.eventProvider.currency_id).trigger('change');
    }
};

onMounted(() => {
    form.event_id = props.eventId;
    $('#hotel-select' + props.type).select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        form.hotel = e.params.data.id;
        selectProvider(e.params.data.id);
    });

    $('#currency' + props.type).select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        form.currency = e.params.data.id;
    });

    edit();
    props.mountCallBack();
});

const isLoader = ref(false);



const updateForm = () => {
    if (date.value) {
        const year = date.value.getFullYear();
        const month = String(date.value.getMonth() + 1).padStart(2, '0');
        const day = String(date.value.getDate()).padStart(2, '0');
        form.deadline = `${year}-${month}-${day}`;
    } else {
        form.deadline = '';
    }
};

</script>


<template>
    <Loader v-bind:show="isLoader"></Loader>

    <form @submit.prevent="submit">
        <div class="row">
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="form-group">
                    <InputLabel for="hotel" value="Fornecedor:" />
                    <select class="form-control form-control-sm" :id="'hotel-select' + type" :required="required">
                        <option value="">.::Selecione::.</option>
                        <option v-for="(option, index) in providers" :selected="option.id == form.provider_id"
                            :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                    <InputError class="mt-2 text-danger" :message="form.errors.hotel" />
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="form-group">
                    <InputLabel for="city" value="Cidade:" />
                    <TextInput type="text" class="form-control form-control-sm" v-model="form.city" disabled="true" />
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <InputLabel for="iss_percent" value="ISS:" />
                            <TextInput type="number" class="form-control form-control-sm percent"
                                v-model="form.iss_percent" required autofocus min="0" step="0.01"
                                autocomplete="iss_percent" />
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <InputLabel for="service_percent" value="Serviço:" />
                            <TextInput type="number" class="form-control form-control-sm percent"
                                v-model="form.service_percent" required autofocus min="0" step="0.01"
                                autocomplete="service_percent" />
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <InputLabel for="iva_percent" value="IVA:" />
                            <TextInput type="number" class="form-control form-control-sm percent"
                                v-model="form.iva_percent" required autofocus min="0" step="0.01"
                                autocomplete="iva_percent" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <InputLabel for="currencies" value="Moeda:" />
                            <select class="form-control form-control-sm" :id="'currency' + type" :required="required">
                                <option value="">.::Selecione::.</option>
                                <option v-for="(option, index) in currencies" :selected="option.id == form.currency"
                                    :value="option.id">
                                    {{ option.name }}
                                </option>
                            </select>
                            <InputError class="mt-2 text-danger" :message="form.errors.currency" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <InputLabel for="invoice" value="Nota Fiscal" />
                            <select class="form-control form-control-sm" v-model="form.invoice">
                                <option :value="false">Não</option>
                                <option :value="true">Sim</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="form-group">
                    <InputLabel for="internal_observation" value="Observação Interna:" />
                    <textarea class="form-control form-control-sm" v-model="form.internal_observation"></textarea>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="form-group">
                    <InputLabel for="customer_observation" value="Observação Cliente:" />
                    <textarea class="form-control form-control-sm" v-model="form.customer_observation"></textarea>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <InputLabel for="iof" value="IOF:" />
                            <TextInput type="number" class="form-control form-control-sm percent" v-model="form.iof"
                                required autofocus min="0" step="0.01" autocomplete="iof" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <InputLabel for="service_charge" value="Taxa de Turismo:" />
                            <TextInput type="number" class="form-control form-control-sm percent"
                                v-model="form.service_charge" required autofocus min="0" step="0.01"
                                autocomplete="service_charge" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="form-group">

                    <VDatePicker v-model="date" @update:modelValue="updateForm"
                        :model-config="{ type: 'string', mask: 'YYYY-MM-DD', timeAdjust: '00:00:00' }">
                        <template #default="{ inputValue, inputEvents }">

                            <InputLabel for="in" value="Prazo:" />
                            <TextInput readonly class="form-control custom-datepicker" :value="inputValue"
                                v-on="inputEvents" />
                            <InputError class="mt-2 text-danger" :message="form.errors.date" />

                        </template>
                    </VDatePicker>

                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="form-group">
                    <InputLabel for="taxa_4bts" value="Taxa 4BTS (%):" />
                    <TextInput type="number" class="form-control form-control-sm percent" v-model="form.taxa_4bts"
                        required autofocus min="0" step="0.01" autocomplete="taxa_4bts" />
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <PrimaryButton css-class="btn btn-primary m-1" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status"
                        aria-hidden="true"></span>
                    Salvar
                </PrimaryButton>
                <PrimaryButton type="button" v-if="eventProvider != null && eventProvider.id > 0"
                    css-class="btn btn-info m-1" v-on:click="newEventProvider();">
                    Novo
                </PrimaryButton>
            </div>
        </div>
    </form>

</template>
