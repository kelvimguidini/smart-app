<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import Loader from '@/Components/Loader.vue';

const isLoader = ref(false);

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
    mountCallBack: {
        type: Function,
        default: null,
    },
    newEventHotel: {
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
    return Math.ceil(difference / (1000 * 60 * 60 * 24));
}


const statusBlockEdit = () => {
    if (props.eventHotel && props.eventHotel.status_history) {
        var status = props.eventHotel.status_history.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0].status; // TODO ordenar por data e pegar o ultimo registro

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
    if (opt.received_proposal_percent > 0) {
        return Math.ceil(unitCost(opt) / opt.received_proposal_percent);
    }
    return unitCost(opt);
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
        sum += parseFloat(opt.count);
    }
    return sum.toLocaleString('pt-BR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
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
            case 'sc':
                sum += evho.service_charge * daysBetween(opt.in, opt.out) * opt.count;
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
    formDelete.delete(route('event-hotel-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset()
            props.mountCallBack();
            props.newEventHotel('hotel');
        },
    });
};

const showDetails = ref(false);

onBeforeUnmount(() => {
    window.removeEventListener('resize', adjustStickyColumns);
    if (resizeObserver) resizeObserver.disconnect();
});

let resizeObserver;

const adjustStickyColumns = () => {
    const tables = document.querySelectorAll('table');

    tables.forEach(table => {
        const rows = table.querySelectorAll('tr');

        rows.forEach(row => {
            const stickyCols = row.querySelectorAll('.sticky-col');
            let leftOffset = 0;

            stickyCols.forEach(col => {
                col.style.left = `${leftOffset}px`;
                leftOffset += col.offsetWidth;
            });
        });
    });
};

onMounted(() => {
    nextTick(() => {
        adjustStickyColumns();

        // Atualiza se a janela for redimensionada
        window.addEventListener('resize', adjustStickyColumns);

        // Observa mudanças no layout (como troca de abas)
        resizeObserver = new ResizeObserver(adjustStickyColumns);
        document.querySelectorAll('table').forEach(table => resizeObserver.observe(table));
    });
});

</script>

<style scoped>
.sticky-col {
    position: sticky;
    z-index: 2;
}

.sticky-col::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: inherit;
    z-index: -1;
}

.bg-white {
    background-color: #fff !important;
}
</style>

