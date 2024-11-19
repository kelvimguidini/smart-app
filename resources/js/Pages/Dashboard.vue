<script setup>

import Chart from 'chart.js/auto';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const pendingValidate = ref({
    loading: true,
    data: 0,
    error: false
});

const linksApproved = ref({
    loading: true,
    data: 0,
    error: false
});

const eventStatus = ref({
    loading: true,
    data: [],
    error: false
});

const waitApproval = ref({
    loading: true,
    data: {
        "hotels": 0,
        "transports": 0
    },
    error: false
});

const events = ref({
    loading: true,
    data: [],
    error: false
});

const groups = ref({
    loading: true,
    data: [],
    error: false
});
// Outros dados do dashboard

const dashboardData = ref({
    pendingValidate: null,
    eventStatus: null,
    waitApproval: null,
    linksApproved: null,
    byMonths: null,
    userGroups: null,
    loading: true,
    error: false,
});

const fetchDashboardData = async () => {
    try {
        const response = await axios.get('/dashboard-data'); // Chamada única
        const data = response.data;

        try {
            renderWaitApproval(data.waitApproval);
        } catch (error) {
            waitApproval.value.error = true;
            console.error("Erro ao processar waitApproval:", error);
        }

        try {
            renderEventStatus(data.eventStatus);
        } catch (error) {
            eventStatus.value.error = true;
            console.error("Erro ao processar eventStatus:", error);
        }

        try {
            renderPendingValidate(data.pendingValidate);
        } catch (error) {
            pendingValidate.value.error = true;
            console.error("Erro ao processar pendingValidate:", error);
        }

        try {
            renderLinksApproved(data.linksApproved);
        } catch (error) {
            linksApproved.value.error = true;
            console.error("Erro ao processar linksApproved:", error);
        }

        try {
            renderEventMonth(data.byMonths);
        } catch (error) {
            events.value.error = true;
            console.error("Erro ao processar eventMonth:", error);
        }

        try {
            renderUserGroups(data.userGroups);
        } catch (error) {
            groups.value.error = true;
            console.error("Erro ao processar userGroups:", error);
        }
  
    } catch (error) {
        console.error(error);
        waitApproval.value.loading = false;
        eventStatus.value.loading = false;
        pendingValidate.value.loading = false;
        linksApproved.value.loading = false;
        events.value.loading = false;
        groups.value.loading = false;

        waitApproval.value.error = true;
        eventStatus.value.error = true;
        pendingValidate.value.error = true;
        linksApproved.value.error = true;
        events.value.error = true;
        groups.value.error = true;
    }
};


onMounted(() => {
    fetchDashboardData();
});

const pieHotelStatus = (hotel) => {
    // Set new default font family and font color to mimic Bootstrap's default styling
    // Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    // Chart.defaults.global.defaultFontColor = '#858796';

    const chartData = Object.keys(hotel).map((key) => ({
        label: key,
        value: hotel[key],
        backgroundColor: generateColor(),
    }));

    eventStatus.value.data.hotel = chartData;

    // Pie Chart Example
    var ctx = document.getElementById("pie-hotel-status");

    var myPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            // labels: chartData.map((item) => item.label),
            datasets: [{
                data: chartData.map((item) => item.value),
                backgroundColor: chartData.map((item) => item.backgroundColor),
                hoverBackgroundColor: chartData.map((item) => item.backgroundColor),
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: {
                display: false
            },
            cutoutPercentage: 80,
        },
    });
}


const renderPendingValidate = (response) => {
    pendingValidate.value.data = response;
    pendingValidate.value.loading = false;
};

const renderLinksApproved = (response) => {
    linksApproved.value.data = response;
    linksApproved.value.loading = false;
};

const renderEventStatus = (response) => {

    pieHotelStatus(response.original);
    eventStatus.value.loading = false;

};

