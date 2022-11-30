<script setup>
import { onMounted } from 'vue';
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


onMounted(() => {

    // contador de tempo para a mensagem sumir
    setTimeout(function () {
        $('#message').fadeOut(1000, function () {
            props.show = false;
            $(this).remove()
        });
    }, 7000); // milliseconds
});

var cssMessage = "display: block; position: fixed; top: 0; right: 0; padding-top: 10px; padding-right: 10px; z-index: 9999";
var cssInner = "margin: 0 auto; box-shadow: 1px 1px 5px black;";

</script>


<template>
    <div id="message" :style="cssMessage">
        <div :class="'alert alert-' + type + ' alert-dismissable'" :style="cssInner">
            <a href="#" class="close" data-dismiss="alert" style="padding-left: 10px;" aria-label="close">×</a>
            {{ message }}
        </div>
    </div>
</template>
