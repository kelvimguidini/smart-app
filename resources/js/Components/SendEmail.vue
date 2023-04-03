<script setup>
import Modal from '@/Components/Modal.vue';

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
        default: 'btn btn-primary'
    },
});

const id = 'modal-' + Math.floor(Date.now() * Math.random()).toString(36)

</script>

<template>
    <Modal :modal-title="'Link para ser enviado ao contato do hotel para preencher o orçamento.'"
        :ok-botton-callback="createLink" :ok-botton-callback-param="{ event: event.id, provider: prov.id }"
        ok-botton-label="Enviar link por email" btn-class="btn btn-secondary btn-icon-split mr-2">
        <template v-slot:button>
            <span class="icon text-white-50">
                <i class="fas fa-link"></i>
            </span>
            <span class="text">Criar link Orçamento</span>
        </template>
        <template v-slot:content>

        </template>
    </Modal>
</template>
