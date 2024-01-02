<script setup>

import { Link, useForm } from '@inertiajs/inertia-vue3';
import { onMounted, ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

import '@/vendor/jquery/jquery.min.js';
import '@/vendor/bootstrap/js/bootstrap.bundle.min.js';


//JQUERY UI
import '@/vendor/jquery-ui/jquery-ui.js';
import '@/vendor/jquery-ui/jquery-ui.css';

// Mask
import '@/vendor/mask/jquery.mask.js';
import '@/vendor/mask/jquery.maskMoney.js';

// Custom scripts for all pages
import '@/vendor/sb-admin-2.js';


import NavBar from '@/Components/NavBar.vue';

const props = defineProps({
    tokenValid: {
        type: Boolean,
        default: false
    },
    event: {
        type: Object,
        default: {}
    },
    providerCity: {
        type: String,
        default: ''
    },
    providerName: {
        type: String,
        default: ''
    },
    eventHotel: {
        type: Object,
        default: {}
    },
    eventAb: {
        type: Object,
        default: {}
    },
    eventHall: {
        type: Object,
        default: {}
    },
    eventAdd: {
        type: Object,
        default: {}
    },
    budget: {
        type: Object,
        default: {}
    },
    flash: {
        type: Object,
        default: {
            type: "success",
            show: false,
            message: ""
        }
    },
    prove: {
        type: Boolean,
        default: false
    },
    user: {
        type: Number,
        default: 0
    },
    tokenEvaluated: {
        type: Boolean,
        default: false
    },
    token: {
        type: String,
        default: ''
    },
});

const daysBetween = (date1, date2) => {
    // Convert both dates to milliseconds
    var one = new Date(date1).getTime();
    var two = new Date(date2).getTime();
    // Calculate the difference in milliseconds
    var difference = Math.abs(one - two);
    // Convert back to days and return
    return Math.ceil(difference / (1000 * 60 * 60 * 24));
}

const daysBetween1 = (date1, date2) => {
    // Convert both dates to milliseconds
    var one = new Date(date1).getTime();
    var two = new Date(date2).getTime();
    // Calculate the difference in milliseconds
    var difference = Math.abs(one - two);
    // Convert back to days and return
    return Math.ceil(difference / (1000 * 60 * 60 * 24)) + 1;
}

const form = useForm({
    comissionsHotel: [],
    valuesHotel: [],
    commentsHotel: [],
    idsOptHotel: [],
    eventHotelId: null,
    hostingFeeHotel: 0,
    ISSFeeHotel: 0,
    IVAFeeHotel: 0,
    commentHotel: '',
    itemIdsHotel: [],

    comissionsAb: [],
    valuesAb: [],
    commentsAb: [],
    idsOptAb: [],
    eventAbId: null,
    hostingFeeAb: 0,
    ISSFeeAb: 0,
    IVAFeeAb: 0,
    commentAb: '',
    itemIdsAb: [],

    comissionsAdd: [],
    valuesAdd: [],
    commentsAdd: [],
    idsOptAdd: [],
    eventAddId: null,
    hostingFeeAdd: 0,
    ISSFeeAdd: 0,
    IVAFeeAdd: 0,
    commentAdd: '',
    itemIdsAdd: [],

    comissionsHall: [],
    valuesHall: [],
    commentsHall: [],
    idsOptHall: [],
    eventHallId: null,
    hostingFeeHall: 0,
    ISSFeeHall: 0,
    IVAFeeHall: 0,
    commentHall: '',
    itemIdsHall: [],

    id: 0,
    eventId: props.event.id
});

const formProve = useForm({
    id: 0,
    decision: 0,
    user: 0,
    token: ''
});

const edit = () => {

    props.budget.provider_budget_items && props.budget.provider_budget_items.filter(element => element.event_hotel_opt_id > 0).forEach((element, index) => {
        form.comissionsHotel[index] = element.comission;
        form.valuesHotel[index] = element.value;
        form.commentsHotel[index] = element.comment;
        form.idsOptHotel[index] = element.event_hotel_opt_id;
        form.itemIdsHotel[index] = element.id;

        $("#valuesHotel_" + index).maskMoney('mask', element.value);
    });

    props.budget.provider_budget_items && props.budget.provider_budget_items.filter(element => element.event_ab_opt_id > 0).forEach((element, index) => {
        form.comissionsAb[index] = element.comission;
        form.valuesAb[index] = element.value;
        form.commentsAb[index] = element.comment;
        form.idsOptAb[index] = element.event_ab_opt_id;
        form.itemIdsAb[index] = element.id;

        $("#valuesAb_" + index).maskMoney('mask', element.value);
    });

    props.budget.provider_budget_items && props.budget.provider_budget_items.filter(element => element.event_add_opt_id > 0).forEach((element, index) => {
        form.comissionsAdd[index] = element.comission;
        form.valuesAdd[index] = element.value;
        form.commentsAdd[index] = element.comment;
        form.idsOptAdd[index] = element.event_add_opt_id;
        form.itemIdsAdd[index] = element.id;

        $("#valuesAdd_" + index).maskMoney('mask', element.value);
    });

    props.budget.provider_budget_items && props.budget.provider_budget_items.filter(element => element.event_hall_opt_id > 0).forEach((element, index) => {

        form.comissionsHall[index] = element.comission;
        form.valuesHall[index] = element.value;
        form.commentsHall[index] = element.comment;
        form.idsOptHall[index] = element.event_hall_opt_id;
        form.itemIdsHall[index] = element.id;

        $("#valuesHall_" + index).maskMoney('mask', element.value);
    });

    form.id = props.budget.id;

    form.eventHotelId = props.budget.event_hotel_id;
    form.hostingFeeHotel = props.budget.hosting_fee_hotel;
    form.ISSFeeHotel = props.budget.iss_fee_hotel;
    form.IVAFeeHotel = props.budget.iva_fee_hotel;
    form.commentHotel = props.budget.comment_hotel;

    form.eventAbId = props.budget.event_ab_id;
    form.hostingFeeAb = props.budget.hosting_fee_ab;
    form.ISSFeeAb = props.budget.iss_fee_ab;
    form.IVAFeeAb = props.budget.iva_fee_ab;
    form.commentAb = props.budget.comment_ab;

    form.eventAddId = props.budget.event_add_id;
    form.hostingFeeAdd = props.budget.hosting_fee_add;
    form.ISSFeeAdd = props.budget.iss_fee_add;
    form.IVAFeeAdd = props.budget.iva_fee_add;
    form.commentAdd = props.budget.comment_add;

    form.eventHallId = props.budget.event_hall_id;
    form.hostingFeeHall = props.budget.hosting_fee_hall;
    form.ISSFeeHall = props.budget.iss_fee_hall;
    form.IVAFeeHall = props.budget.iva_fee_hall;
    form.commentHall = props.budget.comment_hall;
};

onMounted(() => {
    let symbol = 'R$ ';

    if (props.eventHotel) {
        symbol = props.eventHotel.currency.symbol + ' ';
        form.eventHotelId = props.eventHotel.id;
    }
    if (props.eventAb) {
        symbol = props.eventAb.currency.symbol + ' ';
        form.eventAbId = props.eventAb.id;
    }
    if (props.eventHall) {
        symbol = props.eventHall.currency.symbol + ' ';
        form.eventHallId = props.eventHall.id;
    }
    if (props.eventAdd) {
        symbol = props.eventAdd.currency.symbol + ' ';
        form.eventAddId = props.eventAdd.id;
    }

    props.eventHotel != null && props.eventHotel.event_hotels_opt != null && props.eventHotel.event_hotels_opt.map((element, index) => {
        form.idsOptHotel[index] = element.id;
        $("#valuesHotel_" + index).maskMoney({ prefix: symbol, allowNegative: false, thousands: '.', decimal: ',', affixesStay: true })
            .on('change', (e) => {
                form.valuesHotel[index] = $(e.target).val();
            });
        form.valuesHotel[index] = 0;
    });

    props.eventAdd != null && props.eventAdd.event_add_opts != null && props.eventAdd.event_add_opts.map((element, index) => {
        form.idsOptAdd[index] = element.id;
        $("#valuesAdd_" + index).maskMoney({ prefix: symbol, allowNegative: false, thousands: '.', decimal: ',', affixesStay: true })
            .on('change', (e) => {
                form.eventAdd[index] = $(e.target).val();
            });
        form.valuesAdd[index] = 0;
    });

    props.eventAb != null && props.eventAb.event_ab_opts != null && props.eventAb.event_ab_opts.map((element, index) => {
        form.idsOptAb[index] = element.id;
        $("#valuesAb_" + index).maskMoney({ prefix: symbol, allowNegative: false, thousands: '.', decimal: ',', affixesStay: true })
            .on('change', (e) => {
                form.eventAb[index] = $(e.target).val();
            });
        form.valuesAb[index] = 0;
    });

    props.eventHall != null && props.eventHall.event_hall_opts != null && props.eventHall.event_hall_opts.map((element, index) => {
        form.idsOptHall[index] = element.id;
        $("#valuesHall_" + index).maskMoney({ prefix: symbol, allowNegative: false, thousands: '.', decimal: ',', affixesStay: true })
            .on('change', (e) => {
                form.eventHall[index] = $(e.target).val();
            });
        form.valuesHall[index] = 0;
    });


    if (props.budget) {
        edit();
    }
});

const submitProve = (decision) => {
    formProve.id = props.budget.id;
    formProve.user = props.user;
    formProve.decision = decision;

    formProve.token = props.token;

    formProve.post(route('budget-prove'), {
        onSuccess: () => {
            formProve.reset();
        }
    });
};

const submit = () => {
    form.valuesHotel && form.valuesHotel.map((element, index) => {
        return element = $('#valuesHotel_' + index).maskMoney('unmasked')[0]
    });

    form.valuesAdd && form.valuesAdd.map((element, index) => {
        return element = $('#valuesAdd_' + index).maskMoney('unmasked')[0]
    });

    form.valuesAb && form.valuesAb.map((element, index) => {
        return element = $('#valuesAb_' + index).maskMoney('unmasked')[0]
    });

    form.valuesHall && form.valuesHall.map((element, index) => {
        return element = $('#valuesHall_' + index).maskMoney('unmasked')[0]
    });

    form.post(route('budget-save'), {
        onSuccess: () => {
        }
    });
};

</script>

<style>
@tailwind base;
@tailwind component;
@tailwind utilities;

#footer .flex-grow {
    flex-grow: 1;
}

