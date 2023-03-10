<script setup>
import { onMounted } from 'vue';
// Bootstrap core JavaScript
import '@/vendor/jquery/jquery.min.js';
import '@/vendor/bootstrap/js/bootstrap.bundle.min.js';

// Core plugin JavaScript
import '@/vendor/jquery-easing/jquery.easing.min.js';


//SELECT2
import '@/vendor/select2/select2.min.css';
import '@/vendor/select2/select2-bootstrap.css';
import '@/vendor/select2/select2.min.js';
import '@/vendor/select2/pt-BR.min.js';

//JQUERY UI
import '@/vendor/jquery-ui/jquery-ui.js';
import '@/vendor/jquery-ui/jquery-ui.css';

// Mask
import '@/vendor/mask/jquery.mask.js';
import '@/vendor/mask/jquery.maskMoney.js';

// Custom scripts for all pages
import '@/vendor/sb-admin-2.js';

import '../../css/app.css';
import '../vendor/fontawesome-free/css/all.min.css';

import '@/vendor/datatables/jquery.dataTables.min.js';
import '@/vendor/datatables/dataTables.bootstrap4.min.js';

import '../vendor/datatables/dataTables.bootstrap4.min.css';


import Menu from '@/Components/Menu.vue';
import NavBar from '@/Components/NavBar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import { Link } from '@inertiajs/inertia-vue3';


const menuItem = [
    {
        link: route().current('dashboard') || route().current('/') ? '' : route('dashboard'),
        name: 'Inicio',
        icon: 'fas fa-fw fa-home',
        active: route().current('dashboard') || route().current('/'),
        isItem: true,
    },
    {
        name: 'Usuários',
        icon: 'fas fw fa-users',
        isItem: false,
        collapseHeader: 'Administrar acessos',
        subMenu: [
            {
                link: route().current('role') ? '' : route('role'),
                name: 'Grupo de Acesso',
                active: route().current('role'),
                role: 'role_admin'
            },
            {
                link: route().current('register') ? '' : route('register'),
                name: 'Usuário',
                active: route().current('register'),
                role: 'user_admin'
            }
        ],
    },
    {
        name: 'Cadastro',
        icon: 'fa fa-table',
        isItem: false,
        collapseHeader: 'Tabelas auxiliares',
        subMenu: [
            {
                link: route().current('currency') ? '' : route('currency'),
                name: 'Moeda',
                active: route().current('currency'),
                role: ['currency_admin']
            },
            {
                link: route().current('customer') ? '' : route('customer'),
                name: 'Clientes',
                active: route().current('customer'),
                role: 'customer_admin'
            },
            {
                link: route().current('crd') ? '' : route('crd'),
                name: 'CRD\'s',
                active: route().current('crd'),
                role: 'crd_admin'
            },
        ],
        divider: true
    },
    {
        name: 'Cadastro p/ Fornecedor',
        icon: 'fa fa-hotel',
        isItem: false,
        collapseHeader: 'Auxiliares de Fornecedor',
        subMenu: [
            {
                link: route().current('broker') ? '' : route('broker'),
                name: 'Broker',
                active: route().current('broker'),
                role: ['broker_admin']
            }
        ],
    },
    {
        name: 'Cadastro p/ Hotel',
        icon: 'fa fa-bed',
        isItem: false,
        collapseHeader: 'Auxiliares de Hotel',
        subMenu: [
            {
                link: route().current('apto') ? '' : route('apto'),
                name: 'Apartamento',
                active: route().current('apto'),
                role: ['apto_admin']
            },
            {
                link: route().current('category') ? '' : route('category'),
                name: 'Categoria',
                active: route().current('category'),
                role: ['category_admin']
            },
            {
                link: route().current('regime') ? '' : route('regime'),
                name: 'Regime',
                active: route().current('regime'),
                role: ['regime_admin']
            },
            {
                link: route().current('purpose') ? '' : route('purpose'),
                name: 'Propósito',
                active: route().current('purpose'),
                role: ['purpose_admin']
            },
        ],
    },
    {
        name: 'Cadastros p/ A&B',
        icon: 'fa fa-utensils',
        isItem: false,
        collapseHeader: 'Auxiliares de A&B',
        subMenu: [
            {
                link: route().current('local') ? '' : route('local'),
                name: 'Local',
                active: route().current('local'),
                role: ['local_admin']
            },
            {
                link: route().current('service') ? '' : route('service'),
                name: 'Serviço',
                active: route().current('service'),
                role: ['service_admin']
            },
            {
                link: route().current('service-type') ? '' : route('service-type'),
                name: 'Tipo de Serviço',
                active: route().current('service-type'),
                role: ['service_type_admin']
            },
        ],
    },
    {
        name: 'Cadastros p/ Salão',
        icon: 'fa fa-warehouse',
        isItem: false,
        collapseHeader: 'Auxiliares de Salão',
        subMenu: [
            {
                link: route().current('service-hall') ? '' : route('service-hall'),
                name: 'Serviço',
                active: route().current('service-hall'),
                role: ['service_hall_admin']
            },
            {
                link: route().current('purpose-hall') ? '' : route('purpose-hall'),
                name: 'Propósito',
                active: route().current('purpose-hall'),
                role: ['purpose_hall_admin']
            },
        ]
    },
    {
        link: route().current('hotel') ? '' : route('hotel'),
        name: 'Fornecedor',
        icon: 'fa fa-building',
        active: route().current('hotel'),
        isItem: true,
        role: ['event_admin', 'hotel_operator'],
        divider: true
    },
    {
        name: 'Eventos',
        icon: 'fa fa-luggage-cart',
        isItem: false,
        collapseHeader: 'Cotações',
        subMenu: [
            {
                link: route().current('event-create') ? '' : route('event-create'),
                name: 'Cadastro Inicial',
                active: route().current('event-create'),
                role: 'event_admin'
            },
            {
                link: route().current('event-list') ? '' : route('event-list'),
                name: 'Listar',
                active: route().current('event-list'),
                role: ['event_admin', 'hotel_operator', 'land_operator', 'air_operator']
            }
        ],
    },
];

onMounted(() => {
    $("#sidebarToggle, #sidebarToggleTop").on('click', function (e) {
        $("body").toggleClass("sidebar-toggled");
        $(".sidebar").toggleClass("toggled");
        if ($(".sidebar").hasClass("toggled")) {
            $('.sidebar .collapse').collapse('hide');
        };
    });
});


</script>

<template>
    <!-- Page Wrapper -->
    <div id="wrapper">

        <Menu :menu-items="menuItem" :menu-title="$page.props.appName"></Menu>

        <FlashMessage v-if="$page.props.flash != null" :show="$page.props.flash != null"
            :message="$page.props.flash.message" :type="$page.props.flash.type"></FlashMessage>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <NavBar></NavBar>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <slot name="header" />
                        </h1>

                    </div>

                    <!-- Page Heading -->
                    <header class=" shadow" v-if="$slots.header">
                    </header>
                    <!-- Page Content -->
                    <main>
                        <slot />
                    </main>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; CODE WEY Desenvolvimento</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>


    <!-- Scroll to Top Button-->
    <Link class="scroll-to-top rounded" href="#page-top">
    <i class="fa fa-angle-up"></i>

    </Link>
</template>
