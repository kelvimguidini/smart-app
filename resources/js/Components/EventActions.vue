<template>
    <div>

        <!-- Modal de câmbio por moeda -->
        <Modal :key="index" modal-title="Câmbio" :ok-botton-callback="saveExchangeRates"
            v-if="event.currencies && event.currencies.length" :ok-botton-callback-param="event.id"
            btn-class="btn-sm btn-primary btn-icon-split mr-2">
            <template v-slot:button>
                <span class="icon text-white-50">
                    <i class="fas fa-exchange-alt"></i>
                </span>
                <span class="text">Câmbio</span>
            </template>
            <template v-slot:content>
                <div class="form-group">
                    <div v-for="currency in event.currencies" :key="currency.id" class="mb-2">
                        <label :for="'exchangeRate_' + event.id + '_' + currency.sigla">
                            Câmbio {{ currency.sigla }}:
                        </label>
                        <input type="text" class="form-control" v-model="exchangeRates[currency.sigla]"
                            :id="'exchangeRate_' + event.id + '_' + currency.sigla" />
                    </div>
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

        <ModalHistory v-if="$page.props.auth.permissions.some((p) => p.name === 'show_history')" :event-id="event.id"
            :key="'history-' + event.id" />

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
import ModalHistory from './ModalHistory.vue';

const formDelete = useForm({
    id: 0
});
const isLoader = ref(false);

// Para múltiplas moedas
const exchangeRates = ref({});
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



// Salva todos os câmbios das moedas
const saveExchangeRates = async (eventId) => {
    try {
        isLoader.value = true;
        // Monta objeto para envio: { moeda: valor }
        const rates = {};
        if (event.currencies && event.currencies.length) {
            event.currencies.forEach(currency => {
                const val = $("#exchangeRate_" + eventId + "_" + currency.sigla).maskMoney('unmasked')[0];
                rates[currency.sigla] = val;
            });
        }
        await axios.post(route('event-save-exchange-rate'), {
            event_id: eventId,
            exchange_rates: rates
        });
    } catch (error) {
        console.error('Erro ao salvar os câmbios:', error);
    } finally {
        isLoader.value = false;
    }
};

const props = defineProps({
    event: Object,
    index: Number
});


// Função para atualizar os campos de câmbio por moeda
const updateFields = () => {
    // Atualiza câmbios por moeda
    if (props.event?.currencies && Array.isArray(props.event.currencies)) {
        props.event.currencies.forEach(currency => {
            if (!exchangeRates.value) exchangeRates.value = {};
            // Se já existe valor salvo para a moeda
            if (props.event.exchange_rates && props.event.exchange_rates[currency.sigla]) {
                exchangeRates.value[currency.sigla] = props.event.exchange_rates[currency.sigla];
                $("#exchangeRate_" + props.event.id + "_" + currency.sigla).maskMoney('mask', props.event.exchange_rates[currency.sigla]);
            } else {
                exchangeRates.value[currency.sigla] = '';
            }
        });
    }
    // Valor faturamento
    if (props.event?.valor_faturamento) {
        vlFaturamento.value = props.event.valor_faturamento;
    }
};

// Chama na montagem inicial
onMounted(updateFields);

// Chama sempre que o evento mudar
watch(() => props.event, updateFields, { deep: true });
</script>