#footer div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
    width: 100%;
}
</style>

<template>
    <div>
        <div v-if="flash != null && flash.show" class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen">
                <div class="modal-overlay fixed inset-0 bg-gray-500 opacity-50"></div>
                <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto"
                    :class="{
                        'bg-green-100 text-green-900': flash.type === 'success',
                        'bg-red-100 text-red-900': flash.type === 'error',
                        'bg-yellow-100 text-yellow-900': flash.type === 'warning'
                    }">
                    <div class="modal-content py-4 text-left px-6">
                        <div class="modal-header flex justify-between items-center pb-3">
                            <p class="text-lg font-semibold">Aprovação de Orçamento</p>
                            <button class="modal-close cursor-pointer z-50" @click="flash.show = false">
                                <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18"
                                    height="18" viewBox="0 0 18 18">
                                    <path
                                        d="M18 1.5l-1.5-1.5-7.5 7.5-7.5-7.5-1.5 1.5 7.5 7.5-7.5 7.5 1.5 1.5 7.5-7.5 7.5 7.5 1.5-1.5-7.5-7.5z" />
                                </svg>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-4"
                                style="font-size: 16px; line-height: 1.4em; color: #333; font-family: Arial, sans-serif;">
                                {{ flash.message }}
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <NavBar></NavBar>
    <div v-if="!tokenValid" class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <Link href="/">
            <ApplicationLogo class="w-20 h-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="bg-red-100 text-red-800 border-red-200 px-4 py-3 rounded mb-4">
                <p>A URL inserida não é válida. Por favor, verifique se a URL está correta e tente novamente.</p>
            </div>
        </div>
    </div>

    <div v-if="tokenValid" class="min-h-screen flex flex-col items-center pt-10 bg-gray-100">

        <form @submit.prevent="submit">
            <div class="w-92 " style="margin-bottom: 30px;">
                <div style="margin-bottom: 18px;" class="relative overflow-x-auto shadow-md sm:rounded-lg mb-18 "
                    v-if="eventHotel != null && eventHotel.event_hotels_opt != null && eventHotel.event_hotels_opt.length > 0">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th colspan="11" scope="col" class="px-6 py-3 text-2xl font-bold mb-4">
                                    Hospedagem
                                </th>
                            </tr>
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Regime
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Propósito
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Categoria
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Apartamento
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Entrada
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Saída
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Quantidade
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Noites
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Comissão %
                                </th>

                                <th scope="col" class="px-6 py-3">
                                    Valor
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Observação
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700"
                                v-for="(item, index) in eventHotel.event_hotels_opt" :key="index">
                                <th scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ item.regime.name }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ item.purpose.name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.category_hotel.name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.apto_hotel.name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ new Date(item.in).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ new Date(item.out).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.count }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ daysBetween(item.in, item.out) }}
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" v-model="form.comissionsHotel[index]" :disabled="prove"
                                        class="border rounded-lg px-2 py-1 w-full">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" :id="'valuesHotel_' + index" :disabled="prove"
                                        class="border rounded-lg px-2 py-1 w-full money-">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" v-model="form.commentsHotel[index]" :disabled="prove"
                                        class="border rounded-lg px-2 py-1 w-full">
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa de Hospedagem (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" :disabled="prove" v-model="form.hostingFeeHotel"
                                        class="border rounded-lg px-2 py-1">
                                </td>
                                <td class="px-6 py-4" colspan="5" rowspan="4">
                                    <label class="font-bold">Observação:</label>
                                    <textarea v-model="form.commentHotel" :disabled="prove"
                                        class="border rounded-lg px-2 py-1 w-full"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa de ISS (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" :disabled="prove" v-model="form.ISSFeeHotel"
                                        class="border rounded-lg px-2 py-1">
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa IVA (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" :disabled="prove" v-model="form.IVAFeeHotel"
                                        class="border rounded-lg px-2 py-1">
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div style="margin-bottom: 18px;" class="relative overflow-x-auto shadow-md sm:rounded-lg mb-18"
                    v-if="eventAb != null && eventAb.event_ab_opts != null && eventAb.event_ab_opts.length > 0">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th colspan="10" scope="col" class="px-6 py-3 text-2xl font-bold mb-4">
                                    Alimentos & Bebidas
                                </th>
                            </tr>
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Serviço
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Tipo Serviço
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Local
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    De
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Ate
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Quantidade
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Noites
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Comissão %
                                </th>

                                <th scope="col" class="px-6 py-3">
                                    Valor
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Observação
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700"
                                v-for="(item, index) in eventAb.event_ab_opts" :key="index">
                                <th scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ item.service.name }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ item.service_type.name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.local.name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ new Date(item.in).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ new Date(item.out).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.count }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ daysBetween1(item.in, item.out) }}
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" v-model="form.comissionsAb[index]" :disabled="prove"
                                        class="border rounded-lg px-2 py-1 w-full">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" :id="'valuesAb_' + index" :disabled="prove"
                                        class="border rounded-lg px-2 py-1 w-full money-">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" v-model="form.commentsAb[index]" :disabled="prove"
                                        class="border rounded-lg px-2 py-1 w-full">
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa de Hospedagem (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" :disabled="prove" v-model="form.hostingFeeAb"
                                        class="border rounded-lg px-2 py-1">
                                </td>
                                <td class="px-6 py-4" colspan="5" rowspan="4">
                                    <label class="font-bold">Observação:</label>
                                    <textarea v-model="form.commentAb" :disabled="prove"
                                        class="border rounded-lg px-2 py-1 w-full"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa de ISS (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" :disabled="prove" v-model="form.ISSFeeAb"
                                        class="border rounded-lg px-2 py-1">
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa IVA (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" :disabled="prove" v-model="form.IVAFeeAb"
                                        class="border rounded-lg px-2 py-1">
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div style="margin-bottom: 18px;" class="relative overflow-x-auto shadow-md sm:rounded-lg mb-18"
                    v-if="eventHall != null && eventHall.event_hall_opts != null && eventHall.event_hall_opts.length > 0">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th colspan="10" scope="col" class="px-6 py-3 text-2xl font-bold mb-4">
                                    Salões
                                </th>
                            </tr>
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Nome
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Finalidade
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    M2
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    De
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Ate
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Quantidade
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Noites
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Comissão %
                                </th>

                                <th scope="col" class="px-6 py-3">
                                    Valor
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Observação
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700"
                                v-for="(item, index) in eventHall.event_hall_opts" :key="index">
                                <th scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ item.name }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ item.purpose.name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.m2 }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ new Date(item.in).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ new Date(item.out).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.count }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ daysBetween(item.in, item.out) }}
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" :disabled="prove" v-model="form.comissionsHall[index]"
                                        class="border rounded-lg px-2 py-1 w-full">
                                </td>
                                <td class="px-6 py-4">
                                    <div v-once="form.idsOptHall[index] = item.id"></div>
                                    <input type="text" :id="'valuesHall_' + index"
                                        class="border rounded-lg px-2 py-1 w-full money-">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" :disabled="prove" v-model="form.commentsHall[index]"
                                        class="border rounded-lg px-2 py-1 w-full">
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa de Salão (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" :disabled="prove" v-model="form.hostingFeeHall"
                                        class="border rounded-lg px-2 py-1">
                                </td>
                                <td class="px-6 py-4" colspan="5" rowspan="4">
                                    <label class="font-bold">Observação:</label>
                                    <textarea v-model="form.commentHall" :disabled="prove"
                                        class="border rounded-lg px-2 py-1 w-full"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa de ISS (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" :disabled="prove" v-model="form.ISSFeeHall"
                                        class="border rounded-lg px-2 py-1">
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa IVA (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" :disabled="prove" v-model="form.IVAFeeHall"
                                        class="border rounded-lg px-2 py-1">
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div style="margin-bottom: 18px;" class="relative overflow-x-auto shadow-md sm:rounded-lg mb-18"
                    v-if="eventAdd != null && eventAdd.event_add_opts != null && eventAdd.event_add_opts.length > 0">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th colspan="9" scope="col" class="px-6 py-3 text-2xl font-bold mb-4">
                                    ADICIONAIS
                                </th>
                            </tr>
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Serviço
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Medida
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Frequência
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    De
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Ate
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Quantidade
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Noites
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Comissão %
                                </th>

                                <th scope="col" class="px-6 py-3">
                                    Valor
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Observação
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700"
                                v-for="(item, index) in eventAdd.event_add_opts" :key="index">
                                <th scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ item.service.name }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ item.measure.name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.frequency.name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ new Date(item.in).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ new Date(item.out).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.count }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ daysBetween(item.in, item.out) }}
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" :disabled="prove" v-model="form.comissionsAdd[index]"
                                        class="border rounded-lg px-2 py-1 w-full">
                                </td>
                                <td class="px-6 py-4">
                                    <div v-once="form.idsOptAdd[index] = item.id"></div>
                                    <input type="text" :disabled="prove" :id="'valuesAdd_' + index"
                                        class="border rounded-lg px-2 py-1 w-full money-">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" :disabled="prove" v-model="form.commentsAdd[index]"
                                        class="border rounded-lg px-2 py-1 w-full">
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa de Adicionais (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" :disabled="prove" v-model="form.hostingFeeAdd"
                                        class="border rounded-lg px-2 py-1">
                                </td>
                                <td class="px-6 py-4" colspan="5" rowspan="4">
                                    <label class="font-bold">Observação:</label>
                                    <textarea v-model="form.commentAdd" :disabled="prove"
                                        class="border rounded-lg px-2 py-1 w-full"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa de ISS (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" v-model="form.ISSFeeAdd" class="border rounded-lg px-2 py-1">
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4" colspan="2">
                                    <label class="font-bold">Taxa IVA (%):</label>
                                </td>
                                <td class="px-6 py-4" colspan="3">
                                    <input type="number" v-model="form.IVAFeeAdd" class="border rounded-lg px-2 py-1">
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <button v-if="!prove && !tokenEvaluated"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded float-right"
                    :class="{ 'opacity-60': form.processing }" :disabled="form.processing">
                    <span v-if="form.processing" class="animate-spin mr-2 h-4 w-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh">
                            <path d="M23 4v6h-6M1 20v-6h6" />
                            <path d="M3.51 9a9 9 0 1 1 2.13 5.82L7 15.5" />
                        </svg>
                    </span>
                    Salvar
                </button>

                <div v-if="tokenEvaluated" class="bg-red-100 text-red-800 border-red-200 px-4 py-3 rounded mb-4">
                    <p>Esse ítem já foi validado pelo gestor. Por favor entre em contato direto com ele caso precise alterar
                        alguma informação.</p>
                </div>

                <button type="button" v-if="prove && user > 0 && !tokenEvaluated" v-on:click="submitProve(1)"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded float-right"
                    :class="{ 'opacity-60': formProve.processing }" :disabled="formProve.processing">
                    <span v-if="formProve.processing" class="animate-spin mr-2 h-4 w-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh">
                            <path d="M23 4v6h-6M1 20v-6h6" />
                            <path d="M3.51 9a9 9 0 1 1 2.13 5.82L7 15.5" />
                        </svg>
                    </span>
                    Aprovar
                </button>

                <button type="button" v-if="prove && user > 0 && !tokenEvaluated"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded float-right"
                    :class="{ 'opacity-60': formProve.processing }" :disabled="formProve.processing"
                    v-on:click="submitProve(2)">
                    <span v-if="formProve.processing" class="animate-spin mr-2 h-4 w-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh">
                            <path d="M23 4v6h-6M1 20v-6h6" />
                            <path d="M3.51 9a9 9 0 1 1 2.13 5.82L7 15.5" />
                        </svg>
                    </span>
                    Reprovar
                </button>

            </div>
        </form>
    </div>

    <footer id="footer" class="relative">
        <div class="flex-grow" style="background-color:#1f2937;">
            <div>
                <div style="display:inline-block;padding:10px;">
                    <p>Avenida das Americas, 3434 - Bloco 5 - Grupo 520</p>
                    <p>Barra da Tijuca - Rio de Janeiro - 22.640-102</p>
                </div>
                <div style="display:inline-block;padding:10px;text-align:right;float:right;">
                    <p>Tel.: (+55 21) 2025-7900</p>
                    <p>www.4BTS.com.br</p>
                </div>
                <div style="display: flex;padding:10px;text-align:right;justify-content: flex-end;">
                    <img style="width:4rem;height:4rem;margin-right:25px;vertical-align:middle;left:1;"
                        src="/storage/logos/logo.png" alt="4BTS">
                </div>
            </div>
        </div>
    </footer>
</template>