const renderEventMonth = (response) => {
    
            events.value.data = response.original;
            // Area Chart Example
            var ctx = document.getElementById("area-event");
            var myLineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: response.original.map(function (item) {
                        return item.month;
                    }),
                    datasets: [
                        {
                            label: "Data Do Evento",
                            lineTension: 0.3,
                            backgroundColor: "rgba(78, 115, 223, 0.05)",
                            borderColor: "rgba(78, 115, 223, 1)",
                            pointRadius: 3,
                            pointBackgroundColor: "rgba(78, 115, 223, 1)",
                            pointBorderColor: "rgba(78, 115, 223, 1)",
                            pointHoverRadius: 3,
                            pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                            pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                            pointHitRadius: 10,
                            pointBorderWidth: 2,
                            data: response.original.map(function (item) {
                                return item.event_count;
                            }),
                        },
                        {
                            label: "Data do Registro",
                            lineTension: 0.3,
                            backgroundColor: "rgba(115, 223, 78, 0.05)",
                            borderColor: "rgba(115, 223, 78, 1)",
                            pointRadius: 3,
                            pointBackgroundColor: "rgba(115, 223, 78, 1)",
                            pointBorderColor: "rgba(115, 223, 78, 1)",
                            pointHoverRadius: 3,
                            pointHoverBackgroundColor: "rgba(115, 223, 78, 1)",
                            pointHoverBorderColor: "rgba(115, 223, 78, 1)",
                            pointHitRadius: 10,
                            pointBorderWidth: 2,
                            data: response.original.map(function (item) {
                                return item.register_count;
                            }),
                        }
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            time: {
                                unit: 'date'
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 7
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                                // Include a dollar sign in the ticks
                                callback: function (value, index, values) {
                                    return number_format(value);
                                }
                            },
                            gridLines: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }],
                    },
                    legend: {
                        display: true
                    },
                    tooltipse4re4: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
                        titleFontSize: 14,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                        callbacks: {
                            label: function (tooltipItem, chart) {
                                var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                                return datasetLabel + ': $' + number_format(tooltipItem.yLabel);
                            }
                        }
                    }
                }
            });

            events.value.loading = false;
};

const renderWaitApproval = (response) => {
    waitApproval.value.data = response.original;
    waitApproval.value.loading = false;
};

const renderUserGroups = (response) => {
    groups.value.data = response.original;
    groups.value.loading = false;
};

const generateColor = () => {

    // Gerar um valor aleatório entre 0 e 255 para cada componente RGB
    const red = Math.floor(Math.random() * 256);
    const green = Math.floor(Math.random() * 256);
    const blue = Math.floor(Math.random() * 256);

    // Formatar a cor no formato hexadecimal
    return "#" + componentToHex(red) + componentToHex(green) + componentToHex(blue);
}
const componentToHex = (component) => {
    const hex = component.toString(16);
    return hex.length === 1 ? "0" + hex : hex;
}

