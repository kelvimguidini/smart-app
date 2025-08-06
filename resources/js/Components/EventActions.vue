<template>
    <div>

        <!-- Modal de câmbio -->
        <Modal :key="index" modal-title="Câmbio" :ok-botton-callback="saveExchangeRate"
            :ok-botton-callback-param="event.id" btn-class="btn-sm btn-primary btn-icon-split mr-2">
            <template v-slot:button>
                <span class="icon text-white-50">
                    <i class="fas fa-exchange-alt"></i>
                </span>
                <span class="text">Câmbio</span>
            </template>
            <template v-slot:content>
                <div class="form-group">
                    <label for="exchangeRate">Valor do Câmbio:</label>
                    <input type="text" class="form-control money" v-model="exchangeRate"
                        :id="'exchangeRate_' + event.id" />
                </div>
            </template>
        </Modal>

        <Modal :key="index" modal-title="Valor do Faturamento" :ok-botton-callback="saveFaturamento"
            :ok-botton-callback-param="event.id" btn-class="btn-sm btn-warning btn-icon-split mr-2">
            <template v-slot:button>
                <span class="icon text-white-50">
                    <i class="fas fa-money-bill-1-wave"></i>
                </span>
                <span class="text">Vl. Faturamento</span>
            </template>
            <template v-slot:content>
                <div class="form-group">
                    <label for="vlFaturamento_">Valor do Faturamento:</label>
                    <input type="text" class="form-control money" v-model="vlFaturamento"
                        :id="'vlFaturamento_' + event.id" />
                </div>
            </template>
        </Modal>

        <Link v-if="$page.props.auth.permissions.some((p) => p.name === 'event_admin')"
            class="btn-sm btn-info btn-icon-split mr-2" :href="route('event-edit', { id: event.id })">
        <span class="icon text-white-50">
            <i class="fas fa-edit"></i>
        </span>
        <span class="text">Editar</span>
        </Link>

        <Modal v-if="$page.props.auth.permissions.some((p) => p.name === 'event_admin')" :key="index"
            :modal-title="'Confirmar Exclusão de ' + event.name" :ok-botton-callback="deleteEvent"
            :ok-botton-callback-param="event.id" btn-class="btn-sm btn-danger btn-icon-split mr-2">
            <template v-slot:button>
                <span class="icon text-white-50">
                    <i class="fas fa-trash"></i>
                </span>
                <span class="text">Excluir</span>
            </template>
            <template v-slot:content>
                Tem certeza que deseja apagar esse registro?
            </template>
        </Modal>


    </div>
    <Loader v-bind:show="isLoader" />
</template>

<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import Loader from './Loader.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const formDelete = useForm({
    id: 0
});
const isLoader = ref(false);

const exchangeRate = ref(0);
const vlFaturamento = ref(0);

const deleteEvent = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('event-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
        },
    });
};

const saveFaturamento = async (eventId) => {
    try {
        isLoader.value = true;
        await axios.post(route('event-save-vl-faturamento'), {
            event_id: eventId,
            vl_faturamento: $('#vlFaturamento_' + eventId).maskMoney('unmasked')[0]
        });
    } catch (error) {
        console.error('Erro ao salvar o Valor do faturamento:', error);
    } finally {
        isLoader.value = false;
    }
};


const saveExchangeRate = async (eventId) => {
    try {
        isLoader.value = true;
        await axios.post(route('event-save-exchange-rate'), {
            event_id: eventId,
            exchange_rate: $('#exchangeRate_' + eventId).maskMoney('unmasked')[0]
        });
    } catch (error) {
        console.error('Erro ao salvar o câmbio:', error);
    } finally {
        isLoader.value = false;
    }
};

const props = defineProps({
    event: Object,
    index: Number
});


onMounted(() => {
    $('.money').maskMoney({ prefix: 'R$ ', allowNegative: false, allowZero: true, thousands: '.', decimal: ',', affixesStay: true });
    if (props.event?.exchange_rate) {
        exchangeRate.value = props.event?.exchange_rate;
        $("#exchangeRate_" + props.event.id).maskMoney('mask', props.event?.exchange_rate);
    }
    if (props.event?.valor_faturamento) {
        vlFaturamento.value = props.event?.valor_faturamento;
        $("#vlFaturamento_" + props.event.id).maskMoney('mask', props.event?.valor_faturamento);
    }
});
</script>