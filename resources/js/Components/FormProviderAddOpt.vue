<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted, ref, watch, defineExpose } from 'vue';
import Loader from '@/Components/Loader.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    eventAdd: {
        type: Object,
        default: null
    },
    measures: {
        type: Array,
        default: [],
    },
    services: {
        type: Array,
        default: [],
    },
    frequencies: {
        type: Array,
        default: [],
    }
});

const formOpt = useForm({
    event_add_id: 0,
    id: 0,
    measure: 0,
    service: 0,
    frequency: 0,
    unit: '',
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
    formOpt.event_add_id = props.eventAdd.id;

    if (!isEdit) {
        formOpt.id = 0;
    }

    formOpt.measure = opt.measure_id;
    formOpt.service = opt.service_id;
    formOpt.frequency = opt.frequency_id;
    formOpt.unit = opt.unit;
    formOpt.pax = opt.pax;
    formOpt.in = new Date(opt.in);
    formOpt.out = new Date(opt.out);
    range.value.start = formOpt.in;
    range.value.end = formOpt.out;
    formOpt.received_proposal = opt.received_proposal;
    formOpt.received_proposal_percent = opt.received_proposal_percent;
    formOpt.kickback = opt.kickback;
    formOpt.count = opt.count;
    setRange(opt);

    $('#measure').val(opt.measure_id).trigger('change');
    $('#service').val(opt.service_id).trigger('change');
    $('#frequency').val(opt.frequency_id).trigger('change');


    $("#received_proposal").maskMoney('mask', opt.received_proposal);

    $('#tabs-add').tabs({ active: 2 });
}

defineExpose({
    duplicate,
    editOpt
});


const submitOpt = () => {
    isLoader.value = true;

    formOpt.received_proposal = $('#received_proposal').maskMoney('unmasked')[0];

    if (formOpt.event_add == 0) {
        formOpt.event_add = props.eventAdd.id;
    }

    formOpt.post(route('add-opt-save'), {
        onFinish: () => {
            formOpt.reset();
            formOpt.add_id = props.eventAdd.add_id;
            $('#measure').val('').trigger('change');
            $('#service').val('').trigger('change');
            $('#frequency').val('').trigger('change');

            $('#received_proposal').val('');

            isLoader.value = false;

            formOpt.id = 0;

            $('#tabs-add').tabs({ active: 0 });
        },
        onError: (errors) => {
            console.error('Erro no backend:', errors);
            isLoader.value = false; // Desativa o loader em caso de erro
        },
        onSuccess: () => {
            isLoader.value = false; // Garante que o loader seja desativado após sucesso
        },
    });
};

onMounted(() => {
    //Hotel
    $('#measure').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.measure = e.params.data.id;
    });

    $('#service').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.service = e.params.data.id;
    });

    $('#frequency').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formOpt.frequency = e.params.data.id;
    });

    //Hotel - Fim
    if (props.eventAdd != null) {
        formOpt.event_add_id = props.eventAdd.id;
    }
    if (props.eventAdd != null) {
        formOpt.event_add_id = props.eventAdd.id;
    }

    let symbol = 'R$ ';
    if (props.eventAdd != null) {
        symbol = props.eventAdd.currency.symbol + ' ';
        formOpt.add_id = props.eventAdd.add_id;
    }
    var opt = {
        in: range.start,
        out: range.end,
    };
    setRange(opt);
    
    $('.money').maskMoney({ prefix: symbol, allowNegative: false, allowZero: true, thousands: '.', decimal: ',', affixesStay: true });
});


const isLoader = ref(false);

const range = ref({
    start: new Date(),
    end: new Date(),
});


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
</script>


<template>
    <Loader v-bind:show="isLoader"></Loader>


    <form @submit.prevent="submitOpt">
        <div class="row">
            <div class="col-lg-4">

                <div class="form-group">
                    <InputLabel for="service" value="Serviços:" />
                    <select class="form-control" id="service" required>
                        <option value="">.::Selecione::.</option>
                        <option v-for="(option, index) in services" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <InputLabel for="unit" value="Unidade utilizada:" />
                    <TextInput type="number" class="form-control" v-model="formOpt.unit" required autofocus
                        autocomplete="name" />
                </div>

                <div class="form-group">
                    <InputLabel for="measure" value="Medida:" />
                    <select class="form-control" id="measure" required="required">
                        <option value="">.::Selecione::.</option>
                        <option v-for="(option, index) in measures" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>


            </div>

            <div class="col-lg-4">

                <div class="form-group">
                    <InputLabel for="pax" value="#Pax:" />
                    <TextInput type="text" class="form-control" v-model="formOpt.pax" required autofocus
                        autocomplete="pax" />
                </div>


                <div class="form-group">
                    <InputLabel for="frequency" value="Frequência:" />
                    <select class="form-control" id="frequency" required="required">
                        <option value="">.::Selecione::.</option>
                        <option v-for="(option, index) in frequencies" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>


                <div class="row">
                    
                    <VDatePicker v-model="range" is-range expanded :columns="2" @update:modelValue="updateForm">
                        <template #default="{ inputValue, inputEvents }">

                            <div class="col">
                                <div class="form-group">
                                    <InputLabel for="in" value="IN:" />
                                    <TextInput readonly class="form-control custom-datepicker" :value="inputValue.start"
                                        v-on="inputEvents.start" />
                                    <InputError class="mt-2 text-danger" :message="formOpt.errors.in" />
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <InputLabel for="out" value="OUT:" />
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
                            <TextInput type="number" class="form-control" v-model="formOpt.count" required autofocus min="0"
                                autocomplete="count" />
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
                                v-model="formOpt.received_proposal" required autofocus autocomplete="received_proposal" />
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
                    <PrimaryButton css-class="btn btn-primary float-right m-1" :class="{ 'opacity-25': formOpt.processing }"
                        :disabled="formOpt.processing || eventAdd == null || eventAdd.id == 0">
                        <i class="fa fa-save" v-if="formOpt.id > 0"></i>
                        <i class="fa fa-plus" v-else></i>
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </form>
</template>
