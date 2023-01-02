<script setup>
import { onMounted } from 'vue';
// Bootstrap core JavaScript
import '@/vendor/jquery/jquery.min.js';
import '@/vendor/bootstrap/js/bootstrap.bundle.min.js';

// Core plugin JavaScript
import '@/vendor/jquery-easing/jquery.easing.min.js';

// Mask
import '@/vendor/mask/jquery.mask.js';

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
            }
        ],
    },
    {
        name: 'Eventos',
        icon: 'fa fa-table',
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
                role: ['event_admin', 'event_operator']
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
