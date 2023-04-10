
<script setup>
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import CKEditor from '@ckeditor/ckeditor5-vue';
import { computed } from 'vue';

const props = defineProps({
    content: {
        type: [Array, String],
        default: false,
    },
    value: {
        default: null,
    },
    height: {
        type: Number,
        default: 150,
    },
});
const emit = defineEmits(['update:content']);

const proxyContent = computed({
    get() {
        return props.content;
    },

    set(val) {
        emit('update:content', val);
        emit('update:contentCode', encodeURIComponent(val));
    },
});

</script>

<template>
    <div>
        <CKEditor.component :editor="ClassicEditor" v-model="proxyContent"
            :toolbar="['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote']"
            :style="{ height: `${height}px` }" />
    </div>
</template>
