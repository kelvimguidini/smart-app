<template>
    <Modal modal-title="Histórico de Alterações" btn-class="btn-sm btn-info btn-icon-split mr-2" :content-big="true">
        <template #button>
            <div v-on:click="loadHistory();">
                <span class="icon text-white-50">
                    <i class="fas fa-history"></i>
                </span>
                <span class="text">Histórico</span>
            </div>
        </template>

        <template #content>
            <div v-if="loading" class="text-center py-3">
                <i class="fas fa-spinner fa-spin"></i> Carregando histórico...
            </div>

            <div v-else>
                <div v-if="history.length">
                    <table class="table table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Usuário</th>
                                <th>Data</th>
                                <th>Aba</th>
                                <th>Ação</th>
                                <th>Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(item, index) in history" :key="index">
                                <tr>
                                    <td>{{ item.user?.name || 'Desconhecido' }}</td>
                                    <td>{{ formatDate(item.created_at, true) }}</td>
                                    <td>{{ tableLabels[item.table_name] }}</td>
                                    <td>
                                        <span :class="badgeClass(item.action)">
                                            {{ actionLabel(item.action) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button v-if="item.action === 'updated'" class="btn btn-sm btn-outline-info"
                                            @click="toggleDetails(index)">
                                            <i :class="item.showDetails ? 'fas fa-eye-slash' : 'fas fa-eye'"
                                                class="me-1"></i>
                                            {{ item?.showDetails ? 'Ocultar' : 'Ver' }}
                                        </button>

                                        <button v-if="item.action === 'deleted' && !item.restored"
                                            class="btn btn-sm btn-outline-success" @click="restoreFromHistory(item)"
                                            :disabled="restoring">
                                            <i :class="restoring ? 'fas fa-spinner fa-spin' : 'fas fa-undo'"></i>
                                            {{ restoring ? 'Restaurando...' : 'Recuperar' }}
                                        </button>

                                        <!-- se já foi restaurado, mostra label -->
                                        <span v-else-if="item.action === 'deleted' && item.restored"
                                            class="badge badge-success">
                                            Recuperado em {{ formatDate(item.restored_at, true) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="item.showDetails">
                                    <td colspan="5">
                                        <ul class="list-group list-group-flush diff-list">
                                            <li v-for="diff in diffFields(item)" :key="diff.field"
                                                class="list-group-item diff-item bg-light">
                                                <div class="d-flex align-items-start" style="gap:8px;">
                                                    <div class="field-name">
                                                        <b>{{ fieldLabels[diff.field] ?? diff.field }}: </b>
                                                    </div>
                                                    <div class="text-danger old-value" style="white-space: nowrap;">
                                                        {{ diff.old }}
                                                    </div>
                                                    <div class="mx-2 arrow">→</div>
                                                    <div class="text-success new-value" style="white-space: nowrap;">
                                                        {{ diff.new }}
                                                    </div>
                                                </div>
                                            </li>
                                            <li v-if="diffFields(item).length === 0" class="list-group-item text-muted">
                                                Nenhuma alteração relevante.
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div v-else class="alert alert-info text-center">
                    Nenhuma alteração registrada para este fornecedor.
                </div>
            </div>
        </template>
    </Modal>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import Modal from "./Modal.vue";

const props = defineProps({
    eventId: Number
});

const history = ref([]);
const loading = ref(false);
const restoring = ref(false);

const loadHistory = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get(
            route("history-get", [props.eventId])
        );
        history.value = data.map((h) => ({ ...h, showDetails: false }));
    } catch (error) {
        console.error("Erro ao carregar histórico:", error);
    } finally {
        loading.value = false;
    }
};

const toggleDetails = (index) => {
    history.value[index].showDetails = !history.value[index].showDetails;
};

const restoreFromHistory = async (item) => {
    restoring.value = true;
    try {
        await axios.post(route('history-restore'), {
            table: item.table_name,
            record_id: item.record_id,
            history_id: item.id
        });
        // Recarrega o histórico após a restauração
        await loadHistory();
    } catch (error) {
        console.error("Erro ao restaurar histórico:", error);
    } finally {
        restoring.value = false;
    }
};

const badgeClass = (action) => {
    switch (action) {
        case "created":
            return "badge bg-success";
        case "updated":
            return "badge bg-warning text-dark";
        case "deleted":
            return "badge bg-danger text-white";
        default:
            return "badge bg-secondary";
    }
};

const actionLabel = (action) => {
    switch (action) {
        case "created":
            return "Criado";
        case "updated":
            return "Alterado";
        case "deleted":
            return "Excluído";
        default:
            return action;
    }
};

const dateFields = [
    'deadline_date',
    'date',
    'date_final',
    'checkin',
    'checkout',
    'created_at',
    'updated_at',
    'in',
    'out',
];

const booleanFields = [
    'invoice',
];

const formatDate = (dateStr, showTime = false) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    const datePart = d.toLocaleDateString('pt-BR');
    if (!showTime) return datePart;
    const timePart = d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    return `${datePart} ${timePart}`;
};

const diffFields = (item) => {
    const oldObj = item.old_data ? JSON.parse(item.old_data) : {};
    const newObj = item.new_data ? JSON.parse(item.new_data) : {};
    const diffs = [];
    const ignoreFields = ['updated_at', 'created_at', 'deleted_at'];

    for (let key in newObj) {
        if (ignoreFields.includes(key)) continue;
        if (oldObj[key] !== newObj[key]) {
            let oldVal = oldObj[key] ?? '';
            let newVal = newObj[key] ?? '';
            // Formata se estiver no array de campos de data
            if (dateFields.includes(key)) {
                oldVal = formatDate(oldVal);
                newVal = formatDate(newVal);
            }
            // Formata campos booleanos
            if (booleanFields.includes(key)) {
                oldVal = (oldVal === true || oldVal === "true" || oldVal === 1 || oldVal === "1") ? "Sim" : "Não";
                newVal = (newVal === true || newVal === "true" || newVal === 1 || newVal === "1") ? "Sim" : "Não";
            }
            if (item.table_name === 'event_hall_opt' && key === 'name') {
                key = 'Descrição'
            }
            diffs.push({
                field: key,
                old: oldVal,
                new: newVal
            });
        }
    }
    return diffs;
};

const tableLabels = {
    event: "Base do Evento",
    event_hotel: "Hotel",
    event_hotel_opt: "Hotel - Detalhes",
    event_ab: "A&B",
    event_ab_opt: "A&B - Detalhes",
    event_add: "Adicional",
    event_add_opt: "Adicional - Detalhes",
    event_hall: "Salão",
    event_hall_opt: "Salão - Detalhes",
    event_transport: "Transporte",
    event_transport_opt: "Transporte - Detalhes",
};

const fieldLabels = {
    name: "Nome do Evento",
    code: "Código do Zendesk",
    requester: "Solicitante",
    customer_id: "Empresa",
    sector: "Setor",
    pax_base: "Base de Pax",
    cost_center: "Centro de Custo",
    date: "Data do Evento",
    date_final: "Data do Evento Fim",
    crd_id: "CRD",
    hotel_operator: "Operador - Hotel",
    air_operator: "Operador - Aéreo",
    land_operator: "Operador - Terrestre",

    //Provider
    provider_id: "Fornecedor",
    city: "Cidade",
    currency: "Moeda",
    invoice: "Nota Fiscal",
    iss_percent: "ISS (%)",
    service_percent: "Serviço (%)",
    iva_percent: "IVA (%)",
    service_charge: "Taxa de Turismo",
    iof: "IOF (%)",
    deadline: "Prazo",
    deadline_date: "Prazo",
    internal_observation: "Observação Interna",
    customer_observation: "Observação Cliente",
    taxa_4bts: "Taxa 4BTS (%)",
    type: "Tipo",
    currency_id: "Moeda",
    //OPTs genérico
    broker_id: "Broker",
    in: "IN",
    out: "OUT",
    count: "QTD",
    kickback: "Comissão (%)",
    received_proposal: "Proposta Recebida",
    received_proposal_percent: "Markup (%)",
    order: "Ordem",
    service_id: "Serviço",

    // Campos específicos de cada Opt
    // Hotel
    hotel_id: "Hotel",
    hotel: "Hotel",
    apto_hotel_id: "Apartamento",
    category_hotel_id: "Categoria",
    regime_id: "Regime",
    purpose_id: "Proposito",
    compare_trivago: "Comparação Trivago",
    compare_website_htl: "Comparação Website Hotel",
    compare_omnibess: "Comparação Omnibees",

    // AB
    ab_id: "A&B",
    ab: "A&B",
    service_type_id: "Tipo de Serviço",
    local_id: "Local",
    // Add
    add_id: "Adicional",
    add: "Adicional",
    measure_id: "Unidade de Medida",
    frequency_id: "Frequência",
    unit: "Unidade",
    // Hall
    hall_id: "Salão",
    hall: "Salão",
    // Transport
    transport_id: "Transporte",
    transport: "Transporte",
    vehicle_id: "Veículo",
    model_id: "Modelo",
    brand_id: "Marca",
    observation: "Observação",

    // Campos de valores e datas
    value: "Valor",
    qtd: "Quantidade",
    qtd_dayles: "Qtd Diárias",
    start_date: "Data Início",
    end_date: "Data Fim",
    checkin: "Check-in",
    checkout: "Check-out",
};

</script>

<style scoped>
/* items inline até encher a linha, depois quebram automaticamente */
.diff-list {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 8px;
    padding-left: 0;
    margin-bottom: 0;
}

/* cada diff fica compacto, em linha; não força largura fixa */
.diff-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
    /* não cresce para preencher a linha inteira */
    padding: 8px 12px;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    background: #fff;
    box-sizing: border-box;
    border-bottom-width: 1px !important;
}

/* evitar que nomes longos quebrem a linha interna */
.diff-item .field-name {
    font-weight: 600;
    white-space: nowrap;
    margin-right: 6px;
}

/* manter valores em uma única linha e truncar com ellipsis se exceder */
.old-value,
.new-value {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 220px;
    /* ajuste conforme necessidade */
}

/* seta compacta */
.arrow {
    color: #6c757d;
    white-space: nowrap;
}

.badge {
    font-size: 0.95rem;
}
</style>