<template>
    <Loader v-bind:show="isLoader"></Loader>

    <div class="row">
        <div v-if="eventHotels.length == 0" class="alert alert-primary" role="alert">
            Nenhum cadastro de hotel!
        </div>
        <PrimaryButton v-if="eventHotels.length > 0" type="button" css-class="btn btn-success btn-sm btn-icon-split m-1"
            :title="showDetails ? 'Ocultar' : 'Exibir'" v-on:click="showDetails = !showDetails">
            <span class="icon text-white-50">
                <i class="fas" v-bind:class="{ 'fa-eye': showDetails, 'fa-eye-slash': !showDetails }"></i>
            </span>
            <span class="text m2">{{ showDetails ? 'Ocultar' : 'Exibir' }} Detalhes</span>
        </PrimaryButton>
        <div class="table-responsive">
            <table class="table table-sm table-bordered text-center" width="100%" cellspacing="0">

                <tbody>
                    <template v-for="(evho, index) in eventHotels.sort((a, b) => a.order - b.order)" :key="evho.id">

                        <tr>
                            <th class="table-header table-header-c1 sticky-col" colspan="2">Hotel {{ index + 1 }}</th>
                            <th class="text-left table-header table-header-c2" :colspan="showDetails ? 23 : 12">
                                {{ evho.hotel?.name }}
                            </th>
                            <th class="align-middle text-right table-header-c1 table-header" colspan="3">
                                <Link class="btn btn-info btn-sm btn-icon-split" :disabled="statusBlockEdit()"
                                    :href="route('event-edit', { 'id': evho.event_id, 'tab': 1, 'ehotel': evho.id })">
                                <span class="icon text-white-50">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span class="text">Editar</span>
                                </Link>

                                <Modal modal-title="Confirmar Remoção" :ok-botton-callback="deleteEventHotel"
                                    :ok-botton-callback-param="{ 'id': evho.id, 'event_id': evho.event_id }"
                                    btn-class="btn btn-sm btn-danger btn-icon-split m-1"
                                    :btnDisabled="statusBlockEdit()">
                                    <template v-slot:button>
                                        <span class="icon text-white-50">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                        <span class="text">Excluir</span>
                                    </template>
                                    <template v-slot:content>
                                        <span class="text-dark text-left">Tem certeza que deseja remover o hotel {{
                                            evho.hotel.name
                                        }} do evento {{ evho.event.name }}</span>
                                    </template>
                                </Modal>
                            </th>
                        </tr>

                        <tr class="table-subheader">
                            <th colspan="10" class="text-left">{{ evho.hotel.national
                                ? "Nacional" : "Internacional" }}
                            </th>
                            <th colspan="2" class="align-middle">Valor de Venda</th>
                            <th colspan="2" class="align-middle">Valor de Custo</th>
                            <th colspan="2"></th>
                            <template v-if="showDetails">
                                <th class="align-middle" colspan="2">ISS</th>
                                <th class="align-middle" colspan="2">Serviço</th>
                                <th class="align-middle" colspan="2">IVA</th>
                                <th class="align-middle" colspan="2">Tx. Turismo</th>
                                <th colspan="3" class="align-middle">Comparativo</th>
                            </template>
                            <th class="align-middle"></th>
                        </tr>
                        <tr class="table-header-c1">
                            <th class="align-middle table-header-c1 sticky-col">Broker</th>
                            <th class="align-middle table-header-c1 sticky-col">Regime</th>
                            <th class="align-middle table-header-c1 sticky-col">Proposito</th>
                            <th class="align-middle table-header-c1 sticky-col">CAT.</th>
                            <th class="align-middle table-header-c1 sticky-col">APTO</th>
                            <th class="align-middle table-header-c1 sticky-col">IN</th>
                            <th class="align-middle table-header-c1 sticky-col">OUT</th>
                            <th class="align-middle table-header-c1 sticky-col">QTD</th>
                            <th class="align-middle table-header-c1 sticky-col">NTS</th>
                            <th class="align-middle">Comissão (%)</th>

                            <th class="align-middle">Unidade</th>
                            <th class="align-middle">Total</th>
                            <th class="align-middle">Unidade</th>
                            <th class="align-middle">Custo TTL</th>
                            <th class="align-middle">Proposta Recebida</th>
                            <th class="align-middle">%</th>
                            <template v-if="showDetails">
                                <th class="align-middle">Cliente {{ evho.iss_percent }}%</th>
                                <th class="align-middle">Custo {{ evho.iss_percent }}%</th>
                                <th class="align-middle">Cliente {{ evho.service_percent }}%</th>
                                <th class="align-middle">Custo {{ evho.service_percent }}%</th>
                                <th class="align-middle">Cliente {{ evho.iva_percent }}%</th>
                                <th class="align-middle">Custo {{ evho.iva_percent }}%</th>
                                <th class="align-middle">Cliente</th>
                                <th class="align-middle">Custo</th>

                                <th class="align-middle">Trivago</th>
                                <th class="align-middle">Website HTL</th>
                                <th class="align-middle">Omnibess</th>
                            </template>
                            <th class="align-middle"></th>
                        </tr>

                        <!-- Opt TRs -->
                        <tr v-if="evho.event_hotels_opt.length == 0">
                            <td :colspan="showDetails ? 28 : 17">
                                <div class="alert alert-primary" role="alert">
                                    Nenhuma opção para hotel cadastrada!
                                </div>
                            </td>
                        </tr>

                        <tr v-for="opt in evho.event_hotels_opt">
                            <td class="align-middle bg-white sticky-col">{{ opt.broker?.name }}</td>
                            <td class="align-middle bg-white sticky-col">{{ opt.regime?.name }}</td>
                            <td class="align-middle bg-white sticky-col">{{ opt.purpose?.name }}</td>
                            <td class="align-middle bg-white sticky-col">{{ opt.category_hotel?.name }}</td>
                            <td class="align-middle bg-white sticky-col">{{ opt.apto_hotel?.name }}</td>
                            <td class="align-middle bg-white sticky-col">{{
                                new Date(opt.in).toLocaleDateString()
                            }}
                            </td>
                            <td class="align-middle bg-white sticky-col">{{
                                new Date(opt.out).toLocaleDateString()
                            }}
                            </td>
                            <td class="align-middle bg-white sticky-col">
                                {{ opt.count ? parseFloat(opt.count).toLocaleString('pt-BR', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits:
                                        2
                                }) : '' }}
                            </td>
                            <td class="align-middle bg-white sticky-col">
                                {{ daysBetween(opt.in, opt.out) }}
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ opt.kickback }}
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(unitSale(opt), evho.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(unitSale(opt) * daysBetween(opt.in, opt.out) * opt.count,
                                    evho.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(unitCost(opt), evho.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(unitCost(opt) * daysBetween(opt.in, opt.out) *
                                    opt.count, evho.currency.sigla) }}
                            </td>
                            <td class=" align-middle">{{
                                formatCurrency(opt.received_proposal, evho.currency.sigla)
                            }}</td>
                            <td class="align-middle">{{
                                opt.received_proposal_percent
                            }}
                            </td>
                            <template v-if="showDetails">

                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((unitSale(opt) * evho.iss_percent) / 100, evho.currency.sigla)
                                    }}</b>
                                </td>
                                <td class=" align-middle text-success">
                                    <b>{{ formatCurrency((unitCost(opt) * evho.iss_percent) / 100, evho.currency.sigla)
                                    }}</b>
                                </td>

                                <td class="align-middle">
                                    <b>{{ formatCurrency((unitSale(opt) * evho.service_percent) / 100,
                                        evho.currency.sigla) }}</b>
                                </td>
                                <td class=" align-middle">
                                    <b>{{ formatCurrency((unitCost(opt) * evho.service_percent) / 100,
                                        evho.currency.sigla) }}</b>
                                </td>

                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((unitSale(opt) * evho.iva_percent) / 100, evho.currency.sigla)
                                    }}</b>
                                </td>
                                <td class=" align-middle text-success">
                                    <b>{{ formatCurrency((unitCost(opt) * evho.iva_percent) / 100, evho.currency.sigla)
                                    }}</b>
                                </td>

                                <td class="align-middle">
                                    <b>{{ formatCurrency(evho.service_charge,
                                        evho.currency.sigla) }}</b>
                                </td>
                                <td class=" align-middle">
                                    <b>{{ formatCurrency(evho.service_charge,
                                        evho.currency.sigla) }}</b>
                                </td>

                                <td class="align-middle bg-secondary text-white">{{
                                    formatCurrency(opt.compare_trivago, evho.currency.sigla)
                                }}
                                </td>
                                <td class="align-middle bg-secondary text-white">{{
                                    formatCurrency(opt.compare_website_htl, evho.currency.sigla)
                                }}
                                </td>
                                <td class="align-middle bg-secondary text-white">
                                    {{ formatCurrency(opt.compare_omnibess, evho.currency.sigla) }}
                                </td>
                            </template>
                            <td class="align-middle">
                                <div class="d-flex">
                                    <PrimaryButton
                                        :disabled="!(eventHotel != null && eventHotel.id > 0 && eventHotel.id == opt.event_hotel_id) || statusBlockEdit()"
                                        type="button" css-class="btn btn-info btn-circle btn-sm text-white"
                                        title="Editar" v-on:click="editOpt(opt)">
                                        <i class="fas fa-edit"></i>
                                    </PrimaryButton>

                                    <PrimaryButton
                                        :disabled="!(eventHotel != null && eventHotel.id > 0 && eventHotel.id == opt.event_hotel_id) || statusBlockEdit()"
                                        type="button" css-class="btn btn-info btn-circle btn-sm text-white"
                                        title="Duplicar" v-on:click="duplicate(opt)">
                                        <i class="fas fa-clone"></i>
                                    </PrimaryButton>

                                    <Modal :key="index" :modal-title="'Confirmar Remoção'"
                                        :ok-botton-callback="deleteOpt" :ok-botton-callback-param="opt.id"
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
                            <td class="align-middle bg-warning text-dark text-rigth table-subheader sticky-col">
                                Diária Média:
                            </td>
                            <td class="align-middle bg-warning text-dark table-subheader sticky-col">
                                {{ formatCurrency(average(evho), evho.currency.sigla) }}
                            </td>
                            <td class="align-middle table-subheader sticky-col"></td>
                            <td class="align-middle bg-warning text-dark text-rigth table-subheader sticky-col"
                                colspan="2">
                                Room Nights:
                            </td>
                            <td class="align-middle table-subheader sticky-col">{{ roomNights(evho) }}</td>
                            <td class="align-middle text-rigth table-subheader sticky-col"># Aptos:</td>
                            <td class="align-middle table-subheader sticky-col">{{ sumCount(evho) }}</td>
                            <td class="align-middle table-subheader sticky-col">
                                {{ sumNts(evho) }}
                            </td>
                            <td class="align-middle bg-success text-white" colspan="2">
                                Total venda:
                            </td>
                            <td class="align-middle bg-success text-white">
                                {{ formatCurrency(sumSale(evho), evho.currency.sigla) }}
                            </td>
                            <td class="align-middle bg-warning text-dark text-rigth">
                                Total Custo
                            </td>
                            <td class="align-middle bg-warning text-dark">
                                {{ formatCurrency(sumCost(evho), evho.currency.sigla) }}
                            </td>
                            <td class="align-middle text-rigth">
                                Média %
                            </td>
                            <td class="align-middle">
                                {{
                                    new Intl.NumberFormat({
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }).format((1 - (sumCost(evho) / sumSale(evho))) * 100)
                                }}
                            </td>
                            <template v-if="showDetails">
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency(sumTaxes(evho, 'iss'), evho.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((sumCost(evho) * evho.iss_percent) / 100, evho.currency.sigla)
                                    }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency(sumTaxes(evho, 'serv'), evho.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency((sumCost(evho) * evho.service_percent) / 100,
                                        evho.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency(sumTaxes(evho, 'iva'), evho.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle text-success">
                                    <b>{{ formatCurrency((sumCost(evho) * evho.iva_percent) / 100, evho.currency.sigla)
                                    }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency(sumTaxes(evho, 'sc'), evho.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle">
                                    <b>{{ formatCurrency(evho.service_charge,
                                        evho.currency.sigla) }}</b>
                                </td>
                                <td class="align-middle" colspan="3"></td>
                            </template>
                            <td class="align-middle"></td>
                        </tr>

                        <tr>
                            <td class="align-middle text-dark text-left bg-white sticky-col" colspan="3">
                                OBSERVAÇÃO INTERNA:
                            </td>
                            <td class="align-middle text-dark text-left" colspan="9">
                                <b>{{ evho.internal_observation }}</b>
                            </td>
                            <td class="align-middle" colspan="2">
                                Venda
                            </td>
                            <td class="align-middle" colspan="2">
                                <b>{{ formatCurrency(sumSale(evho) + sumTaxes(evho, 'iss') +
                                    sumTaxes(evho, 'serv') + sumTaxes(evho, 'iva') + sumTaxes(evho, 'sc'),
                                    evho.currency.sigla) }}</b>
                            </td>
                            <template v-if="showDetails">
                                <td colspan="11"></td>
                            </template>
                            <td class="align-middle"></td>
                        </tr>

                        <tr>
                            <td class="align-middle text-dark text-left bg-white sticky-col" colspan="3">
                                OBSERVAÇÃO CLIENTE:
                            </td>
                            <td class="align-middle text-dark text-left" colspan="9">
                                <b>{{ evho.customer_observation }}</b>
                            </td>

                            <td class="align-middle" colspan="2">
                                Custo
                            </td>
                            <td class="align-middle" colspan="2">
                                <b>{{ formatCurrency(((sumCost(evho) * evho.iss_percent) / 100) +
                                    ((sumCost(evho) * evho.service_percent) / 100) + ((sumCost(evho) *
                                        evho.iva_percent) / 100) + sumCost(evho), evho.currency.sigla) }}</b>
                            </td>

                            <template v-if="showDetails">
                                <td colspan="11"></td>
                            </template>
                            <td></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
