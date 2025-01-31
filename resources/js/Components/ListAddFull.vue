<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import Loader from '@/Components/Loader.vue';

const isLoader = ref(false);

const props = defineProps({
    eventAdds: {
        type: Array,
        default: []
    },
    eventAdd: {
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
    newEventAdd: {
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
    if (props.eventAdd && props.eventAdd.status_history) {
        var status = props.eventAdd.status_history.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0].status; // TODO ordenar por data e pegar o ultimo registro

        return (status == "dating_with_customer" || status == "Cancelled")
    }
    return false;
}

const formatCurrency = (value, sigla = 'BRL') => {
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

const roomNights = (evAdd) => {
    let sum = 0;
    for (const opt of evAdd.event_add_opts) {
        sum += (opt.count * daysBetween(opt.in, opt.out));
    }
    return sum;
}

const average = (evAdd) => {
    let sum = 0;
    for (const opt of evAdd.event_add_opts) {
        sum += unitSale(opt);
    }
    return sum / evAdd.event_add_opts.length;
}

const sumCount = (evAdd) => {
    let sum = 0;
    for (const opt of evAdd.event_add_opts) {
        sum += opt.count;
    }
    return sum;
}

const sumNts = (evAdd) => {
    let sum = 0;
    for (const opt of evAdd.event_add_opts) {
        sum += daysBetween(opt.in, opt.out);
    }
    return sum;
}

const sumSale = (evAdd) => {
    let sum = 0;
    for (const opt of evAdd.event_add_opts) {
        sum += unitSale(opt) * daysBetween(opt.in, opt.out) * opt.count;
    }
    return sum;
}

const sumCost = (evAdd) => {
    let sum = 0;
    for (const opt of evAdd.event_add_opts) {
        sum += unitCost(opt) * daysBetween(opt.in, opt.out) * opt.count;
    }
    return sum;
}

const sumTaxes = (evAdd, taxType) => {
    let sum = 0;
    for (const opt of evAdd.event_add_opts) {
        switch (taxType) {
            case 'iss':
                sum += ((unitSale(opt) * evAdd.iss_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
            case 'serv':
                sum += ((unitSale(opt) * evAdd.service_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
            case 'iva':
                sum += ((unitSale(opt) * evAdd.iva_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
            case 'sc':
                sum += ((unitSale(opt) * evAdd.service_charge) / 100) * daysBetween(opt.in, opt.out) * opt.count;
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
    formDelete.delete(route('event-add-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset()
            props.mountCallBack();
            props.newEventAdd('add');
        },
    });
};

const showDetails = ref(false);
</script>


<template>
    <Loader v-bind:show="isLoader"></Loader>

    <div class="row">
        <div v-if="eventAdds.length == 0" class="alert alert-primary" role="alert">
            Nenhum cadastro de adicional!
        </div>
        <PrimaryButton v-if="eventAdds.length > 0" type="button" css-class="btn btn-success btn-sm btn-icon-split m-1"
            :title="showDetails ? 'Ocultar' : 'Exibir'" v-on:click="showDetails = !showDetails">
            <span class="icon text-white-50">
                <i class="fas" v-bind:class="{ 'fa-eye': showDetails, 'fa-eye-slash': !showDetails }"></i>
            </span>
            <span class="text m2">{{ showDetails ? 'Ocultar' : 'Exibir' }} Detalhes</span>
        </PrimaryButton>
        <div class="table-responsive">
            <table class="table table-sm table-bordered text-center" width="100%" cellspacing="0">

                <tbody>
                    <template v-for="(evAdd, index) in eventAdds" :key="evAdd.id">

                        <tr>
                            <th class="table-header table-header-c1" colspan="2">Hotel {{ index + 1 }}</th>
                            <th class="text-left table-header table-header-c2" :colspan="showDetails ? 20 : 12">
                                {{ evAdd.add.name }}
                            </th>

                            <th class="align-middle text-right table-header-c1 table-header" colspan="3">
                                <Link class="btn btn-info btn-sm btn-icon-split" :disabled="statusBlockEdit()"
                                    :href="route('event-edit', { 'id': evAdd.event_id, 'tab': 4, 'ehotel': evAdd.id })">
                                <span class="icon text-white-50">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span class="text">Editar</span>
                                </Link>

                                <Modal modal-title="Confirmar Remoção" :ok-botton-callback="deleteEventHotel"
                                    :ok-botton-callback-param="{ 'id': evAdd.id, 'event_id': evAdd.event_id }"
                                    btn-class="btn btn-sm btn-danger btn-icon-split m-1" :btnDisabled="statusBlockEdit()">
                                    <template v-slot:button>
                                        <span class="icon text-white-50">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                        <span class="text">Excluir</span>
                                    </template>
                                    <template v-slot:content>
                                        <span class="text-dark">Tem certeza que deseja remover o hotel {{
                                            evAdd.add.name
                                        }} do evento {{ evAdd.add.name }}</span>
                                    </template>
                                </Modal>
                            </th>

                        </tr>

                        <tr class="table-subheader">
                            <th class="text-left" colspan="10">
                                {{ evAdd.add.national ? "Nacional" : "Internacional" }}
                            </th>

                            <th colspan="2" class="align-middle" scope="col">Valor de Venda</th>
                            <th colspan="2" class="align-middle" scope="col">Valor de Custo</th>
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
                            <th class="align-middle">Serviço</th>
                            <th class="align-middle">Unidade utilizada</th>
                            <th class="align-middle">Medida</th>
                            <th class="align-middle">#PAX</th>
                            <th class="align-middle">Frequência</th>
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
                                <th class="align-middle">Cliente {{ evAdd.iss_percent }}%</th>
                                <th class="align-middle">Custo {{ evAdd.iss_percent }}%</th>
                                <th class="align-middle">Cliente {{ evAdd.service_percent }}%</th>
                                <th class="align-middle">Custo {{ evAdd.service_percent }}%</th>
                                <th class="align-middle">Cliente {{ evAdd.iva_percent }}%</th>
                                <th class="align-middle">Custo {{ evAdd.iva_percent }}%</th>
                                <th class="align-middle">Cliente {{ evAdd.service_charge }}%</th>
                                <th class="align-middle">Custo {{ evAdd.service_charge }}%</th>
                            </template>
                            <th class="align-middle"></th>
                        </tr>

                        <!-- Opt TRs -->
                        <tr v-if="evAdd.event_add_opts.length == 0">
                            <td :colspan="showDetails ? 28 : 20">
                                <div class="alert alert-primary" role="alert">
                                    Nenhuma opção para adicional cadastrada!
                                </div>
                            </td>
                        </tr>
                        <tr v-for="opt in evAdd.event_add_opts">
                            <td class="align-middle">{{ opt.service.name }}</td>
                            <td class="align-middle">{{ opt.unit }}</td>
                            <td class="align-middle">{{ opt.measure.name }}</td>
                            <td class="align-middle">{{ opt.pax }}</td>
                            <td class="align-middle">{{ opt.frequency.name }}</td>
                            <td class="align-middle">{{ new Date(opt.in).toLocaleDateString() }}</td>
                            <td class="align-middle">{{ new Date(opt.out).toLocaleDateString() }}</td>
                            <td class="align-middle">{{ opt.count }}</td>
                            <td class="align-middle">
                                {{ daysBetween(opt.in, opt.out) }}
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ opt.kickback }}
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(unitSale(opt), evAdd.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(unitSale(opt) * daysBetween(opt.in, opt.out) * opt.count, evAdd.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(unitCost(opt), evAdd.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(unitCost(opt) * daysBetween(opt.in, opt.out) *
                                    opt.count, evAdd.currency.sigla) }}
                            </td>
                            <td class=" align-middle">{{
                                formatCurrency(opt.received_proposal, evAdd.currency.sigla)
                            }}</td>
                            <td class="align-middle">{{
                                opt.received_proposal_percent
                            }}
                            </td>
                            <template v-if="showDetails">

                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((unitSale(opt) * evAdd.iss_percent) / 100, evAdd.currency.sigla) }}</b>
                                </td>
                                <td class=" align-middle text-success">
                                    <b>{{ formatCurrency((unitCost(opt) * evAdd.iss_percent) / 100, evAdd.currency.sigla) }}</b>
                                </td>

                                <td class="align-middle">
                                    <b>{{ formatCurrency((unitSale(opt) * evAdd.service_percent) / 100, evAdd.currency.sigla) }}</b>
                                </td>
                                <td class=" align-middle">
                                    <b>{{ formatCurrency((unitCost(opt) * evAdd.service_percent) / 100, evAdd.currency.sigla) }}</b>
                                </td>

                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((unitSale(opt) * evAdd.iva_percent) / 100, evAdd.currency.sigla) }}</b>
                                </td>
                                <td class=" align-middle text-success">
                                    <b>{{ formatCurrency((unitCost(opt) * evAdd.iva_percent) / 100, evAdd.currency.sigla) }}</b>
                                </td>

                                <td class="align-middle">
                                    <b>{{ formatCurrency((unitSale(opt) * evAdd.service_charge) / 100, evAdd.currency.sigla) }}</b>
                                </td>
                                <td class=" align-middle">
                                    <b>{{ formatCurrency((unitCost(opt) * evAdd.service_charge) / 100, evAdd.currency.sigla) }}</b>
                                </td>

                            </template>
                            <td class="align-middle">
                                <div class="d-flex">
                                    <PrimaryButton
                                        :disabled="!(eventAdd != null && eventAdd.id > 0 && eventAdd.id == opt.event_add_id) || statusBlockEdit()"
                                        type="button" css-class="btn btn-info btn-circle btn-sm text-white" title="Editar"
                                        v-on:click="editOpt(opt)">
                                        <i class="fas fa-edit"></i>
                                    </PrimaryButton>

                                    <PrimaryButton
                                        :disabled="!(eventAdd != null && eventAdd.id > 0 && eventAdd.id == opt.event_add_id) || statusBlockEdit()"
                                        type="button" css-class="btn btn-info btn-circle btn-sm text-white" title="Duplicar"
                                        v-on:click="duplicate(opt)">
                                        <i class="fas fa-clone"></i>
                                    </PrimaryButton>

                                    <Modal :key="index" :modal-title="'Confirmar Remoção'" :ok-botton-callback="deleteOpt"
                                        :ok-botton-callback-param="opt.id" :btnDisabled="statusBlockEdit()"
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
                        <tr class="table-subheader">
                            <td class="align-middle bg-warning text-dark text-rigth">
                                Diária Média:
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(average(evAdd), evAdd.currency.sigla) }}
                            </td>
                            <td class="align-middle"></td>
                            <td class="align-middle bg-warning text-dark text-rigth" colspan="2">
                                Room Nights:
                            </td>
                            <td class="align-middle">{{ roomNights(evAdd) }}</td>
                            <td class="align-middle text-rigth"># Aptos:</td>
                            <td class="align-middle">{{ sumCount(evAdd) }}</td>
                            <td class="align-middle">
                                {{ sumNts(evAdd) }}
                            </td>
                            <td class="align-middle bg-success text-white" colspan="2">
                                Total venda:
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(sumSale(evAdd), evAdd.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-warning text-dark text-rigth">
                                Total Custo
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(sumCost(evAdd), evAdd.currency.sigla) }}
                            </td>
                            <td class="align-middle text-rigth">
                                Média %
                            </td>
                            <td class="align-middle">
                                {{
                                    new Intl.NumberFormat({
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }).format((1 - (sumCost(evAdd) / sumSale(evAdd))) * 100)
                                }}
                            </td>

                            <template v-if="showDetails">
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency(sumTaxes(evAdd, 'iss'), evAdd.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((sumCost(evAdd) * evAdd.iss_percent) / 100, evAdd.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency(sumTaxes(evAdd, 'serv'), evAdd.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency((sumCost(evAdd) * evAdd.service_percent) / 100, evAdd.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency(sumTaxes(evAdd, 'iva'), evAdd.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((sumCost(evAdd) * evAdd.iva_percent) / 100, evAdd.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency(sumTaxes(evAdd, 'sc'), evAdd.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency((sumCost(evAdd) * evAdd.service_charge) / 100, evAdd.currency.sigla) }}</b>
                                </td>
                            </template>
                            <td class="align-middle"></td>
                        </tr>

                        <tr>
                            <td class="align-middle text-dark text-left" colspan="3">
                                OBSERVAÇÃO INTERNA:
                            </td>
                            <td class="align-middle text-dark text-left" colspan="9">
                                <b>{{ evAdd.customer_observation }}</b>
                            </td>
                            <td class="align-middle" colspan="2">
                                Venda
                            </td>
                            <td class="align-middle" colspan="2">
                                <b>{{ formatCurrency(sumSale(evAdd) + sumTaxes(evAdd, 'iss') +
                                    sumTaxes(evAdd, 'serv') + sumTaxes(evAdd, 'iva') + sumTaxes(evAdd, 'sc'), evAdd.currency.sigla) }}</b>
                            </td>
                            <template v-if="showDetails">
                                <td colspan="8"></td>
                            </template>
                            <td></td>
                        </tr>

                        <tr>
                            <td class="align-middle text-dark text-left" colspan="3">
                                OBSERVAÇÃO CLIENTE:
                            </td>
                            <td class="align-middle text-dark text-left" colspan="9">
                                <b>{{ evAdd.customer_observation }}</b>
                            </td>

                            <td class="align-middle" colspan="2">
                                Custo
                            </td>
                            <td class="align-middle" colspan="2">
                                <b>{{ formatCurrency(((sumCost(evAdd) * evAdd.iss_percent) / 100) +
                                    ((sumCost(evAdd) * evAdd.service_percent) / 100) + ((sumCost(evAdd) *
                                        evAdd.iva_percent) / 100) + sumCost(evAdd), evAdd.currency.sigla) }}</b>
                            </td>

                            <template v-if="showDetails">
                                <td colspan="8"></td>
                            </template>
                            <td></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
