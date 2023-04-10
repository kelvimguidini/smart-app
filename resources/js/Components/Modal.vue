<script setup>

import { Link } from '@inertiajs/inertia-vue3';

const props = defineProps({
    modalTitle: {
        type: String,
        default: '',
    },
    okBottonLabel: {
        type: String,
        default: 'Confirmar',
    },
    btnClass: {
        type: String,
        default: 'btn btn-primary'
    },
    okBottonCallback: {
        type: Function,
        default: null,
    },
    okBottonCallbackParam: {
        // type: any,
        default: null,
    },
    btnDisabled: {
        type: Boolean,
        default: false
    },
    btnIsLink: {
        type: Boolean,
        default: false
    },
    url: {
        type: String,
        default: ''
    }
});

const id = 'modal-' + Math.floor(Date.now() * Math.random()).toString(36)

</script>

<template>
    <a href="" :class="btnClass" :disabled="btnDisabled" data-toggle="modal" :data-target="'#' + id">
        <slot name="button" />
    </a>

    <!-- Modal -->
    <div class="modal fade" :id="id" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="exampleModalLabel">{{ modalTitle }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <slot name="content" />
                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button v-if="okBottonCallback != null && !btnIsLink" type="button" class="btn btn-primary"
                        v-on:click="okBottonCallback(okBottonCallbackParam)" data-dismiss="modal">
                        {{ okBottonLabel }}
                    </button>
                    <Link v-on:click="okBottonCallback(okBottonCallbackParam)" data-dismiss="modal" v-if="btnIsLink"
                        class="btn btn-primary" :href="url">{{
                            okBottonLabel }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
