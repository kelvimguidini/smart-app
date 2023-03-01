<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';
const props = defineProps({
    eventHotels: {
        type: Array,
        default: []
    },
    eventHotel: {
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
    if (props.eventHotel != null) {
        sigla = props.eventHotel.currency.sigla;
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

const roomNights = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += (opt.count * daysBetween(opt.in, opt.out));
    }
    return sum;
}

const average = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += unitSale(opt);
    }
    return sum / evho.event_hotels_opt.length;
}

const sumCount = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += opt.count;
    }
    return sum;
}

const sumNts = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += daysBetween(opt.in, opt.out);
    }
    return sum;
}

const sumSale = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += unitSale(opt) * daysBetween(opt.in, opt.out) * opt.count;
    }
    return sum;
}

const sumCost = (evho) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        sum += unitCost(opt) * daysBetween(opt.in, opt.out) * opt.count;
    }
    return sum;
}

const sumTaxes = (evho, taxType) => {
    let sum = 0;
    for (const opt of evho.event_hotels_opt) {
        switch (taxType) {
            case 'iss':
                sum += ((unitSale(opt) * evho.iss_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
            case 'serv':
                sum += ((unitSale(opt) * evho.service_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
            case 'iva':
                sum += ((unitSale(opt) * evho.iva_percent) / 100) * daysBetween(opt.in, opt.out) * opt.count;
                break;
        }

    }
    return sum;
}
//FIM FUNÇÕES GERAIS

const showDetails = ref(false);
</script>


<template>
    <div class="row">
        <PrimaryButton type="button" css-class="btn btn-success btn-sm btn-icon-split mr-2"
            :title="showDetails ? 'Ocultar' : 'Exibir'" v-on:click="showDetails = !showDetails">
            <span class="icon text-white-50">
                <i class="fas" v-bind:class="{ 'fa-eye-slash': showDetails, 'fa-eye': !showDetails }"></i>
            </span>
            <span class="text">{{ showDetails ? 'Ocultar' : 'Exibir' }} Detalhes</span>
        </PrimaryButton>
        <div class="table-responsive">
            <table class="table table-sm table-bordered text-center" width="100%" cellspacing="0">
                <thead class="thead-dark">
                    <tr>
                        <th class="align-middle" rowspan="2" scope="col">Broker</th>
                        <th class="align-middle" rowspan="2" scope="col">Regime</th>
                        <th class="align-middle" rowspan="2" scope="col">Proposito</th>
                        <th class="align-middle" rowspan="2" scope="col">CAT.</th>
                        <th class="align-middle" rowspan="2" scope="col">APTO</th>
                        <th class="align-middle" rowspan="2" scope="col">IN</th>
                        <th class="align-middle" rowspan="2" scope="col">OUT</th>
                        <th class="align-middle" rowspan="2" scope="col">QTD</th>
                        <th class="align-middle" rowspan="2" scope="col">NTS</th>
                        <th class="align-middle" rowspan="2" scope="col">Comissão (%)</th>
                        <th colspan="2" class="  align-middle" scope="col">Valor de Venda</th>
                        <th colspan="2" class="align-middle" scope="col">Valor de Custo</th>
                        <th class="align-middle" rowspan="2" scope="col">Proposta Recebida</th>
                        <th class="align-middle" rowspan="2" scope="col">%</th>
                        <template v-if="showDetails">
                            <th colspan="3" class="align-middle" scope="col">Comparativo</th>
                            <th colspan="6" class="align-middle" scope="col">
                                IMPOSTOS DESTACADOS POR SERVIÇOS
                            </th>
                        </template>
                        <th class="align-middle" rowspan="2" scope="col"></th>
                    </tr>
                    <tr>
                        <th class="align-middle">Unidade</th>
                        <th class="align-middle">Total</th>
                        <th class="align-middle">Unidade</th>
                        <th class="align-middle">Custo TTL</th>
                        <template v-if="showDetails">
                            <th class="align-middle">Trivago</th>
                            <th class="align-middle">Website HTL</th>
                            <th class="align-middle">Omnibess</th>
                            <th class="align-middle">{{ eventHotel != null ? eventHotel.percentISS : 0 }}%</th>
                            <th class="align-middle">ISS</th>
                            <th class="align-middle">{{ eventHotel != null ? eventHotel.percentIService : 0 }}%</th>
                            <th class="align-middle">Servico</th>
                            <th class="align-middle">{{ eventHotel != null ? eventHotel.percentIVA : 0 }}%</th>
                            <th class="align-middle">IVA</th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(evho, index) in eventHotels" :key="evho.id">

                        <tr class="bg-light text-dark thead-dark">
                            <th class="text-left" :colspan="showDetails ? 24 : 15">
                                Hotel {{ index + 1 }} || {{ evho.hotel.name }} || {{ evho.hotel.national
                                    ?
                                    "Nacional" : "Internacional" }}
                                {{ evho.hotel.city }}
                            </th>
                            <th class="align-middle text-right" colspan="2">
                                <Link class="btn btn-info btn-sm btn-icon-split mr-2"
                                    :href="route('event-edit', { 'id': evho.event_id, 'tab': 1, 'ehotel': evho.id })">
                                <span class="icon text-white-50">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span class="text">Editar</span>
                                </Link>
                            </th>
                        </tr>

                        <!-- Opt TRs -->
                        <tr v-for="opt in evho.event_hotels_opt">
                            <td class="align-middle">{{ opt.broker.name }}</td>
                            <td class="align-middle">{{ opt.regime.name }}</td>
                            <td class="align-middle">{{ opt.purpose.name }}</td>
                            <td class="align-middle">{{ opt.category_hotel.category.name }}
                            </td>
                            <td class="align-middle">{{ opt.apto_hotel.apto.name }}</td>
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
                                <td class=" align-middle bg-secondary text-white">{{
                                    formatCurrency(opt.compare_trivago)
                                }}
                                </td>
                                <td class=" align-middle bg-secondary text-white">{{
                                    formatCurrency(opt.compare_website_htl)
                                }}
                                </td>
                                <td class=" align-middle bg-secondary text-white">
                                    {{ formatCurrency(opt.compare_omnibess) }}
                                </td>
                                <td class="align-middle bg-secondary text-white">
                                    {{ evho.iss_percent }}</td>
                                <td class=" align-middle bg-secondary text-white">
                                    {{ formatCurrency((unitSale(opt) * evho.iss_percent) / 100) }}
                                </td>
                                <td class="align-middle bg-secondary text-white">
                                    {{ evho.service_percent }}
                                </td>
                                <td class=" align-middle bg-secondary text-white">
                                    {{ formatCurrency(((unitSale(opt)) * evho.service_percent) /
                                        100) }}
                                </td>
                                <td class="align-middle bg-secondary text-white">{{
                                    evho.iva_percent
                                }}</td>
                                <td class=" align-middle bg-secondary text-white">
                                    {{ formatCurrency(((unitSale(opt)) * evho.iva_percent) / 100) }}
                                </td>
                            </template>
                            <td class="align-middle">
                                <div class="d-flex">
                                    <PrimaryButton
                                        :disabled="!(eventHotel != null && eventHotel.id > 0 && eventHotel.id == opt.event_hotel_id)"
                                        type="button" css-class="btn btn-info btn-circle btn-sm text-white" title="Editar"
                                        v-on:click="editOpt(opt)">
                                        <i class="fas fa-edit"></i>
                                    </PrimaryButton>

                                    <PrimaryButton
                                        :disabled="!(eventHotel != null && eventHotel.id > 0 && eventHotel.id == opt.event_hotel_id)"
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
                            <td class="align-middle bg-warning text-dark">
                                Diária Média:
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(average(evho)) }}
                            </td>
                            <td class="align-middle"></td>
                            <td class="align-middle bg-warning text-dark" colspan="2">
                                Room Nights:
                            </td>
                            <td class="align-middle">{{ roomNights(evho) }}</td>
                            <td class="align-middle"># Aptos:</td>
                            <td class="align-middle">{{ sumCount(evho) }}</td>
                            <td class="align-middle bg-warning text-dark">
                                {{ sumNts(evho) }}
                            </td>
                            <td class="align-middle bg-success text-white" colspan="2">
                                Total venda:
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(sumSale(evho)) }}
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                Total Custo
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(sumCost(evho)) }}
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                Média %
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{
                                    new Intl.NumberFormat({
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }).format((1 - (sumCost(evho) / sumSale(evho))) * 100)
                                }}
                            </td>
                            <template v-if="showDetails">
                                <td class="align-middle" colspan="3"></td>
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
                            <td class="align-middle text-dark" colspan="3">
                                OBSERVAÇÃO INTERNA:
                            </td>
                            <td class="align-middle text-dark text-left" colspan="13">
                                {{ evho.internal_observation }}
                            </td>
                            <template v-if="showDetails">
                                <td class="align-middle" colspan="3"></td>
                                <td class="align-middle bg-success text-white">
                                    {{ formatCurrency(sumTaxes(evho, 'iss')) }}
                                </td>
                                <td class="align-middle">
                                    {{ formatCurrency((sumCost(evho) * evho.iss_percent) / 100) }}
                                </td>
                                <td class="align-middle bg-success text-white">
                                    {{ formatCurrency(sumTaxes(evho, 'serv')) }}
                                </td>
                                <td class="align-middle">
                                    {{ formatCurrency((sumCost(evho) * evho.service_percent) / 100) }}
                                </td>
                                <td class="align-middle bg-success text-white">
                                    {{ formatCurrency(sumTaxes(evho, 'iva')) }}
                                </td>
                                <td class="align-middle">
                                    {{ formatCurrency((sumCost(evho) * evho.iva_percent) / 100) }}
                                </td>
                            </template>
                            <td class="align-middle"></td>
                        </tr>

                        <tr>
                            <td class="align-middle text-dark" colspan="3">
                                OBSERVAÇÃO CLIENTE:
                            </td>
                            <td class="align-middle text-dark text-left" colspan="13">
                                {{ evho.customer_observation }}
                            </td>

                            <template v-if="showDetails">
                                <td class="align-middle" colspan="3"></td>
                                <td class="align-middle bg-success text-white">
                                    Venda
                                </td>
                                <td class="align-middle bg-success text-white" colspan="2">
                                    {{ formatCurrency(sumSale(evho) + sumTaxes(evho, 'iss') +
                                        sumTaxes(evho, 'serv') + sumTaxes(evho, 'iva')) }}
                                </td>
                                <td class="align-middle bg-warning text-white">
                                    Custo
                                </td>
                                <td class="align-middle bg-warning text-white" colspan="2">
                                    {{ formatCurrency(((sumCost(evho) * evho.iss_percent) / 100) +
                                        ((sumCost(evho) * evho.service_percent) / 100) + ((sumCost(evho) *
                                            evho.iva_percent) / 100) + sumCost(evho)) }}
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
