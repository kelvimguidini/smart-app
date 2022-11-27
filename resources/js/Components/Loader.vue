<script setup>

import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
});

</script>

<template>
    <div v-if="show" class="app-load__2" id="js-load">
        <div class="app-load__content">
            <div class="app-load__img">
                <ApplicationLogo></ApplicationLogo>
            </div>
            <div class="app-load__icon">
                <div></div>
            </div><span class="app-wid-100 app-load__text" id="js-text-load">Processando sua Solicitação</span>
        </div>
    </div>
</template>
