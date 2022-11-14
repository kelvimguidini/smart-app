<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';

/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core';
/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
/* import specific icons */
import { faUserSecret, faBars, faTimes, faTv, faGripHorizontal, faHome, faCheck, faUsers } from '@fortawesome/free-solid-svg-icons';

/* add icons to the library */
library.add(faUserSecret, faBars, faTimes, faTv, faGripHorizontal, faHome, faCheck, faUsers);


const props = defineProps({
    //! Menu settings
    isMenuOpen: {
        type: Boolean,
        default: true,
    },
    menuTitle: {
        type: String,
        default: '',
    },
    menuLogo: {
        type: String,
        default: '',
    },
    menuIcon: {
        type: String,
        default: '',
    },
    isPaddingLeft: {
        type: Boolean,
        default: true,
    },
    menuOpenedPaddingLeftBody: {
        type: String,
        default: '250px'
    },
    menuClosedPaddingLeftBody: {
        type: String,
        default: '78px'
    },
    //! Menu items
    menuItems: {
        type: Array,
        default: () => [
            {
                link: '#',
                name: 'Dashboard',
                tooltip: 'Dashboard',
                icon: 'fa-solid fa-house',
            },
            {
                link: '#',
                name: 'User',
                tooltip: 'User',
            },
            {
                link: '#',
                name: 'Messages',
                tooltip: 'Messages',
                icon: 'bx-chat',
            },
            {
                link: '#',
                name: 'Analytics',
                tooltip: 'Analytics',
                icon: 'bx-pie-chart-alt-2',
            },
            {
                link: '#',
                name: 'File Manager',
                tooltip: 'Files',
                icon: 'bx-folder',
            },
            {
                link: '#',
                name: 'Order',
                tooltip: 'Order',
                icon: 'bx-cart-alt',
            },
            {
                link: '#',
                name: 'Saved',
                tooltip: 'Saved',
                icon: 'bx-heart',
            },
            {
                link: '#',
                name: 'Setting',
                tooltip: 'Setting',
                icon: 'bx-cog',
            },
        ],
    },
    //! Profile detailes
    profileImg: {
        type: String,
        default: new URL('/resources/images/logo.png', import.meta.url).href,
    },
    profileName: {
        type: String,
        default: 'Fayzullo Saidakbarov',
    },
    profileRole: {
        type: String,
        default: 'Frontend vue developer',
    },
    isExitButton: {
        type: Boolean,
        default: true,
    },
    isLoggedIn: {
        type: Boolean,
        default: true,
    },
    //! Styles
    bgColor: {
        type: String,
        default: '#11101d',
    },
    secondaryColor: {
        type: String,
        default: '#1d1b31',
    },
    homeSectionColor: {
        type: String,
        default: '#e4e9f7',
    },
    logoTitleColor: {
        type: String,
        default: '#fff',
    },
    iconsColor: {
        type: String,
        default: '#fff',
    },
    itemsTooltipColor: {
        type: String,
        default: '#e4e9f7',
    },
    menuItemsHoverColor: {
        type: String,
        default: '#fff',
    },
    menuItemsActiveColor: {
        type: String,
        default: '#ccc',
    },
    menuItemsTextColor: {
        type: String,
        default: '#fff',
    },
    menuFooterTextColor: {
        type: String,
        default: '#fff',
    },
});
const isOpened = ref(true);
onMounted(() => props.isOpened = props.isMenuOpen);

const cssVars = computed(() => {
    return {
        // '--padding-left-body': this.isOpened ? this.menuOpenedPaddingLeftBody : this.menuClosedPaddingLeftBody,
        '--bg-color': props.bgColor,
        '--secondary-color': props.secondaryColor,
        '--home-section-color': props.homeSectionColor,
        '--logo-title-color': props.logoTitleColor,
        '--icons-color': props.iconsColor,
        '--items-tooltip-color': props.itemsTooltipColor,
        '--menu-items-hover-color': props.menuItemsHoverColor,
        '--menu-items-active-color': props.menuItemsActiveColor,
        '--menu-items-text-color': props.menuItemsTextColor,
        '--menu-footer-text-color': props.menuFooterTextColor,
    }
});

</script>


<template>
    <div class="sidebar" :class="isOpened ? 'open' : ''" :style="cssVars">
        <div class="logo-details" style="margin: 6px 14px 0 14px;">
            <img v-if="menuLogo" :src="menuLogo" alt="menu-logo" class="menu-logo icon">
            <i v-else class="bx icon" :class="menuIcon" />
            <div v-if="isOpened" class="logo_name">
                {{ menuTitle }}
            </div>
            <FontAwesomeIcon :icon="isOpened ? 'fas fa-times' : 'fa-solid fa-bars'" class="bx"
                :class="isOpened ? 'bx-menu-alt-right' : 'bx-menu'" id="btn" @click="isOpened = !isOpened">
            </FontAwesomeIcon>

        </div>

        <div
            style="display: flex ; flex-direction:column; justify-content: space-between; flex-grow: 1; max-height: calc(100% - 60px); ">
            <div id="my-scroll" style="margin: 6px 14px 0 14px;">
                <ul class="nav-list" style="overflow: visible;">

                    <span v-for="(menuItem, index) in menuItems" :key="index">
                        <li>
                            <a v-if="menuItem.isItem" :href="menuItem.link" :class="{ 'active': menuItem.active }">
                                <FontAwesomeIcon :icon="menuItem.icon || 'fa-solid fa-check'" class="bx">
                                </FontAwesomeIcon>
                                <span class="links_name">{{ menuItem.name }}</span>
                            </a>
                            <Dropdown v-if="!menuItem.isItem" :keep-open="menuItem.active"
                                :content-classes="['py-1', 'submenu']"
                                :content-father-classes="isOpened ? 'submenu-father-fluid' : 'submenu-father'"
                                :align="isOpened ? 'right' : 'left'">
                                <template #trigger>
                                    <a href="#" :class="{ 'active': menuItem.active }">
                                        <FontAwesomeIcon :icon="menuItem.icon || 'fa-solid fa-check'" class="bx">
                                        </FontAwesomeIcon>
                                        <span class="links_name">{{ menuItem.name }}</span>

                                        <svg class="ml-2 -mr-0.5 h-4 w-4 hasSubMenu" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </template>

                                <template #content>
                                    <ul class="" style="overflow: visible;">

                                        <span v-for="(subMenuItem, index) in menuItem.subMenu" :key="index">
                                            <li>
                                                <a :href="subMenuItem.link" :class="{ 'active': subMenuItem.active }">
                                                    <FontAwesomeIcon :icon="subMenuItem.icon || 'fa-solid fa-check'"
                                                        class="bx">
                                                    </FontAwesomeIcon>
                                                    <span class="links_name">{{ subMenuItem.name }}</span>
                                                </a>
                                            </li>
                                        </span>
                                    </ul>
                                </template>
                            </Dropdown>
                            <span class="tooltip">{{ menuItem.tooltip || menuItem.name }}</span>
                        </li>
                    </span>
                </ul>
            </div>

            <div v-if="isLoggedIn" class="profile">
                <div class="profile-details">
                    <img v-if="profileImg" :src="profileImg" alt="profileImg">
                    <i v-else class="bx bxs-user-rectangle" />
                    <div class="name_job">
                        <div class="name">
                            {{ profileName }}
                        </div>
                        <div class="job">
                            {{ profileRole }}
                        </div>
                    </div>
                </div>
                <i v-if="isExitButton" class="bx bx-log-out" id="log_out" @click.stop="$emit('button-exit-clicked')" />
            </div>
        </div>
    </div>
</template>
