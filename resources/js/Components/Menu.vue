<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
const props = defineProps({
    //! Menu settings
    menuTitle: {
        type: String,
        default: '',
    },
    //! Menu items
    menuItems: {
        type: Array,
        default: () => [
            {
                link: '#',
                name: 'Inicio',
                icon: 'fa-solid fa-house',
                active: true,
                isItem: true
            },

        ],
    }
});

</script>


<template>

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar - Brand -->
        <Link class="sidebar-brand d-flex align-items-center justify-content-center">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">{{ menuTitle }}</div>
        </Link>

        <li v-for="(menuItem, index) in menuItems" :key="index" :class="{ 'active': menuItem.active }" class="nav-item">

            <!-- Nav Item -->
            <Link v-if="menuItem.isItem" class="nav-link" :href="menuItem.link">
            <i :class="menuItem.icon || 'fa-solid fa-check'"></i>
            <span>{{ menuItem.name }}</span>
            </Link>

            <!-- Nav Item - Collapse Menu -->
            <a v-if="!menuItem.isItem && menuItem.subMenu.some((s) => $page.props.auth.permissions.some((p) => p.name === s.role || (Array.isArray(s.role) && s.role.some((r) => r == p.name))))"
                class="nav-link collapsed" href="#" data-toggle="collapse" :data-target="'#collapse1' + index"
                aria-expanded="true" :aria-controls="'collapse1' + index">
                <i :class="menuItem.icon || 'fa-solid fa-check'"></i>
                <span>{{ menuItem.name }}</span>
            </a>
            <div v-if="!menuItem.isItem" :id="'collapse1' + index" class="collapse"
                :class="{ 'show': menuItem.subMenu.some((s) => s.active) }" aria-labelledby="headingTwo"
                style="visibility: inherit" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">{{ menuItem.collapseHeader }}:</h6>
                    <Link
                        v-for="(subMenuItem, subIndex) in menuItem.subMenu.filter((s) => $page.props.auth.permissions.some((p) => p.name === s.role || (Array.isArray(s.role) && s.role.some((r) => r == p.name))))"
                        :key="subIndex" class="collapse-item" :href="subMenuItem.link"
                        :class="{ 'active': subMenuItem.active }">
                    {{ subMenuItem.name }}
                    </Link>
                </div>
            </div>
        </li>


        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

    </ul>
    <!-- End of Sidebar -->
</template>
