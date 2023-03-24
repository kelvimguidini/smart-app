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
                v-if="event.event_hotels.find((s) => s.hotel_id == provider.id) != null && event.event_hotels.find((s) => s.hotel_id == provider.id).event_hotels_opt != null && event.event_hotels.find((s) => s.hotel_id == provider.id).event_hotels_opt.length > 0">
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
                    <tr v-for="(item, index) in event.event_hotels.find((s) => s.hotel_id == provider.id).event_hotels_opt"
                        :key="index" class="table-row {{ index % 2 === 0 ? 'bg-gray-100' : '' }}">
                        <th class="border border-gray-400 px-4 py-2">{{ item.category_hotel.name }}</th>
                        <th class="border border-gray-400 px-4 py-2">{{ item.regime.name }}</th>
                        <th class="border border-gray-400 px-4 py-2">{{ item.apto_hotel.name }}</th>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.in).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.out).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.count }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ daysBetween(item.in, item.out) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{
                            formatCurrency(sumTaxesProvider(event.event_hotels.find((s) =>
                                s.hotel_id == provider.id), item)) }}
                        </td>
                        <td class="border border-gray-400 px-4 py-2">
                            {{ (item.received_proposal + sumTaxesProvider(event.event_hotels.find((s) =>
                                s.hotel_id == provider.id), item)) }}
                        </td>
                        <td class="border border-gray-400 px-4 py-2">{{
                            formatCurrency(sumTaxesProvider(event.event_hotels.find((s) =>
                                s.hotel_id == provider.id), item) + unitSale(item)) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="10" class="border border-gray-400 px-4 py-2">Diária Média dos Salões</td>
                        <td class="border border-gray-400 px-4 py-2">R$ 0,00</td>
                    </tr>
                    <tr>
                        <td colspan="10" class="border border-gray-400 px-4 py-2">Diárias de Salões</td>
                        <td class="border border-gray-400 px-4 py-2" colspan="2">0</td>
                    </tr>
                </tfoot>
            </table>

            <table class="w-full border-collapse border border-gray-400 mt-4"
                v-if="event.event_halls.find((s) => s.hall_id == provider.id) != null && event.event_halls.find((s) => s.hall_id == provider.id).event_hall_opts != null && event.event_halls.find((s) => s.hall_id == provider.id).event_hall_opts.length > 0">
                <thead>
                    <tr class="table-header"
                        :style="'background-image: linear-gradient(to right, ' + hexToRgb(event.customer.color, 0.7) + ', ' + hexToRgb(event.customer.color, 0.3) + ');'">
                        <th colspan="8" class="py-2 text-center">
                            Salões
                        </th>
                    </tr>
                    <tr class="bg-gray-100">
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
                    <tr v-for="(item, index) in event.event_halls.find((s) => s.hall_id == provider.id).event_hall_opts"
                        :key="index" class="table-row {{ index % 2 === 0 ? 'bg-gray-100' : '' }}">
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.in).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.in).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.count }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ daysBetween(item.in, item.out) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(event.event_halls.find((s) =>
                            s.hall_id == provider.id)).iss_percent }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                    </tr>
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="7" class="border border-gray-400 px-4 py-2">Diária Média dos Salões</td>
                        <td class="border border-gray-400 px-4 py-2">R$ 0,00</td>
                    </tr>
                    <tr>
                        <td colspan="7" class="border border-gray-400 px-4 py-2">Diárias de Salões</td>
                        <td class="border border-gray-400 px-4 py-2" colspan="2">0</td>
                    </tr>
                </tfoot>
            </table>


            <table class="w-full border-collapse border border-gray-400 mt-4"
                v-if="event.event_halls.find((s) => s.hall_id == provider.id) != null && event.event_halls.find((s) => s.hall_id == provider.id).event_hall_opts != null && event.event_halls.find((s) => s.hall_id == provider.id).event_hall_opts.length > 0">

                <thead>
                    <tr class="table-header"
                        :style="'background-image: linear-gradient(to right, ' + hexToRgb(event.customer.color, 0.7) + ', ' + hexToRgb(event.customer.color, 0.3) + ');'">
                        <th colspan="8" class="py-2 text-center">
                            Salões
                        </th>
                    </tr>
                    <tr class="bg-gray-100">
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
                    <tr v-for="(item, index) in event.event_halls.find((s) => s.hall_id == provider.id).event_hall_opts"
                        :key="index" class="table-row {{ index % 2 === 0 ? 'bg-gray-100' : '' }}">
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.in).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.in).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.count }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ daysBetween(item.in, item.out) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(event.event_halls.find((s) =>
                            s.hall_id == provider.id)).iss_percent }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                    </tr>
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="7" class="border border-gray-400 px-4 py-2">Diária Média dos Salões</td>
                        <td class="border border-gray-400 px-4 py-2">R$ 0,00</td>
                    </tr>
                    <tr>
                        <td colspan="7" class="border border-gray-400 px-4 py-2">Diárias de Salões</td>
                        <td class="border border-gray-400 px-4 py-2" colspan="2">0</td>
                    </tr>
                </tfoot>
            </table>


            <table class="w-full border-collapse border border-gray-400 mt-4"
                v-if="event.event_halls.find((s) => s.hall_id == provider.id) != null && event.event_halls.find((s) => s.hall_id == provider.id).event_hall_opts != null && event.event_halls.find((s) => s.hall_id == provider.id).event_hall_opts.length > 0">

                <thead>
                    <tr class="table-header"
                        :style="'background-image: linear-gradient(to right, ' + hexToRgb(event.customer.color, 0.7) + ', ' + hexToRgb(event.customer.color, 0.3) + ');'">
                        <th colspan="8" class="py-2 text-center">
                            Salões
                        </th>
                    </tr>
                    <tr class="bg-gray-100">
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
                    <tr v-for="(item, index) in event.event_halls.find((s) => s.hall_id == provider.id).event_hall_opts"
                        :key="index" class="table-row {{ index % 2 === 0 ? 'bg-gray-100' : '' }}">
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.in).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ new Date(item.in).toLocaleDateString() }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ item.count }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ daysBetween(item.in, item.out) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(event.event_halls.find((s) =>
                            s.hall_id == provider.id)).iss_percent }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(unitSale(item)) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(item.received_proposal) }}</td>
                    </tr>
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="7" class="border border-gray-400 px-4 py-2">Diária Média dos Salões</td>
                        <td class="border border-gray-400 px-4 py-2">R$ 0,00</td>
                    </tr>
                    <tr>
                        <td colspan="7" class="border border-gray-400 px-4 py-2">Diárias de Salões</td>
                        <td class="border border-gray-400 px-4 py-2" colspan="2">0</td>
                    </tr>
                </tfoot>
            </table>


            <table class="w-full border-collapse border border-gray-400 mt-4"
                v-if="event.event_halls.find((s) => s.hall_id == provider.id) != null && event.event_halls.find((s) => s.hall_id == provider.id).event_hall_opts != null && event.event_halls.find((s) => s.hall_id == provider.id).event_hall_opts.length > 0">

                <thead>
                    <tr>
                        <th colspan="8">
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
                    <tr>
                        <td class="border border-gray-400 px-4 py-2">Hospedagem</td>
                        <td class="border border-gray-400 px-4 py-2">Rooms Night</td>
                        <td class="border border-gray-400 px-4 py-2">{{ 656 }}</td>
                        <td class="border border-gray-400 px-4 py-2">BRL</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(98) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(654, 46) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-400 px-4 py-2">Alimentos & Bebidas</td>
                        <td class="border border-gray-400 px-4 py-2">Refeições</td>
                        <td class="border border-gray-400 px-4 py-2">{{ 656 }}</td>
                        <td class="border border-gray-400 px-4 py-2">BRL</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(98) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(654, 46) }}</td>
                    </tr>

                    <tr>
                        <td class="border border-gray-400 px-4 py-2">Salões e Eventos</td>
                        <td class="border border-gray-400 px-4 py-2">Serviços e Areas</td>
                        <td class="border border-gray-400 px-4 py-2">{{ 656 }}</td>
                        <td class="border border-gray-400 px-4 py-2">BRL</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(98) }}</td>
                        <td class="border border-gray-400 px-4 py-2">{{ formatCurrency(654, 46) }}</td>
                    </tr>
                </tbody>
                <tfoot class="table-footer">
                    <tr class="bg-blue-100">
                        <td class="border border-gray-400 px-4 py-2">IOF</td>
                        <td class="border border-gray-400 px-4 py-2">7%</td>
                        <td class="border border-gray-400 px-4 py-2">Taxa de Serviço</td>
                        <td class="border border-gray-400 px-4 py-2">7%</td>
                        <td class="border border-gray-400 px-4 py-2">Cambio</td>
                        <td class="border border-gray-400 px-4 py-2">Valor Total</td>
                    </tr>
                    <tr class="bg-gray-100">
                        <td class="border border-gray-400 px-4 py-2" colspan="2">IOF</td>
                        <td class="border border-gray-400 px-4 py-2" colspan="2">Taxa de Serviço</td>
                        <td class="border border-gray-400 px-4 py-2">Cambio</td>
                        <td class="border border-gray-400 px-4 py-2">Valor Total</td>
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
