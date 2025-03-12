<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'info',
    },
});

const isVisible = ref(props.show);
const flashMessage = ref(props.message);
const flashType = ref(props.type);

const showMessage = (message, type = 'info') => {
    flashMessage.value = message;
    flashType.value = type;
    isVisible.value = true;

    setTimeout(() => {
        isVisible.value = false;
    }, 20000); // milliseconds
};

onMounted(() => {
    if (props.show) {
        showMessage(props.message, props.type);
    }
});

const cssMessage = "display: block; position: fixed; top: 0; right: 0; padding-top: 10px; padding-right: 10px; z-index: 9999";
const cssInner = "margin: 0 auto; box-shadow: 1px 1px 5px black;";

</script>

<template>
    <div v-if="isVisible" id="message" :style="cssMessage">
        <div :class="'alert alert-' + flashType + ' alert-dismissable'" :style="cssInner">
            <a href="#" class="close" @click="isVisible = false" style="padding-left: 10px;" aria-label="close">×</a>
            {{ flashMessage }}
        </div>
    </div>
</template>
