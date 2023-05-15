<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import Loader from '@/Components/Loader.vue';

const isLoader = ref(false);

const props = defineProps({
    eventTransports: {
        type: Array,
        default: []
    },
    eventTransport: {
        type: Object,
        default: []
    },
    duplicate: {
        type: Function,
        default: null,
    },
    editOpt: {
        type: Function,
        default: null,
    },
    deleteOpt: {
        type: Function,
        default: null,
    },
    mountCallBack: {
        type: Function,
        default: null,
    },
    newEventTransport: {
        type: Function,
        default: null,
    },
});


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
    if (props.eventTransport != null) {
        sigla = props.eventTransport.currency.sigla;
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

const roomNights = (evtr) => {
    let sum = 0;
    for (const opt of evtr.event_transport_opts) {
        sum += (opt.count * daysBetween(opt.in, opt.out));
    }
    return sum;
}

const average = (evtr) => {
    let sum = 0;
    for (const opt of evtr.event_transport_opts) {
        sum += unitSale(opt);
    }
    return sum / evtr.event_transport_opts.length;
}

const sumCount = (evtr) => {
    let sum = 0;
    for (const opt of evtr.event_transport_opts) {
        sum += opt.count;
    }
    return sum;
}

const sumNts = (evtr) => {
    let sum = 0;
    for (const opt of evtr.event_transport_opts) {
        sum += daysBetween(opt.in, opt.out);
    }
    return sum;
}

const sumSale = (evtr) => {
    let sum = 0;
    for (const opt of evtr.event_transport_opts) {
        sum += unitSale(opt) * daysBetween(opt.in, opt.out) * opt.count;
    }
    return sum;
}

const sumCost = (evtr) => {
    let sum = 0;
    for (const opt of evtr.event_transport_opts) {
        sum += unitCost(opt) * daysBetween(opt.in, opt.out) * opt.count;
    }
    return sum;
}

const sumTaxes = (evtr, taxType) => {
    let sum = 0;
    for (const opt of evtr.event_transport_opts) {
        switch (taxType) {
            case 'iss':
                sum += ((unitSale(opt) * evtr.iss_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
            case 'serv':
                sum += ((unitSale(opt) * evtr.service_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
            case 'iva':
                sum += ((unitSale(opt) * evtr.iva_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
        }

    }
    return sum;
}
//FIM FUNÇÕES GERAIS
const formDelete = useForm({
    id: 0,
    event_id: 0
});

const deleteEventTransport = (data) => {
    isLoader.value = true;
    formDelete.id = data.id;
    formDelete.event_id = data.event_id;
    formDelete.delete(route('event-transport-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset()
            props.mountCallBack();
            props.newEventTransport('transport');
        },
    });
};

const showDetails = ref(false);
</script>


<template>
    <Loader v-bind:show="isLoader"></Loader>

    <div class="row">

        <div v-if="eventTransports.length == 0" class="alert alert-primary" role="alert">
            Nenhum fornecedor cadastrado!
        </div>
        <PrimaryButton v-if="eventTransports.length > 0" type="button" css-class="btn btn-success btn-sm btn-icon-split m-1"
            :title="showDetails ? 'Ocultar' : 'Exibir'" v-on:click="showDetails = !showDetails">
            <span class="icon text-white-50">
                <i class="fas" v-bind:class="{ 'fa-eye': showDetails, 'fa-eye-slash': !showDetails }"></i>
            </span>
            <span class="text m2">{{ showDetails ? 'Ocultar' : 'Exibir' }} Detalhes</span>
        </PrimaryButton>
        <div class="table-responsive">
            <table class="table table-sm table-bordered text-center" width="100%" cellspacing="0">

                <tbody>
                    <template v-for="(evtr, index) in eventTransports" :key="evtr.id">

                        <tr class="bg-light text-dark thead-dark">
                            <th class="text-left" :colspan="showDetails ? 22 : 15">
                                {{ index + 1 }} | {{ evtr.transport.name }} | {{ evtr.transport.national
                                    ?
                                    "Nacional" : "Internacional" }}
                                {{ evtr.transport.city }}
                            </th>
                            <th class="align-middle text-right" colspan="3">
                                <Link class="btn btn-info btn-sm btn-icon-split"
                                    :href="route('event-edit', { 'id': evtr.event_id, 'tab': 5, 'ehotel': evtr.id })">
                                <span class="icon text-white-50">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span class="text">Editar</span>
                                </Link>

                                <Modal modal-title="Confirmar Remoção" :ok-botton-callback="deleteEventTransport"
                                    :ok-botton-callback-param="{ 'id': evtr.id, 'event_id': evtr.event_id }"
                                    btn-class="btn btn-sm btn-danger btn-icon-split m-1">
                                    <template v-slot:button>
                                        <span class="icon text-white-50">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                        <span class="text">Excluir</span>
                                    </template>
                                    <template v-slot:content>
                                        <span class="text-dark text-left">Tem certeza que deseja remover o transporte
                                            terrestre {{
                                                evtr.transport.name
                                            }} do evento {{ evtr.event.name }}</span>
                                    </template>
                                </Modal>
                            </th>
                        </tr>

                        <tr class="thead-dark">
                            <th class="align-middle" rowspan="2" scope="col">Broker</th>
                            <th class="align-middle" rowspan="2" scope="col">Veículo</th>
                            <th class="align-middle" rowspan="2" scope="col">Modelo Uso</th>
                            <th class="align-middle" rowspan="2" scope="col">Serviços</th>
                            <th class="align-middle" rowspan="2" scope="col">Marcas</th>
                            <th class="align-middle" rowspan="2" scope="col">OBS</th>
                            <th class="align-middle" rowspan="2" scope="col">IN</th>
                            <th class="align-middle" rowspan="2" scope="col">OUT</th>
                            <th class="align-middle" rowspan="2" scope="col">QTD</th>
                            <th class="align-middle" rowspan="2" scope="col">Dias</th>
                            <th class="align-middle" rowspan="2" scope="col">Comissão (%)</th>
                            <th colspan="2" class="  align-middle" scope="col">Valor de Venda</th>
                            <th colspan="2" class="align-middle" scope="col">Valor de Custo</th>
                            <th class="align-middle" rowspan="2" scope="col">Proposta Recebida</th>
                            <th class="align-middle" rowspan="2" scope="col">%</th>
                            <template v-if="showDetails">
                                <th colspan="6" class="align-middle" scope="col">
                                    IMPOSTOS DESTACADOS POR SERVIÇOS
                                </th>
                            </template>
                            <th class="align-middle" rowspan="2" scope="col"></th>
                        </tr>
                        <tr class="thead-dark">
                            <th class="align-middle">Unidade</th>
                            <th class="align-middle">Total</th>
                            <th class="align-middle">Unidade</th>
                            <th class="align-middle">Custo TTL</th>
                            <template v-if="showDetails">
                                <th class="align-middle">{{ eventTransport != null ? eventTransport.percentISS : 0 }}%</th>
                                <th class="align-middle">ISS</th>
                                <th class="align-middle">{{ eventTransport != null ? eventTransport.percentIService : 0 }}%
                                </th>
                                <th class="align-middle">Servico</th>
                                <th class="align-middle">{{ eventTransport != null ? eventTransport.percentIVA : 0 }}%</th>
                                <th class="align-middle">IVA</th>
                            </template>
                        </tr>

                        <!-- Opt TRs -->
                        <tr v-if="evtr.event_transport_opts.length == 0">
                            <td :colspan="showDetails ? 24 : 18">
                                <div class="alert alert-primary" role="alert">
                                    {{ evtr }}
                                    Nenhum fornecedor cadastrado!
                                </div>
                            </td>
                        </tr>

                        <tr v-for="opt in evtr.event_transport_opts">
                            <td class="align-middle">{{ opt.broker.name }}</td>
                            <td class="align-middle">{{ opt.vehicle.name }}</td>
                            <td class="align-middle">{{ opt.model.name }}</td>
                            <td class="align-middle">{{ opt.service.name }}</td>
                            <td class="align-middle">{{ opt.brand.name }}</td>
                            <td class="align-middle">{{ opt.observation }}</td>
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
                                <td class="align-middle bg-secondary text-white">
                                    {{ evtr.iss_percent }}</td>
                                <td class=" align-middle bg-secondary text-white">
                                    {{ formatCurrency((unitSale(opt) * evtr.iss_percent) / 100) }}
                                </td>
                                <td class="align-middle bg-secondary text-white">
                                    {{ evtr.service_percent }}
                                </td>
                                <td class=" align-middle bg-secondary text-white">
                                    {{ formatCurrency(((unitSale(opt)) * evtr.service_percent) /
                                        100) }}
                                </td>
                                <td class="align-middle bg-secondary text-white">{{
                                    evtr.iva_percent
                                }}</td>
                                <td class=" align-middle bg-secondary text-white">
                                    {{ formatCurrency(((unitSale(opt)) * evtr.iva_percent) / 100) }}
                                </td>
                            </template>
                            <td class="align-middle">
                                <div class="d-flex">
                                    <PrimaryButton
                                        :disabled="!(eventTransport != null && eventTransport.id > 0 && eventTransport.id == opt.event_transport_id)"
                                        type="button" css-class="btn btn-info btn-circle btn-sm text-white" title="Editar"
                                        v-on:click="editOpt(opt)">
                                        <i class="fas fa-edit"></i>
                                    </PrimaryButton>

                                    <PrimaryButton
                                        :disabled="!(eventTransport != null && eventTransport.id > 0 && eventTransport.id == opt.event_transport_id)"
                                        type="button" css-class="btn btn-info btn-circle btn-sm text-white" title="Duplicar"
                                        v-on:click="duplicate(opt)">
                                        <i class="fas fa-clone"></i>
                                    </PrimaryButton>

                                    <Modal :key="index" :modal-title="'Confirmar Remoção'" :ok-botton-callback="deleteOpt"
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
                            <td colspan="7"></td>
                            <td class="align-middle text-rigth">Veículos:</td>
                            <td class="align-middle">{{ sumCount(evtr) }}</td>
                            <td class="align-middle bg-warning text-dark">
                                {{ sumNts(evtr) }}
                            </td>
                            <td class="align-middle bg-success text-white" colspan="2">
                                Total venda:
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(sumSale(evtr)) }}
                            </td>
                            <td class="align-middle bg-warning text-dark text-rigth">
                                Total Custo
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(sumCost(evtr)) }}
                            </td>
                            <td class="align-middle bg-warning text-dark text-rigth">
                                Média %
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{
                                    new Intl.NumberFormat({
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }).format((1 - (sumCost(evtr) / sumSale(evtr))) * 100)
                                }}
                            </td>
                            <template v-if="showDetails">
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
                            <td class="align-middle text-dark text-left" colspan="3">
                                OBSERVAÇÃO INTERNA:
                            </td>
                            <td class="align-middle text-dark text-left" :colspan="14">
                                {{ evtr.internal_observation }}
                            </td>
                            <template v-if="showDetails">
                                <td class="align-middle bg-success text-white">
                                    {{ formatCurrency(sumTaxes(evtr, 'iss')) }}
                                </td>
                                <td class="align-middle">
                                    {{ formatCurrency((sumCost(evtr) * evtr.iss_percent) / 100) }}
                                </td>
                                <td class="align-middle bg-success text-white">
                                    {{ formatCurrency(sumTaxes(evtr, 'serv')) }}
                                </td>
                                <td class="align-middle">
                                    {{ formatCurrency((sumCost(evtr) * evtr.service_percent) / 100) }}
                                </td>
                                <td class="align-middle bg-success text-white">
                                    {{ formatCurrency(sumTaxes(evtr, 'iva')) }}
                                </td>
                                <td class="align-middle">
                                    {{ formatCurrency((sumCost(evtr) * evtr.iva_percent) / 100) }}
                                </td>
                            </template>
                            <td class="align-middle"></td>
                        </tr>

                        <tr>
                            <td class="align-middle text-dark text-left" colspan="3">
                                OBSERVAÇÃO CLIENTE:
                            </td>
                            <td class="align-middle text-dark text-left" :colspan="14">
                                {{ evtr.customer_observation }}
                            </td>

                            <template v-if="showDetails">
                                <td class="align-middle bg-success text-white">
                                    Venda
                                </td>
                                <td class="align-middle bg-success text-white" colspan="2">
                                    {{ formatCurrency(sumSale(evtr) + sumTaxes(evtr, 'iss') +
                                        sumTaxes(evtr, 'serv') + sumTaxes(evtr, 'iva')) }}
                                </td>
                                <td class="align-middle bg-warning text-white">
                                    Custo
                                </td>
                                <td class="align-middle bg-warning text-white" colspan="2">
                                    {{ formatCurrency(((sumCost(evtr) * evtr.iss_percent) / 100) +
                                        ((sumCost(evtr) * evtr.service_percent) / 100) + ((sumCost(evtr) *
                                            evtr.iva_percent) / 100) + sumCost(evtr)) }}
                                </td>
                            </template>
                            <td></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
