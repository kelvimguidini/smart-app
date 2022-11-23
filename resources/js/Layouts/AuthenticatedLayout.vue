<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
// Bootstrap core JavaScript
import '@/vendor/jquery/jquery.min.js';
import '@/vendor/bootstrap/js/bootstrap.bundle.min.js';

// Core plugin JavaScript
import '@/vendor/jquery-easing/jquery.easing.min.js';

// Custom scripts for all pages
import '@/vendor/sb-admin-2.js';

import '../../css/sb-admin-2.css';
import '../vendor/fontawesome-free/css/all.min.css';

import '@/vendor/datatables/jquery.dataTables.min.js';
import '@/vendor/datatables/dataTables.bootstrap4.min.js';
import '@/vendor/demo/datatables-demo.js';

import '../vendor/datatables/dataTables.bootstrap4.min.css';


import Menu from '@/Components/Menu.vue';
import NavBar from '@/Components/NavBar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import { Link } from '@inertiajs/inertia-vue3';
import { usePage } from '@inertiajs/inertia-vue3'


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
        active: route().current('register') || route().current('role'),
        isItem: false,
        collapseHeader: 'Administrar acessos',
        subMenu: [
            {
                link: route().current('role') ? '' : route('role'),
                name: 'Perfil',
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
        isItem: true,
        name: 'Administração',
        subMenu: [
            {
                link: route().current('dashboard') || route().current('/') ? '#' : route('dashboard'),
                name: 'Perfil',
                tooltip: 'perfil',
                icon: 'fa-solid fa-house',
                active: false,
            },
            {
                link: route().current('dashboard') || route().current('/') ? '#' : route('dashboard'),
                name: 'Usuário',
                tooltip: 'Usuário',
                icon: 'fa-solid fa-house',
                active: false,
            }
        ],
        icon: 'fa-solid fa-users',
        active: false,
        isItem: false
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

        <Menu :menu-items="menuItem" :togle="toggleMenu" :menu-title="$page.props.appName"></Menu>

        <FlashMessage v-if="$page.props.flash != null" :message="$page.props.flash.message"
            :type="$page.props.flash.type"></FlashMessage>

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
                    <header class="bg-white shadow" v-if="$slots.header">
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

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.html">Logout</a>
                </div>
            </div>
        </div>
    </div>

</template>
