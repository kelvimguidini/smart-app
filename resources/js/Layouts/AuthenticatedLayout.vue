<script setup>
// Bootstrap core JavaScript
import '../vendor/jquery/jquery.min.js';
import '../vendor/bootstrap/js/bootstrap.bundle.min.js';

// Core plugin JavaScript
import '../vendor/jquery-easing/jquery.easing.min.js';

// Custom scripts for all pages
import '../vendor/sb-admin-2.min.js';

import '../../css/sb-admin-2.css';
import '../vendor/fontawesome-free/css/all.min.css';

// Page level plugins
// import '../vendor/chart.js/Chart.min.js';

// Page level custom scripts
// import '../vendor/demo/chart-area-demo.js';
// import '../vendor/demo/chart-pie-demo.js';


import { ref, } from 'vue';
import Menu from '@/Components/Menu.vue';
import NavBar from '@/Components/NavBar.vue';
import { Link } from '@inertiajs/inertia-vue3';

/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core'
/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
/* import specific icons */
import { faUserSecret, faBars, faTimes, faTv, faDashboard, faAngleUp } from '@fortawesome/free-solid-svg-icons'


/* add icons to the library */
library.add(faUserSecret, faBars, faTimes, faTv, faDashboard, faAngleUp)


const appName = window.document.getElementsByTagName('title')[0]?.innerText;

const menuItem = [
    {
        link: route().current('dashboard') || route().current('/') ? '' : route('dashboard'),
        name: 'Inicio',
        icon: 'fa-solid fa-house',
        active: route().current('dashboard') || route().current('/'),
        isItem: true,
        isDividerBefore: true,
        heading: ''
    },
    {
        name: 'Usuários',
        icon: 'fa-solid fa-users',
        active: route().current('register'),
        isItem: false,
        isDividerBefore: false,
        heading: '',
        collapseHeader: 'Administrar acessos',
        subMenu: [
            {
                link: route().current('profile') ? '' : route('dashboard'),
                name: 'Perfil',
                icon: 'fa-solid fa-user-lock',
                active: route().current('profile'),
            },
            {
                link: route().current('register') ? '' : route('register'),
                name: 'Usuário',
                icon: 'fa-solid fa-user',
                active: route().current('register'),
            }
        ],
    },
    {
        isItem: true,
        name: 'Administração',
        tooltip: 'Administrar Usuário',
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

</script>

<template>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <Menu :menu-items="menuItem" :menu-title="appName"></Menu>



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
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
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
    <FontAwesomeIcon icon="fa fa-angle-up" />
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
