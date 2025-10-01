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
                    <input type="text" class="form-control" v-model="exchangeRate" :id="'exchangeRate_' + event.id" />
                </div>
            </template>
        </Modal>

        <Modal :key="index" modal-title="Número da Venda" :ok-botton-callback="saveFaturamento"
            :ok-botton-callback-param="event.id" btn-class="btn-sm btn-warning btn-icon-split mr-2">
            <template v-slot:button>
                <span class="icon text-white-50">
                    <i class="fas fa-tag"></i>
                </span>
                <span class="text">N° Venda</span>
            </template>
            <template v-slot:content>
                <div class="form-group">
                    <label for="vlFaturamento_">Número da Venda:</label>
                    <input type="number" class="form-control" v-model="vlFaturamento"
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

        <Modal :modal-title="'Visualizar Evento: ' + event.name" btn-class="btn-sm btn-secondary btn-icon-split mr-2"
            :contentBig="true">
            <template v-slot:button>
                <span class="icon text-white-50">
                    <i class="fas fa-eye"></i>
                </span>
                <span class="text">Ver</span>
            </template>
            <template v-slot:content>
                <EventView :event="event" />
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
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import EventView from '@/Components/EventView.vue';

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
            vl_faturamento: $('#vlFaturamento_' + eventId).val()
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

// Função para atualizar os campos
const updateFields = () => {
    if (props.event?.exchange_rate) {
        exchangeRate.value = props.event.exchange_rate;
        $("#exchangeRate_" + props.event.id).maskMoney('mask', props.event.exchange_rate);
    }
    if (props.event?.valor_faturamento) {
        vlFaturamento.value = props.event.valor_faturamento;
    }
};

// Chama na montagem inicial
onMounted(updateFields);

// Chama sempre que o evento mudar
watch(() => props.event, updateFields, { deep: true });
</script>