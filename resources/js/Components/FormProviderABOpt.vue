<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted, ref, watch, defineExpose } from 'vue';
import Loader from '@/Components/Loader.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import TextInput from '@/Components/TextInput.vue';


const props = defineProps({
    eventAb: {
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
    servicesType: {
        type: Array,
        default: [],
    },
    locals: {
        type: Array,
        default: [],
    },
});

const formOpt = useForm({
    event_ab_id: 0,
    id: 0,
    broker: 0,
    service_id: 0,
    service_type_id: 0,
    local_id: 0,
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

const duplicate = (opt, edit = false) => {
    if (!edit) {
        formOpt.id = 0;
    }
    formOpt.event_ab_id = props.eventAb.id;

    formOpt.broker = opt.broker_id;
    formOpt.local_id = opt.local_id;
    formOpt.service_id = opt.service_id;
    formOpt.service_type_id = opt.service_type_id;
    formOpt.in = new Date(opt.in);
    formOpt.out = new Date(opt.out);
    range.value.start = formOpt.in;
    range.value.end = formOpt.out;
    formOpt.received_proposal = opt.received_proposal;
    formOpt.received_proposal_percent = opt.received_proposal_percent;
    formOpt.kickback = opt.kickback;
    formOpt.count = opt.count;
    setRange(opt);

    $('#broker').val(opt.broker_id).trigger('change');
    $('#local').val(opt.local_id).trigger('change');
    $('#service').val(opt.service_id).trigger('change');
    $('#servece-type').val(opt.service_type_id).trigger('change');

    $("#received_proposal").maskMoney('mask', opt.received_proposal);


    $('#tabs-aandb').tabs({ active: 2 });
}

defineExpose({
    duplicate,
    editOpt
});


const submitOpt = () => {
    isLoader.value = true;

    formOpt.received_proposal = $('#received_proposal').maskMoney('unmasked')[0];


    if (formOpt.event_ab_id == 0) {
        formOpt.event_ab_id = props.eventAb.id;
    }

    formOpt.post(route('ab-opt-save'), {
        onFinish: () => {
            formOpt.reset();
            formOpt.ab_id = props.eventAb.ab_id;
            $('#broker').val('').trigger('change');
            $('#local').val('').trigger('change');
            $('#service').val('').trigger('change');
            $('#service-type').val('').trigger('change');

            $('#received_proposal').val('');

            isLoader.value = false;

            $('#tabs-aandb').tabs({ active: 0 });
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

    $('#local').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.local_id = e.params.data.id;
    });

    $('#service').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.service_id = e.params.data.id;
    });

    $('#service-type').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.service_type_id = e.params.data.id;
    });

    //Hotel - Fim
    if (props.eventAb != null) {
        formOpt.event_ab_id = props.eventAb.id;
    }
    if (props.eventAb != null) {
        formOpt.event_ab_id = props.eventAb.id;
    }

    let symbol = 'R$ ';
    if (props.eventAb != null) {
        symbol = props.eventAb.currency.symbol + ' ';
        formOpt.ab_id = props.eventAb.ab_id;
    }
    $('.money').maskMoney({ prefix: symbol, allowNegative: false, allowZero: true, thousands: '.', decimal: ',', affixesStay: true });
});

const range = ref({
    start: new Date(),
    end: new Date(+1),
});


// Função para atualizar `form.date` e `form.date_final` quando o usuário selecionar novas datas
const updateForm = () => {
    formOpt.in = range.value.start ? range.value.start.toISOString().split('T')[0] : '';
    formOpt.out = range.value.end ? range.value.end.toISOString().split('T')[0] : '';
};


const setRange = (opt) => {
    range.value = {  // 🔥 Criando um novo objeto para garantir a reatividade
        start: new Date(opt.in),
        end: new Date(opt.out),
    };
};

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
                    <InputLabel for="service-type" value="Tipo de Serviços:" />
                    <select class="form-control" id="service-type" :required="required">
                        <option>.::Selecione::.</option>
                        <option v-for="(option, index) in servicesType" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="form-group">
                    <InputLabel for="local" value="Local:" />
                    <select class="form-control" id="local" :required="required">
                        <option>.::Selecione::.</option>
                        <option v-for="(option, index) in locals" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>

                <div class="row">
                    <VDatePicker v-model="range" is-range expanded :columns="2" @update:modelValue="updateForm">
                        <template #default="{ inputValue, inputEvents }">
                            <div class="col">
                                <div class="form-group">
                                    <InputLabel for="date" value="IN:" />
                                    <TextInput readonly class="form-control custom-datepicker" :value="inputValue.start"
                                        v-on="inputEvents.start" />
                                    <InputError class="mt-2 text-danger" :message="formOpt.errors.in" />
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <InputLabel for="date_final" value="OUT:" />
                                    <TextInput readonly class="form-control custom-datepicker" :value="inputValue.end"
                                        v-on="inputEvents.end" />
                                    <InputError class="mt-2 text-danger" :message="formOpt.errors.out" />
                                </div>
                            </div>

                        </template>
                    </VDatePicker>

                </div>

            </div>

            <div class="col-lg-4">


                <div class="row">
                    <div class="col">

                        <div class="form-group">
                            <InputLabel for="count" value="QTD:" />
                            <TextInput type="number" class="form-control" v-model="formOpt.count" required autofocus
                                min="0" autocomplete="count" />
                        </div>
                    </div>

                    <div class="col">

                        <div class="form-group">
                            <InputLabel for="kickback" id="kickback" value="Comissão (%):" />
                            <TextInput type="number" class="form-control" v-model="formOpt.kickback" required autofocus
                                min="0" step="0.01" autocomplete="kickback" />
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <InputLabel for="received_proposal" value="Proposta Recebida:" />
                            <TextInput type="text" id="received_proposal" class="form-control money"
                                v-model="formOpt.received_proposal" required autofocus
                                autocomplete="received_proposal" />
                        </div>
                    </div>

                    <div class="col-lg-4">

                        <div class="form-group">
                            <InputLabel for="received_proposal_percent" value="Markup (%):" />
                            <TextInput type="number" class="form-control percent"
                                v-model="formOpt.received_proposal_percent" required autofocus min="0" step="0.01"
                                autocomplete="received_proposal_percent" />
                        </div>

                    </div>
                </div>
                <div class="flex items-center justify-end mt-4 rigth">
                    <PrimaryButton css-class="btn btn-primary float-right m-1"
                        :class="{ 'opacity-25': formOpt.processing }"
                        :disabled="formOpt.processing || eventAb == null || eventAb.id == 0">
                        <i class="fa fa-save" v-if="formOpt.id > 0"></i>
                        <i class="fa fa-plus" v-else></i>
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </form>
</template>
