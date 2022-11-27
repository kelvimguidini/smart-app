<script setup>
import { onMounted } from 'vue';
const props = defineProps({

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
        $('#message').fadeOut(300, function () {
            $(this).remove();
        });
    }, 7000); // milliseconds
});

var cssMessage = "display: block; position: fixed; top: 0; left: 20%; right: 20%; width: 60%; padding-top: 10px; z-index: 9999";
var cssInner = "margin: 0 auto; box-shadow: 1px 1px 5px black;";

</script>


<template>
    <div id="message" :style="cssMessage">
        <div :class="'alert alert-' + type + ' alert-dismissable'" :style="cssInner">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
            {{ message }}
        </div>
    </div>
</template>