const number_format = (number, decimals, dec_point, thousands_sep) => {

    number = (number + '').replace(',', '').replace(' ', '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            </div>
        </template>

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Content Row -->
            <div class="row">

                <div class="col-xl-3 col-md-6 mb-4">
                    <div
                        :class="['card', { 'border-left-primary': !waitApproval.loading && !waitApproval.error, 'border-left-danger': waitApproval.error }, 'shadow', 'h-100', 'py-2']">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1"
                                        :class="{ 'text-primary': !waitApproval.loading && !waitApproval.error, 'text-danger': waitApproval.error, 'text-muted': waitApproval.loading }">
                                        Fechado com o cliente
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"
                                        :class="{ 'text-gray-800': !waitApproval.loading && !waitApproval.error, 'text-muted': waitApproval.loading }">
                                        {{ waitApproval.loading ? '-' : (waitApproval.error ? "Erro" :
                                            waitApproval.data.hotels) }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-2x text-gray-300"
                                        :class="{ 'fa-bed': !waitApproval.loading && !waitApproval.error, 'fa-exclamation-circle': waitApproval.error, 'fa-spinner fa-spin': waitApproval.loading }"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div
                        :class="['card', { 'border-left-success': !waitApproval.loading && !waitApproval.error, 'border-left-danger': waitApproval.error }, 'shadow', 'h-100', 'py-2']">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1"
                                        :class="{ 'text-success': !waitApproval.loading && !waitApproval.error, 'text-danger': waitApproval.error, 'text-muted': waitApproval.loading }">
                                        Cancelados
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"
                                        :class="{ 'text-gray-800': !waitApproval.loading && !waitApproval.error, 'text-muted': waitApproval.loading }">
                                        {{ waitApproval.loading ? '-' : (waitApproval.error ? "Erro" :
                                            waitApproval.data.transports) }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-2x text-gray-300"
                                        :class="{ 'fa-car-alt': !waitApproval.loading && !waitApproval.error, 'fa-exclamation-circle': waitApproval.error, 'fa-spinner fa-spin': waitApproval.loading }"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Earnings (Monthly) Card Example -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div
                        :class="['card', { 'border-left-info': !linksApproved.loading && !linksApproved.error, 'border-left-danger': linksApproved.error }, 'shadow', 'h-100', 'py-2']">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1"
                                        :class="{ 'text-info': !linksApproved.loading && !linksApproved.error, 'text-danger': linksApproved.error, 'text-muted': linksApproved.loading }">
                                        Aprovação De Orçamento
                                    </div>

                                    <div class="row no-gutters align-items-center">
                                        <div class="col-auto">
                                            <div class="h5 mb-0 font-weight-bold"
                                                :class="{ 'text-gray-800': !linksApproved.loading && !linksApproved.error, 'text-muted': linksApproved.loading }">
                                                {{ linksApproved.loading ? '-' : (linksApproved.error ? "Erro" :
                                                    linksApproved.data + "%") }}
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="progress progress-sm mr-2">
                                                <div class="progress-bar bg-info" role="progressbar"
                                                    :style="{ width: linksApproved.data + '%' }" aria-valuenow="50"
                                                    aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-2x text-gray-300"
                                        :class="{ 'fa-clipboard-list': !linksApproved.loading && !linksApproved.error, 'fa-exclamation-circle': linksApproved.error, 'fa-spinner fa-spin': linksApproved.loading }"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div
                        :class="['card', { 'border-left-warning': !pendingValidate.loading && !pendingValidate.error, 'border-left-danger': pendingValidate.error }, 'shadow', 'h-100', 'py-2']">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1"
                                        :class="{ 'text-warning': !pendingValidate.loading && !pendingValidate.error, 'text-danger': pendingValidate.error, 'text-muted': pendingValidate.loading }">
                                        Orçamentos não avaliados
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"
                                        :class="{ 'text-gray-800': !pendingValidate.loading && !pendingValidate.error, 'text-muted': pendingValidate.loading }">
                                        {{ pendingValidate.loading ? '-' : (pendingValidate.error ? "Erro" :
                                            pendingValidate.data) }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-2x text-gray-300"
                                        :class="{ 'fa-comments': !pendingValidate.loading && !pendingValidate.error, 'fa-exclamation-circle': pendingValidate.error, 'fa-spinner fa-spin': pendingValidate.loading }"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Row -->

            <div class="row">

                <!-- Area Chart -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <!-- Card Header - Dropdown -->
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Eventos Por Mês</h6>
                        </div>
                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="area-event"></canvas>
                                <div v-if="events.loading" class="text-center">
                                    Carregando...
                                </div>
                                <div v-else-if="events.error" class="text-center text-danger">
                                    Ocorreu um erro ao carregar os dados..
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <!-- Card Header - Dropdown -->
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Status</h6>
                        </div>
                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="chart-pie pt-4 pb-2">
                                <canvas id="pie-hotel-status"></canvas>
                            </div>
                            <div v-if="eventStatus.loading" class="text-center">
                                Carregando...
                            </div>
                            <div v-else-if="eventStatus.error" class="text-center text-danger">
                                Ocorreu um erro ao carregar os dados..
                            </div>
                            <div v-else class="mt-4 text-center small">
                                <span v-for="(data, index) in eventStatus.data.hotel" class="mr-2">
                                    <i class="fas fa-circle" :style="{ color: data.backgroundColor }"></i> {{ data.label }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Row -->
            <div class="row">

                <!-- Content Column -->
                <div class="col-lg-8 mb-7">

                    <!-- Project Card Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Percentual De Usuários Por Grupo</h6>
                        </div>
                        <div class="card-body">
                            <div v-if="groups.loading" class="text-center">
                                Carregando...
                            </div>
                            <div v-else-if="groups.error" class="text-center text-danger">
                                Ocorreu um erro ao carregar os dados..
                            </div>
                            <template v-else v-for="group in groups.data">
                                <h4 class="small font-weight-bold">{{ group.Name }} <span class="float-right">{{
                                    group.CountUsers
                                }}</span>
                                </h4>
                                <div class="progress mb-4">
                                    <div class="progress-bar" role="progressbar"
                                        :style="`width: ${group.Percentage}%; background-color: ${generateColor()};`"
                                        :aria-valuenow="group.Percentage" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>

                            </template>
                        </div>
                    </div>

                </div>

            </div>

        </div>
        <!-- /.container-fluid -->

    </AuthenticatedLayout>
</template>
