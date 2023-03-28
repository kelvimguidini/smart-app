<script setup>

import { onMounted, ref } from 'vue';


const props = defineProps({
    event: {
        type: Object,
        default: {}
    },
    provider: {
        type: Object,
        default: {}
    },
    primaryColor: {
        type: String,
        default: '#000000'
    },
    secondaryColor: {
        type: String,
        default: '#FFFFFF'
    },
    accentColor: {
        type: String,
        default: '#FF0000'
    }
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

const formatCurrency = (value) => {
    value = Math.round(value * 100) / 100;
    let sigla = 'BRL';
    if (props.eventHall != null) {
        sigla = props.eventHall.currency.sigla;
    }
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: sigla,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
}

const unitSale = (opt) => {
    var unitCost = opt.received_proposal - ((opt.received_proposal * opt.kickback) / 100);
    return Math.ceil(unitCost / opt.received_proposal_percent)
}

const sumTaxesProvider = (eventP, opt) => {
    return ((unitSale(opt) * eventP.iss_percent) / 100) + ((unitSale(opt) * eventP.service_percent) / 100) + ((unitSale(opt) * eventP.iva_percent) / 100);
}


const sumTotal = (rate, taxes, qtdDayle) => {
    return ((rate + taxes) * qtdDayle);
}

//HOTEL
const sumTotalHotelValue = ref(0);
const sumQtdDayles = ref(0);
const sumValueRate = ref(0);


//AB
const sumTotalABValue = ref(0);
const sumABQtdDayles = ref(0);
const sumABValueRate = ref(0);

//SALÃO
const sumTotalHallValue = ref(0);
const sumHallQtdDayles = ref(0);
const sumHallValueRate = ref(0);

//Adicional
const sumTotalAddValue = ref(0);
const sumAddQtdDayles = ref(0);
const sumAddValueRate = ref(0);

const hexToRgb = (hex, a) => {
    // remover o sinal de jogo da frente, se existir
    hex = hex.replace('#', '');

    // converter o valor hexadecimal em valores RGB
    var r = parseInt(hex.substring(0, 2), 16);
    var g = parseInt(hex.substring(2, 4), 16);
    var b = parseInt(hex.substring(4, 6), 16);

    // retornar a cor em formato RGB
    return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + a + ')';
}
const elementToConvert = ref(null);


const salvarPDF = () => {
    window.print();

}

const hotelEvent = ref(null);
const abEvent = ref(null);
const hallEvent = ref(null);
const addEvent = ref(null);

onMounted(() => {
    hotelEvent.value = props.event.event_hotels.find((s) => s.hotel_id == props.provider.id);
    abEvent.value = props.event.event_abs.find((s) => s.ab_id == props.provider.id);
    hallEvent.value = props.event.event_halls.find((s) => s.hall_id == props.provider.id);
    addEvent.value = props.event.event_adds.find((s) => s.add_id == props.provider.id);

    hotelEvent.value.event_hotels_opt.forEach(item => {
        var rate = parseFloat(item.received_proposal);
        var taxes = parseFloat(sumTaxesProvider(hotelEvent.value, item));
        var qtdDayle = item.count * daysBetween(item.in, item.out)

        sumValueRate.value += rate;
        sumQtdDayles.value += qtdDayle;
        sumTotalHotelValue.value += ((rate + taxes) * qtdDayle);
    });

    abEvent.value.event_ab_opts.forEach(item => {
        var rate = parseFloat(item.received_proposal);
        var taxes = parseFloat(sumTaxesProvider(abEvent.value, item));
        var qtdDayle = item.count * daysBetween(item.in, item.out)

        sumABValueRate.value += rate;
        sumABQtdDayles.value += qtdDayle;
        sumTotalABValue.value += ((rate + taxes) * qtdDayle);
    });

    hallEvent.value.event_hall_opts.forEach(item => {
        var rate = parseFloat(item.received_proposal);
        var taxes = parseFloat(sumTaxesProvider(hallEvent.value, item));

        sumHallValueRate.value += rate;
        sumHallQtdDayles.value += daysBetween(item.in, item.out);
        sumTotalHallValue.value += ((rate + taxes) * daysBetween(item.in, item.out));
    });

    addEvent.value.event_add_opts.forEach(item => {
        var rate = parseFloat(item.received_proposal);
        var taxes = parseFloat(sumTaxesProvider(hallEvent.value, item));
        var qtdDayle = item.count * daysBetween(item.in, item.out)

        sumAddValueRate.value += rate;
        sumAddQtdDayles.value += qtdDayle;
        sumTotalAddValue.value += ((rate + taxes) * daysBetween(item.in, item.out));
    });
});

</script>

<style scoped>
@tailwind base;
@tailwind component;
@tailwind utilities;

/* estilos personalizados */
.header {
    background-color: #FFF;
    /* fundo cinza claro */
    border-bottom: 5px solid #e9540d;
    /* detalhe laranja */
    padding: 1rem;
}

.logo {
    max-width: 200px;
}


tr {
    page-break-inside: avoid;
}

.resumo {
    page-break-before: always;
}

@media print {

    .no-print,
    .no-print * {
        display: none !important;
    }

    @page {
        margin-top: 5cm;
    }

    body {
        -webkit-print-color-adjust: exact;
    }
}
</style>

<template>
    <button class="no-print fixed top-0 right-0 m-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        @click="salvarPDF()">Imprimir</button>

    <div ref="elementToConvert" id="pdf-content" class="mx-8 my-10 container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <header class="header">
            <div class="container mx-auto flex items-center justify-between">
                <div class="flex items-center">
                    <img class="logo" :src="event.customer.logo" :alt="event.customer.name">
                    <div class="ml-2">
                        <h1 class="font-bold text-2xl">Evento: {{ event.name }}</h1>
                        <p class="text-gray-500">Hotel: {{ provider.name }}</p>
                    </div>
                </div>
                <img class="logo w-20 h-20 fill-current text-gray-500" src="/storage/logos/logo.png">

            </div>
        </header>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-400"
                v-if="hotelEvent != null && hotelEvent.event_hotels_opt != null && hotelEvent.event_hotels_opt.length > 0">
                <thead>
                    <tr class="table-header"
                        :style="'background-image: linear-gradient(to right, ' + hexToRgb(event.customer.color, 0.7) + ', ' + hexToRgb(event.customer.color, 0.3) + ');'">
                        <th colspan="11" class="py-2 text-center">
                            Hospedagem
                        </th>
                    </tr>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 px-4 py-2">Cat. Apto</th>
                        <th class="border border-gray-400 px-4 py-2">Regime</th>
                        <th class="border border-gray-400 px-4 py-2">Tipo Apto</th>
                        <th class="border border-gray-400 px-4 py-2">IN</th>
                        <th class="border border-gray-400 px-4 py-2">Out</th>
                        <th class="border border-gray-400 px-4 py-2">Qtd</th>
                        <th class="border border-gray-400 px-4 py-2">Diárias</th>
                        <th class="border border-gray-400 px-4 py-2">Valor</th>
                        <th class="border border-gray-400 px-4 py-2">Taxas</th>
                        <th class="border border-gray-400 px-4 py-2">TTL com Taxa</th>
                        <th class="border border-gray-400 px-4 py-2">Total Geral</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in hotelEvent.event_hotels_opt" :key="index"
                        class="table-row {{ index % 2 === 0 ? 'bg-gray-100' : '' }}">
                        <td class="border border-gray-400 px-4 py-2">{{ item.category_hotel.name }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.regime.name }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.apto_hotel.name }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.in).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.out).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.count }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ daysBetween(item.in, item.out) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{
                            formatCurrency(sumTaxesProvider(hotelEvent, item)) }}
                        </td>
                        <td class="border border-gray-400 px-4 py-2">
                            {{ formatCurrency((parseFloat(item.received_proposal)) + parseFloat(
                                sumTaxesProvider(hotelEvent, item))) }}
                        </td>
                        <td class="border border-gray-400 px-4 py-2">{{
                            formatCurrency(sumTotal(parseFloat(item.received_proposal), parseFloat(
                                sumTaxesProvider(hotelEvent, item)), item.count * daysBetween(item.in, item.out))) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="4" class="border border-gray-400 px-4 py-2">Diária Média: {{
                            formatCurrency(sumValueRate /
                                hotelEvent.event_hotels_opt.length) }} Sem Taxas</td>
                        <td></td>
                        <td colspan="2" class="border border-gray-400 px-4 py-2">{{ sumQtdDayles }} Room Nights</td>
                        <td colspan="3"></td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumTotalHotelValue) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-400 px-4 py-2">Comentários</td>
                        <td colspan="10" class="border border-gray-400 px-4 py-2">{{ hotelEvent.customer_observation }}</td>
                    </tr>
                </tfoot>
            </table>

            <table class="w-full border-collapse border border-gray-400 mt-4"
                v-if="abEvent != null && abEvent.event_ab_opts != null && abEvent.event_ab_opts.length > 0">
                <thead>
                    <tr class="table-header"
                        :style="'background-image: linear-gradient(to right, ' + hexToRgb(event.customer.color, 0.7) + ', ' + hexToRgb(event.customer.color, 0.3) + ');'">
                        <th colspan="10" class="py-2 text-center">
                            Alimentos & Bebidas
                        </th>
                    </tr>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 px-4 py-2">Refeição</th>
                        <th class="border border-gray-400 px-4 py-2">Local</th>
                        <th class="border border-gray-400 px-4 py-2">De</th>
                        <th class="border border-gray-400 px-4 py-2">Até</th>
                        <th class="border border-gray-400 px-4 py-2">Qtd</th>
                        <th class="border border-gray-400 px-4 py-2">Diárias</th>
                        <th class="border border-gray-400 px-4 py-2">Valor</th>
                        <th class="border border-gray-400 px-4 py-2">Taxas</th>
                        <th class="border border-gray-400 px-4 py-2">TTL com Taxa</th>
                        <th class="border border-gray-400 px-4 py-2">Total Geral</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in abEvent.event_ab_opts" :key="index"
                        class="table-row {{ index % 2 === 0 ? 'bg-gray-100' : '' }}">
                        <td class="border border-gray-400 px-4 py-2">{{ item.service_type.name }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.local.name }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.in).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.out).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.count }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ daysBetween(item.in, item.out) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{
                            formatCurrency(sumTaxesProvider(abEvent, item)) }}
                        </td>
                        <td class="border border-gray-400 px-4 py-2">
                            {{ formatCurrency((parseFloat(item.received_proposal)) + parseFloat(
                                sumTaxesProvider(abEvent, item))) }}
                        </td>
                        <td class="border border-gray-400 px-4 py-2">{{
                            formatCurrency(sumTotal(parseFloat(item.received_proposal), parseFloat(
                                sumTaxesProvider(abEvent, item)), item.count * daysBetween(item.in, item.out))) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="3" class="border border-gray-400 px-4 py-2">Preço Medio por Refeição</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumABValueRate /
                            abEvent.event_ab_opts.length) }}</td>
                        <td></td>
                        <td colspan="2" class="border border-gray-400 px-4 py-2">{{ sumABQtdDayles }} Refeições</td>
                        <td colspan="2"></td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumTotalABValue) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-400 px-4 py-2">Comentários</td>
                        <td colspan="9" class="border border-gray-400 px-4 py-2">{{ abEvent.customer_observation }}</td>
                    </tr>
                </tfoot>
            </table>

            <table class="w-full border-collapse border border-gray-400 mt-4"
                v-if="hallEvent != null && hallEvent.event_hall_opts != null && hallEvent.event_hall_opts.length > 0">
                <thead>
                    <tr class="table-header"
                        :style="'background-image: linear-gradient(to right, ' + hexToRgb(event.customer.color, 0.7) + ', ' + hexToRgb(event.customer.color, 0.3) + ');'">
                        <th colspan="11" class="py-2 text-center">
                            Salões & Eventos
                        </th>
                    </tr>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 px-4 py-2">Nome</th>
                        <th class="border border-gray-400 px-4 py-2">Metragem</th>
                        <th class="border border-gray-400 px-4 py-2">Finalidade</th>
                        <th class="border border-gray-400 px-4 py-2">De</th>
                        <th class="border border-gray-400 px-4 py-2">Até</th>
                        <th class="border border-gray-400 px-4 py-2">Qtd</th>
                        <th class="border border-gray-400 px-4 py-2">Diárias</th>
                        <th class="border border-gray-400 px-4 py-2">Valor</th>
                        <th class="border border-gray-400 px-4 py-2">Taxas</th>
                        <th class="border border-gray-400 px-4 py-2">TTL com Taxa</th>
                        <th class="border border-gray-400 px-4 py-2">Total Geral</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in hallEvent.event_hall_opts" :key="index"
                        class="table-row {{ index % 2 === 0 ? 'bg-gray-100' : '' }}">
                        <td class="border border-gray-400 px-4 py-2">{{ item.name }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.m2 }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.purpose.name }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.in).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.out).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.count }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ daysBetween(item.in, item.out) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{
                            formatCurrency(sumTaxesProvider(hallEvent, item)) }}
                        </td>
                        <td class="border border-gray-400 px-4 py-2">
                            {{ formatCurrency((parseFloat(item.received_proposal)) + parseFloat(
                                sumTaxesProvider(hallEvent, item))) }}
                        </td>
                        <td class="border border-gray-400 px-4 py-2">{{
                            formatCurrency(sumTotal(parseFloat(item.received_proposal), parseFloat(
                                sumTaxesProvider(hallEvent, item)), daysBetween(item.in, item.out))) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="3" class="border border-gray-400 px-4 py-2">Diária Média dos Salões</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumHallValueRate /
                            hallEvent.event_hall_opts.length) }}</td>
                        <td colspan="2"></td>
                        <td colspan="2" class="border border-gray-400 px-4 py-2">{{ sumHallQtdDayles }} Diárias de Salões
                        </td>
                        <td colspan="2"></td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumTotalHallValue) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-400 px-4 py-2">Comentários</td>
                        <td colspan="10" class="border border-gray-400 px-4 py-2">{{ hallEvent.customer_observation }}</td>
                    </tr>
                </tfoot>
            </table>

            <table class="w-full border-collapse border border-gray-400 mt-4"
                v-if="addEvent != null && addEvent.event_add_opts != null && addEvent.event_add_opts.length > 0">
                <thead>
                    <tr class="table-header"
                        :style="'background-image: linear-gradient(to right, ' + hexToRgb(event.customer.color, 0.7) + ', ' + hexToRgb(event.customer.color, 0.3) + ');'">
                        <th colspan="11" class="py-2 text-center">
                            Adicionais
                        </th>
                    </tr>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 px-4 py-2">Serviço</th>
                        <th class="border border-gray-400 px-4 py-2">Frequência</th>
                        <th class="border border-gray-400 px-4 py-2">Measure</th>
                        <th class="border border-gray-400 px-4 py-2">De</th>
                        <th class="border border-gray-400 px-4 py-2">Até</th>
                        <th class="border border-gray-400 px-4 py-2">Qtd</th>
                        <th class="border border-gray-400 px-4 py-2">Diárias</th>
                        <th class="border border-gray-400 px-4 py-2">Valor</th>
                        <th class="border border-gray-400 px-4 py-2">Taxas</th>
                        <th class="border border-gray-400 px-4 py-2">TTL com Taxa</th>
                        <th class="border border-gray-400 px-4 py-2">Total Geral</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in addEvent.event_add_opts" :key="index"
                        class="table-row {{ index % 2 === 0 ? 'bg-gray-100' : '' }}">
                        <td class="border border-gray-400 px-4 py-2">{{ item.service.name }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.frequency.name }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.measure.name }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.in).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.out).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.count }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ daysBetween(item.in, item.out) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{
                            formatCurrency(sumTaxesProvider(hallEvent, item)) }}
                        </td>
                        <td class="border border-gray-400 px-4 py-2">
                            {{ formatCurrency((parseFloat(item.received_proposal)) + parseFloat(
                                sumTaxesProvider(hallEvent, item))) }}
                        </td>
                        <td class="border border-gray-400 px-4 py-2">{{
                            formatCurrency(sumTotal(parseFloat(item.received_proposal), parseFloat(
                                sumTaxesProvider(hallEvent, item)), daysBetween(item.in, item.out))) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="3" class="border border-gray-400 px-4 py-2">Valor médio</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumAddValueRate /
                            addEvent.event_add_opts.length) }}</td>
                        <td colspan="2"></td>
                        <td colspan="2" class="border border-gray-400 px-4 py-2">{{ sumAddQtdDayles }}</td>
                        <td colspan="2"></td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumTotalAddValue) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-400 px-4 py-2">Comentários</td>
                        <td colspan="10" class="border border-gray-400 px-4 py-2">{{ addEvent.customer_observation }}</td>
                    </tr>
                </tfoot>
            </table>

            <table class="w-full border-collapse border border-gray-400 mt-4">

                <thead>
                    <tr>
                        <th colspan="8" class="table-header">
                            Resumo da Poposta
                        </th>
                    </tr>
                    <tr class="bg-blue-100">
                        <th class="border border-gray-400 px-4 py-2">Serviços</th>
                        <th class="border border-gray-400 px-4 py-2" colspan="2">Totais de</th>
                        <td class="border border-gray-400 px-4 py-2">Moeda</td>
                        <th class="border border-gray-400 px-4 py-2">Preço médio</th>
                        <th class="border border-gray-400 px-4 py-2">Total do Pedido</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-if="hotelEvent != null && hotelEvent.event_hotels_opt != null && hotelEvent.event_hotels_opt.length > 0">
                        <td class="border border-gray-400 px-4 py-2">Hospedagem</td>
                        <td class="border border-gray-400 px-4 py-2">Rooms Night</td>
                        <td class="border border-gray-400 px-4 py-2">{{ sumQtdDayles }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ hotelEvent.currency.sigla }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumValueRate /
                            hotelEvent.event_hotels_opt.length) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumTotalHotelValue) }}</td>
                    </tr>

                    <tr v-if="abEvent != null && abEvent.event_ab_opts != null && abEvent.event_ab_opts.length > 0">
                        <td class="border border-gray-400 px-4 py-2">Alimentos & Bebidas</td>
                        <td class="border border-gray-400 px-4 py-2">Refeições</td>
                        <td class="border border-gray-400 px-4 py-2">{{ sumABQtdDayles }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ abEvent != null ? abEvent.currency.sigla : "" }}
                        </td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumABValueRate /
                            abEvent.event_ab_opts.length) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumTotalABValue) }}</td>
                    </tr>

                    <tr
                        v-if="hallEvent != null && hallEvent.event_hall_opts != null && hallEvent.event_hall_opts.length > 0">
                        <td class="border border-gray-400 px-4 py-2">Salões e Eventos</td>
                        <td class="border border-gray-400 px-4 py-2">Serviços e Areas</td>
                        <td class="border border-gray-400 px-4 py-2">{{ sumHallQtdDayles }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ hallEvent.currency.sigla }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumHallValueRate /
                            hallEvent.event_hall_opts.length) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumTotalHallValue) }}</td>
                    </tr>

                    <tr v-if="addEvent != null && addEvent.event_add_opts != null && addEvent.event_add_opts.length > 0">
                        <td class="border border-gray-400 px-4 py-2">Adicionais</td>
                        <td class="border border-gray-400 px-4 py-2">Serviços Adicionais</td>
                        <td class="border border-gray-400 px-4 py-2">{{ sumAddQtdDayles }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ addEvent.currency.sigla }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumAddValueRate /
                            addEvent.event_add_opts.length) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumTotalAddValue) }}</td>
                    </tr>

                </tbody>
                <tfoot class="table-footer">
                    <tr class="bg-blue-100">
                        <td class="border border-gray-400 px-4 py-2">IOF</td>
                        <td class="border border-gray-400 px-4 py-2">{{ event.iof }}</td>
                        <td class="border border-gray-400 px-4 py-2">Taxa de Serviço</td>
                        <td class="border border-gray-400 px-4 py-2">{{ event.service_charge }}</td>
                        <td></td>
                        <td class="border border-gray-400 px-4 py-2">Valor Total</td>
                    </tr>
                    <tr class="bg-gray-100">
                        <td class="border border-gray-400 px-4 py-2" colspan="2">{{ formatCurrency(((sumTotalHotelValue +
                            sumTotalABValue + sumTotalHallValue + sumTotalAddValue) * event.iof) / 100) }}</td>
                        <td class="border border-gray-400 px-4 py-2" colspan="2">{{ formatCurrency(((sumTotalHotelValue +
                            sumTotalABValue + sumTotalHallValue + sumTotalAddValue) * event.service_charge) / 100) }}</td>
                        <td></td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(sumTotalHotelValue +
                            sumTotalABValue + sumTotalHallValue + sumTotalAddValue + (((sumTotalHotelValue +
                                sumTotalABValue + sumTotalHallValue + sumTotalAddValue) * event.iof) / 100) +
                            (((sumTotalHotelValue +
                                sumTotalABValue + sumTotalHallValue + sumTotalAddValue) * event.service_charge) / 100)) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <footer id="footer" class="bottom-0 w-full">
            <div style="padding-top: 400px !important;" class=" flex justify-end items-center pt-70 border-t mt-4">
                <div class="text-sm font-medium text-gray-700">
                    <div class="flex items-center">
                        <p class="mr-2">PRAZO:</p>
                        <hr class="border-t border-black w-32 h-0 inline-block flex-grow">
                    </div>
                </div>
            </div>

            <div style="padding-top: 100px" class="flex justify-center items-center gap-8 pt-6 mt-6">
                <div class="w-1/3">
                    <hr class="border-t border-black">
                    <p class="text-center">Autorizado por</p>
                </div>
                <div class="w-1/3">
                    <hr class="border-t border-black">
                    <p class="text-center">Data da autorização</p>
                </div>
            </div>


            <div class="bg-gray-900 text-gray-300 py-4 p-4 pt-6 mt-6">
                <div class="container mx-auto flex justify-between items-center">
                    <div>
                        <p>Avenida das Americas, 3434 - Bloco 5 - Grupo 520</p>
                        <p>Barra da Tijuca - Rio de Janeiro - 22.640-102</p>
                    </div>
                    <div>
                        <p>Tel.: (+55 21) 2025-7900</p>
                        <p>www.4BTS.com.br</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
