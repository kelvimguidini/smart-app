<script setup>

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
                isItem: true,
                isDividerBefore: true,
                heading: ''
            },

        ],
    }
});

</script>


<template>

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center">
            <div class="sidebar-brand-text mx-3">{{ menuTitle }}</div>
        </a>

        <span v-for="(menuItem, index) in menuItems" :key="index">


            <!-- Divider -->
            <hr v-if="menuItem.isDividerBefore" class="sidebar-divider my-0">

            <!-- Heading -->
            <div v-if="menuItem.heading != null" class="sidebar-heading">
                {{ menuItem.heading }}
            </div>

            <!-- Nav Item -->
            <li :class="{ 'active': menuItem.active }" v-if="menuItem.isItem" class="nav-item">
                <a class="nav-link" :href="menuItem.link">
                    <i :class="menuItem.icon || 'fa-solid fa-check'"></i>
                    <span>{{ menuItem.name }}</span></a>
            </li>

            <!-- Nav Item - Collapse Menu -->
            <li class="nav-item" v-if="!menuItem.isItem" :class="{ 'active': menuItem.active }">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i :class="menuItem.icon || 'fa-solid fa-check'"></i>
                    <span>{{ menuItem.name }}</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">{{ menuItem.collapseHeader }}:</h6>
                        <a v-for="(subMenuItem, index) in menuItem.subMenu" :key="index" class="collapse-item"
                            :href="subMenuItem.link" :class="{ 'active': subMenuItem.active }">
                            {{ subMenuItem.name }}</a>
                    </div>
                </div>
            </li>
        </span>

    </ul>
    <!-- End of Sidebar -->
</template>
