<script setup>
import { ref, computed, onMounted, onUnmounted, defineExpose } from 'vue';
import '@/vendor/jquery/jquery.min.js';
import '@/vendor/bootstrap/js/bootstrap.bundle.min.js';
import '@/vendor/bootstrap/scss/bootstrap.scss';

const props = defineProps({
    dissmiss: {
        type: [Array, Boolean],
        default: true,
    },
});

onMounted(() => {
    window.addEventListener('global-flash', handleFlashEvent);
});

onUnmounted(() => {
    window.removeEventListener('global-flash', handleFlashEvent);
});

function handleFlashEvent(event) {


    const { message, type = 'info' } = event.detail || {};

    console.log(message);
    if (message) {
        show({ message, type });
    }
}
const showMessage = ref(false);
const message = ref('');
const type = ref('info');

const show = (flash = { message: '', type: 'info' }) => {
    message.value = flash.message;
    type.value = flash.type || 'info';
    showMessage.value = true;
    if (props.dissmiss) {
        setTimeout(() => (showMessage.value = false), 20000);
    };
}

const close = () => {
    showMessage.value = false;
};

defineExpose({ show }); // Expondo a função `show` para ser acessada via `ref`

const cssMessage = "display: block; position: fixed; top: 0; right: 0; padding-top: 10px; padding-right: 10px; z-index: 9999";
const cssInner = "margin: 0 auto; box-shadow: 1px 1px 5px black;";

</script>

<template>
    <div v-if="showMessage" id="message" :style="cssMessage">
        <div :class="'alert alert-' + type + ' alert-dismissable'" :style="cssInner">
            <a href="#" class="close" @click="close" style="padding-left: 10px;" aria-label="close">×</a>
            {{ message }}
        </div>
    </div>
</template>
