<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import Loader from '@/Components/Loader.vue';

const isLoader = ref(false);

const props = defineProps({
    eventABs: {
        type: Array,
        default: []
    },
    eventAB: {
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
    newEventAb: {
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
    if (props.eventAb != null) {
        sigla = props.eventAb.currency.sigla;
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

const roomNights = (evab) => {
    let sum = 0;
    for (const opt of evab.event_ab_opts) {
        sum += (opt.count * daysBetween(opt.in, opt.out));
    }
    return sum;
}

const average = (evab) => {
    let sum = 0;
    for (const opt of evab.event_ab_opts) {
        sum += unitSale(opt);
    }
    return sum / evab.event_ab_opts.length;
}

const sumCount = (evab) => {
    let sum = 0;
    for (const opt of evab.event_ab_opts) {
        sum += opt.count;
    }
    return sum;
}

const sumNts = (evab) => {
    let sum = 0;
    for (const opt of evab.event_ab_opts) {
        sum += daysBetween(opt.in, opt.out);
    }
    return sum;
}

const sumSale = (evab) => {
    let sum = 0;
    for (const opt of evab.event_ab_opts) {
        sum += unitSale(opt) * daysBetween(opt.in, opt.out) * opt.count;
    }
    return sum;
}

const sumCost = (evab) => {
    let sum = 0;
    for (const opt of evab.event_ab_opts) {
        sum += unitCost(opt) * daysBetween(opt.in, opt.out) * opt.count;
    }
    return sum;
}

const sumTaxes = (evab, taxType) => {
    let sum = 0;
    for (const opt of evab.event_ab_opts) {
        switch (taxType) {
            case 'iss':
                sum += ((unitSale(opt) * evab.iss_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
            case 'serv':
                sum += ((unitSale(opt) * evab.service_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
            case 'iva':
                sum += ((unitSale(opt) * evab.iva_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
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

const deleteEventHotel = (data) => {
    isLoader.value = true;
    formDelete.id = data.id;
    formDelete.event_id = data.event_id;
    formDelete.delete(route('event-ab-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset()
            props.mountCallBack();
            props.newEventAb('ab');
        },
    });
};

const showDetails = ref(false);
</script>


<template>
    <Loader v-bind:show="isLoader"></Loader>

    <div class="row">
        <div v-if="eventABs.length == 0" class="alert alert-primary" role="alert">
            Nenhum fornecedor cadastrado!
        </div>
        <PrimaryButton v-if="eventABs.length > 0" type="button" css-class="btn btn-success btn-sm btn-icon-split m-1"
            :title="showDetails ? 'Ocultar' : 'Exibir'" v-on:click="showDetails = !showDetails">
            <span class="icon text-white-50">
                <i class="fas" v-bind:class="{ 'fa-eye': showDetails, 'fa-eye-slash': !showDetails }"></i>
            </span>
            <span class="text m2">{{ showDetails ? 'Ocultar' : 'Exibir' }} Detalhes</span>
        </PrimaryButton>
        <div class="table-responsive">
            <table class="table table-sm table-bordered text-center" width="100%" cellspacing="0">

                <tbody>
                    <template v-for="(evab, index) in eventABs" :key="evab.id">

                        <tr class="bg-light text-dark thead-dark">
                            <th class="text-left" :colspan="showDetails ? 19 : 13">
                                Hotel {{ index + 1 }} | {{ evab.ab.name }} | {{ evab.ab.national
                                    ?
                                    "Nacional" : "Internacional" }}
                                {{ evab.ab.city }}
                            </th>
                            <th class="align-middle text-right" colspan="3">
                                <Link class="btn btn-info btn-sm btn-icon-split"
                                    :href="route('event-edit', { 'id': evab.event_id, 'tab': 2, 'ehotel': evab.id })">
                                <span class="icon text-white-50">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span class="text">Editar</span>
                                </Link>

                                <Modal modal-title="Confirmar Remoção" :ok-botton-callback="deleteEventHotel"
                                    :ok-botton-callback-param="{ 'id': evab.id, 'event_id': evab.event_id }"
                                    btn-class="btn btn-sm btn-danger btn-icon-split m-1">
                                    <template v-slot:button>
                                        <span class="icon text-white-50">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                        <span class="text">Excluir</span>
                                    </template>
                                    <template v-slot:content>
                                        <span class="text-dark">Tem certeza que deseja remover o hotel {{
                                            evab.ab.name
                                        }} do evento {{ evab.ab.name }}</span>
                                    </template>
                                </Modal>
                            </th>
                        </tr>

                        <tr class="thead-dark">
                            <th class="align-middle" rowspan="2" scope="col">Serviço</th>
                            <th class="align-middle" rowspan="2" scope="col">Broker</th>
                            <th class="align-middle" rowspan="2" scope="col">Tipo Serviço</th>
                            <th class="align-middle" rowspan="2" scope="col">Local</th>
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
                                <th class="align-middle">{{ eventAb != null ? eventAb.percentISS : 0 }}%</th>
                                <th class="align-middle">ISS</th>
                                <th class="align-middle">{{ eventAb != null ? eventAb.percentIService : 0 }}%</th>
                                <th class="align-middle">Servico</th>
                                <th class="align-middle">{{ eventAb != null ? eventAb.percentIVA : 0 }}%</th>
                                <th class="align-middle">IVA</th>
                            </template>
                        </tr>

                        <tr v-if="evab.event_ab_opts.length == 0">
                            <td :colspan="showDetails ? 19 : 13">
                                <div class="alert alert-primary" role="alert">
                                    Nenhum fornecedor cadastrado!
                                </div>
                            </td>
                        </tr>

                        <!-- Opt TRs -->
                        <tr v-for="opt in evab.event_ab_opts">
                            <td class="align-middle">{{ opt.service.name }}</td>
                            <td class="align-middle">{{ opt.broker.name }}</td>
                            <td class="align-middle">{{ opt.service_type.name }}</td>
                            <td class="align-middle">{{ opt.local.name }}</td>
                            <td class="align-middle">{{ new Date(opt.in).toLocaleDateString() }}</td>
                            <td class="align-middle">{{ new Date(opt.out).toLocaleDateString() }}</td>
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
                                    {{ evab.iss_percent }}</td>
                                <td class=" align-middle bg-secondary text-white">
                                    {{ formatCurrency((unitSale(opt) * evab.iss_percent) / 100) }}
                                </td>
                                <td class="align-middle bg-secondary text-white">
                                    {{ evab.service_percent }}
                                </td>
                                <td class=" align-middle bg-secondary text-white">
                                    {{ formatCurrency(((unitSale(opt)) * evab.service_percent) /
                                        100) }}
                                </td>
                                <td class="align-middle bg-secondary text-white">{{
                                    evab.iva_percent
                                }}</td>
                                <td class=" align-middle bg-secondary text-white">
                                    {{ formatCurrency(((unitSale(opt)) * evab.iva_percent) / 100) }}
                                </td>
                            </template>
                            <td class="align-middle">
                                <div class="d-flex">
                                    <PrimaryButton
                                        :disabled="!(eventAB != null && eventAB.id > 0 && eventAB.id == opt.event_ab_id)"
                                        type="button" css-class="btn btn-info btn-circle btn-sm text-white" title="Editar"
                                        v-on:click="editOpt(opt)">
                                        <i class="fas fa-edit"></i>
                                    </PrimaryButton>

                                    <PrimaryButton
                                        :disabled="!(eventAB != null && eventAB.id > 0 && eventAB.id == opt.event_ab_id)"
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
                            <td class="align-middle bg-warning text-dark text-rigth">
                                Diária Média:
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(average(evab)) }}
                            </td>
                            <td class="align-middle"></td>
                            <td class="align-middle bg-warning text-dark text-rigth">
                                Room Nights:
                            </td>
                            <td class="align-middle">{{ roomNights(evab) }}</td>
                            <td class="align-middle text-rigth"># Aptos:</td>
                            <td class="align-middle">{{ sumCount(evab) }}</td>
                            <td class="align-middle bg-warning text-dark">
                                {{ sumNts(evab) }}
                            </td>
                            <td class="align-middle bg-success text-white" colspan="2">
                                Total venda:
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(sumSale(evab)) }}
                            </td>
                            <td class="align-middle bg-warning text-dark text-rigth">
                                Total Custo
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(sumCost(evab)) }}
                            </td>
                            <td class="align-middle bg-warning text-dark text-rigth">
                                Média %
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{
                                    new Intl.NumberFormat({
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }).format((1 - (sumCost(evab) / sumSale(evab))) * 100)
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
                            <td class="align-middle text-dark text-left" colspan="9">
                                {{ evab.internal_observation }}
                            </td>
                            <template v-if="showDetails">
                                <td class="align-middle" colspan="3"></td>
                                <td class="align-middle bg-success text-white">
                                    {{ formatCurrency(sumTaxes(evab, 'iss')) }}
                                </td>
                                <td class="align-middle">
                                    {{ formatCurrency((sumCost(evab) * evab.iss_percent) / 100) }}
                                </td>
                                <td class="align-middle bg-success text-white">
                                    {{ formatCurrency(sumTaxes(evab, 'serv')) }}
                                </td>
                                <td class="align-middle">
                                    {{ formatCurrency((sumCost(evab) * evab.service_percent) / 100) }}
                                </td>
                                <td class="align-middle bg-success text-white">
                                    {{ formatCurrency(sumTaxes(evab, 'iva')) }}
                                </td>
                                <td class="align-middle">
                                    {{ formatCurrency((sumCost(evab) * evab.iva_percent) / 100) }}
                                </td>
                            </template>
                            <td class="align-middle"></td>
                        </tr>

                        <tr>
                            <td class="align-middle text-dark text-left" colspan="3">
                                OBSERVAÇÃO CLIENTE:
                            </td>
                            <td class="align-middle text-dark text-left" colspan="9">
                                {{ evab.customer_observation }}
                            </td>

                            <template v-if="showDetails">
                                <td class="align-middle" colspan="3"></td>
                                <td class="align-middle bg-success text-white">
                                    Venda
                                </td>
                                <td class="align-middle bg-success text-white" colspan="2">
                                    {{ formatCurrency(sumSale(evab) + sumTaxes(evab, 'iss') +
                                        sumTaxes(evab, 'serv') + sumTaxes(evab, 'iva')) }}
                                </td>
                                <td class="align-middle bg-warning text-white">
                                    Custo
                                </td>
                                <td class="align-middle bg-warning text-white" colspan="2">
                                    {{ formatCurrency(((sumCost(evab) * evab.iss_percent) / 100) +
                                        ((sumCost(evab) * evab.service_percent) / 100) + ((sumCost(evab) *
                                            evab.iva_percent) / 100) + sumCost(evab)) }}
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
