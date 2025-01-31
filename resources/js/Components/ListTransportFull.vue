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
// Helper function to truncate hours, minutes, seconds, and milliseconds
const truncateToDate = (date) => {
        const d = new Date(date);
        d.setHours(0, 0, 0, 0);
        return d;
    }

    // Truncate both dates to remove time part
    const truncatedDate1 = truncateToDate(date1);
    const truncatedDate2 = truncateToDate(date2);

    // Convert both truncated dates to milliseconds
    const one = truncatedDate1.getTime();
    const two = truncatedDate2.getTime();

    // Calculate the difference in milliseconds
    const difference = Math.abs(one - two);

    // Convert back to days and return
    return Math.ceil(difference / (1000 * 60 * 60 * 24)) + 1;
}

const statusBlockEdit = () => {
    if (props.eventTransport && props.eventTransport.status_history) {
        var status = props.eventTransport.status_history.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0].status; // TODO ordenar por data e pegar o ultimo registro

        return (status == "dating_with_customer" || status == "Cancelled")
    }
    return false;
}

const formatCurrency = (value, sigla = 'BRL')  => {
    value = Math.round(value * 100) / 100;
    
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: sigla,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
}

const unitCost = (opt) => {
    return opt.received_proposal;
}

const unitSale = (opt) => {
    if(opt.received_proposal_percent > 0){
        return Math.ceil(unitCost(opt) / opt.received_proposal_percent);
    }
    return unitCost(opt);
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
            case 'sc':
                sum += ((unitSale(opt) * evtr.service_charge) / 100) * daysBetween(opt.in, opt.out) * opt.count;
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
            Nenhum cadastro para transporte!
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

                        <tr>
                            <th class="table-header table-header-c1" colspan="3">Transporte {{ index + 1 }}</th>
                            <th class="text-left table-header table-header-c2" :colspan="showDetails ? 20 : 12">
                                {{ evtr.transport.name }}
                            </th>
                            <th class="align-middle text-right table-header-c1 table-header" colspan="3">

                                <Link class="btn btn-info btn-sm btn-icon-split" :disabled="statusBlockEdit()"
                                    :href="route('event-edit', { 'id': evtr.event_id, 'tab': 5, 'ehotel': evtr.id })">
                                <span class="icon text-white-50">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span class="text">Editar</span>
                                </Link>

                                <Modal modal-title="Confirmar Remoção" :ok-botton-callback="deleteEventTransport"
                                    :ok-botton-callback-param="{ 'id': evtr.id, 'event_id': evtr.event_id }"
                                    btn-class="btn btn-sm btn-danger btn-icon-split m-1" :btnDisabled="statusBlockEdit()">
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

                        <tr class="table-subheader">

                            <th class="text-left" colspan="11">
                                {{ evtr.transport.national ? "Nacional" : "Internacional" }}
                                {{ evtr.transport.national ? evtr.transport.city.name + ' - ' + evtr.transport.city.states :  evtr.transport.city.name + ' - ' + evtr.transport.city.country }}
                            </th>

                            <th colspan="2" class="align-middle">Valor de Venda</th>
                            <th colspan="2" class="align-middle">Valor de Custo</th>
                            <th colspan="2"></th>
                            <template v-if="showDetails">
                                <th class="align-middle" colspan="2">ISS</th>
                                <th class="align-middle" colspan="2">Serviço</th>
                                <th class="align-middle" colspan="2">IVA</th>
                                <th class="align-middle" colspan="2">Tx. Turismo</th>
                            </template>
                            <th class="align-middle"></th>
                        </tr>
                        <tr class="table-header-c1">
                            <th class="align-middle">Broker</th>
                            <th class="align-middle">Veículo</th>
                            <th class="align-middle">Modelo Uso</th>
                            <th class="align-middle">Serviços</th>
                            <th class="align-middle">Marcas</th>
                            <th class="align-middle">OBS</th>
                            <th class="align-middle">IN</th>
                            <th class="align-middle">OUT</th>
                            <th class="align-middle">QTD</th>
                            <th class="align-middle">Dias</th>
                            <th class="align-middle">Comissão (%)</th>

                            <th class="align-middle">Unidade</th>
                            <th class="align-middle">Total</th>
                            <th class="align-middle">Unidade</th>
                            <th class="align-middle">Custo TTL</th>

                            <th class="align-middle">Proposta Recebida</th>
                            <th class="align-middle">%</th>
                            <template v-if="showDetails">
                                <th class="align-middle">Cliente {{ evtr.iss_percent }}%</th>
                                <th class="align-middle">Custo {{ evtr.iss_percent }}%</th>
                                <th class="align-middle">Cliente {{ evtr.service_percent }}%</th>
                                <th class="align-middle">Custo {{ evtr.service_percent }}%</th>
                                <th class="align-middle">Cliente {{ evtr.iva_percent }}%</th>
                                <th class="align-middle">Custo {{ evtr.iva_percent }}%</th>
                                <th class="align-middle">Cliente {{ evtr.service_charge }}%</th>
                                <th class="align-middle">Custo {{ evtr.service_charge }}%</th>
                            </template>
                            <th class="align-middle"></th>
                        </tr>

                        <!-- Opt TRs -->
                        <tr v-if="evtr.event_transport_opts.length == 0">
                            <td :colspan="showDetails ? 29 : 21">
                                <div class="alert alert-primary" role="alert">
                                    Nenhuma opção para transporte cadastrada!
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
                            <td class="align-middle">
                                {{ daysBetween(opt.in, opt.out) }}
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ opt.kickback }}
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(unitSale(opt), evtr.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(unitSale(opt) * daysBetween(opt.in, opt.out) * opt.count, evtr.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(unitCost(opt), evtr.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(unitCost(opt) * daysBetween(opt.in, opt.out) *
                                    opt.count, evtr.currency.sigla) }}
                            </td>
                            <td class=" align-middle">{{
                                formatCurrency(opt.received_proposal, evtr.currency.sigla)
                            }}</td>
                            <td class="align-middle">{{
                                opt.received_proposal_percent
                            }}
                            </td>
                            <template v-if="showDetails">
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((unitSale(opt) * evtr.iss_percent) / 100, evtr.currency.sigla) }}</b>
                                </td>
                                <td class=" align-middle text-success">
                                    <b>{{ formatCurrency((unitCost(opt) * evtr.iss_percent) / 100, evtr.currency.sigla) }}</b>
                                </td>

                                <td class="align-middle">
                                    <b>{{ formatCurrency((unitSale(opt) * evtr.service_percent) / 100, evtr.currency.sigla) }}</b>
                                </td>
                                <td class=" align-middle">
                                    <b>{{ formatCurrency((unitCost(opt) * evtr.service_percent) / 100, evtr.currency.sigla) }}</b>
                                </td>

                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((unitSale(opt) * evtr.iva_percent) / 100, evtr.currency.sigla) }}</b>
                                </td>
                                <td class=" align-middle text-success">
                                    <b>{{ formatCurrency((unitCost(opt) * evtr.iva_percent) / 100, evtr.currency.sigla) }}</b>
                                </td>

                                <td class="align-middle">
                                    <b>{{ formatCurrency((unitSale(opt) * evtr.service_charge) / 100, evtr.currency.sigla) }}</b>
                                </td>
                                <td class=" align-middle">
                                    <b>{{ formatCurrency((unitCost(opt) * evtr.service_charge) / 100, evtr.currency.sigla) }}</b>
                                </td>
                            </template>
                            <td class="align-middle">
                                <div class="d-flex">
                                    <PrimaryButton
                                        :disabled="!(eventTransport != null && eventTransport.id > 0 && eventTransport.id == opt.event_transport_id) || statusBlockEdit()"
                                        type="button" css-class="btn btn-info btn-circle btn-sm text-white" title="Editar"
                                        v-on:click="editOpt(opt)">
                                        <i class="fas fa-edit"></i>
                                    </PrimaryButton>

                                    <PrimaryButton
                                        :disabled="!(eventTransport != null && eventTransport.id > 0 && eventTransport.id == opt.event_transport_id) || statusBlockEdit()"
                                        type="button" css-class="btn btn-info btn-circle btn-sm text-white" title="Duplicar"
                                        v-on:click="duplicate(opt)">
                                        <i class="fas fa-clone"></i>
                                    </PrimaryButton>

                                    <Modal :key="index" :modal-title="'Confirmar Remoção'" :ok-botton-callback="deleteOpt"
                                        :ok-botton-callback-param="opt.id"
                                        btn-class="btn btn-danger btn-circle btn-sm text-white"
                                        :btnDisabled="statusBlockEdit()">
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
                        <tr class="table-subheader">
                            <td colspan="7"></td>
                            <td class="align-middle text-rigth">Veículos:</td>
                            <td class="align-middle">{{ sumCount(evtr) }}</td>
                            <td class="align-middle">
                                {{ sumNts(evtr) }}
                            </td>
                            <td class="align-middle bg-success text-white" colspan="2">
                                Total venda:
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(sumSale(evtr), evtr.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-warning text-dark text-rigth">
                                Total Custo
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(sumCost(evtr), evtr.currency.sigla) }}
                            </td>
                            <td class="align-middle text-rigth">
                                Média %
                            </td>
                            <td class="align-middle">
                                {{
                                    new Intl.NumberFormat({
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }).format((1 - (sumCost(evtr) / sumSale(evtr))) * 100)
                                }}
                            </td>
                            <template v-if="showDetails">
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency(sumTaxes(evtr, 'iss'), evtr.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((sumCost(evtr) * evtr.iss_percent) / 100, evtr.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency(sumTaxes(evtr, 'serv'), evtr.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency((sumCost(evtr) * evtr.service_percent) / 100, evtr.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency(sumTaxes(evtr, 'iva'), evtr.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((sumCost(evtr) * evtr.iva_percent) / 100, evtr.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency(sumTaxes(evtr, 'sc'), evtr.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency((sumCost(evtr) * evtr.service_charge) / 100, evtr.currency.sigla) }}</b>
                                </td>
                            </template>
                            <td class="align-middle"></td>
                        </tr>

                        <tr>
                            <td class="align-middle text-dark text-left" colspan="3">
                                <b>OBSERVAÇÃO INTERNA:</b>
                            </td>
                            <td class="align-middle text-dark text-left" colspan="10">
                                <b>{{ evtr.internal_observation }}</b>
                            </td>
                            <td class="align-middle" colspan="2">
                                Venda
                            </td>
                            <td class="align-middle" colspan="2">
                                <b>{{ formatCurrency(sumSale(evtr) + sumTaxes(evtr, 'iss') +
                                    sumTaxes(evtr, 'serv') + sumTaxes(evtr, 'iva') + sumTaxes(evtr, 'sc'), evtr.currency.sigla) }}</b>
                            </td>
                            <template v-if="showDetails">
                                <td colspan="8"></td>
                            </template>
                            <td></td>
                        </tr>

                        <tr>
                            <td class="align-middle text-dark text-left" colspan="3">
                                <b>OBSERVAÇÃO CLIENTE:</b>
                            </td>
                            <td class="align-middle text-dark text-left" colspan="10">
                                <b>{{ evtr.customer_observation }}</b>
                            </td>

                            <td class="align-middle" colspan="2">
                                Custo
                            </td>
                            <td class="align-middle" colspan="2">
                                <b>{{ formatCurrency(((sumCost(evtr) * evtr.iss_percent) / 100) +
                                    ((sumCost(evtr) * evtr.service_percent) / 100) + ((sumCost(evtr) *
                                    evtr.iva_percent) / 100) + sumCost(evtr), evtr.currency.sigla) }}
                                </b>
                            </td>
                          

                            <template v-if="showDetails">
                                <td colspan="8">
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
