<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Loader from '@/Components/Loader.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { ref, onMounted, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import EventActions from '@/Components/EventActions.vue';
import ProviderActions from '@/Components/ProviderActions.vue';
import draggable from 'vuedraggable';

const props = defineProps({
    events: Array,
    filters: Object,
    customers: Array,
    users: Array,
    allStatus: Object
});

const formFilters = useForm({
    startDate: '',
    endDate: '',
    city: '',
    consultant: '',
    client: '',
    status: '',
    eventCode: ''
});
const isLoader = ref(false);
const submitForm = () => {
    // Obter os valores dos filtros
    isLoader.value = true;
    // Fazer uma chamada Axios para enviar os dados dos filtros para o backend
    formFilters.post(route('event-list.filter'), {
        onFinish: () => {
            isLoader.value = false;
        },
    });
};

onMounted(() => {
    //Basico
    $('#customer').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formFilters.client = e.params.data.id;
    });

    $('#consultant').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formFilters.consultant = e.params.data.id;
    });

    $('#status_hotel').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formFilters.status = e.params.data.id;
    });


    $('.s_hotel').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        formStatus.status_hotel = e.params.data.id;
    });

});

const getStatusLabel = (status) => {
    return props.allStatus[status] ? props.allStatus[status].label : 'Solicitado';
};

const showEventDetails = ref(0);

const showHideEventDetails = (id) => {

    if (showEventDetails.value == id) {
        showEventDetails.value = 0;
    } else {
        showEventDetails.value = id;
    }
};

const providersByEvent = (event) => {

    var groups = [];

    event.event_hotels && event.event_hotels.map((current) => {
        if (!groups.some(g => g.type == 'Hotel' && g.id == current.hotel.id)) {

            groups.push({
                id: current.hotel.id,
                name: current.hotel.name,
                city: current.hotel.city,
                email: current.hotel.email,
                sended_mail: current.sended_mail,
                sended_mail_link: current.sended_mail_link,
                token_budget: current.token_budget,
                providerBudget: current.provider_budget,
                type: 'Hotel',
                table: 'event_hotels',
                table_id: current.id,
                status: current.status_his[0]?.status
            });
        }
    }, {});

    event.event_abs && event.event_abs.map((current) => {
        if (!groups.some(g => g.type == 'Hotel' && g.id == current.ab.id)) {
            groups.push({
                id: current.ab.id,
                name: current.ab.name,
                city: current.ab.city,
                email: current.ab.email,
                sended_mail: current.sended_mail,
                sended_mail_link: current.sended_mail_link,
                token_budget: current.token_budget,
                providerBudget: current.provider_budget,
                type: 'Hotel',
                table: 'event_abs',
                table_id: current.id,
                status: current.status_his[0]?.status
            });
        }
    }, {});

    event.event_halls && event.event_halls.map((current) => {
        if (!groups.some(g => g.type == 'Hotel' && g.id == current.hall.id)) {
            groups.push({
                id: current.hall.id,
                name: current.hall.name,
                city: current.hall.city,
                email: current.hall.email,
                sended_mail: current.sended_mail,
                sended_mail_link: current.sended_mail_link,
                token_budget: current.token_budget,
                providerBudget: current.provider_budget,
                type: 'Hotel',
                table: 'event_halls',
                table_id: current.id,
                status: current.status_his[0]?.status
            });
        }
    }, {});

    event.event_adds && event.event_adds.map((current) => {
        if (!groups.some(g => g.type == 'Provedor' && g.id == current.add.id)) {
            groups.push({
                id: current.add.id,
                name: current.add.name,
                city: current.add.city,
                email: current.add.email,
                sended_mail: current.sended_mail,
                sended_mail_link: current.sended_mail_link,
                token_budget: current.token_budget,
                providerBudget: current.provider_budget,
                type: 'Provedor',
                table: 'event_adds',
                table_id: current.id,
                status: current.status_his[0]?.status
            });
        }
    }, {});

    event.event_transports && event.event_transports.map((current) => {

        if (!groups.some(g => g.isTransport && g.id == current.transport.id)) {

            groups.push({
                id: current.transport.id,
                name: current.transport.name,
                city: current.transport.city,
                email: current.transport.email,
                type: 'Transporte Terrestre',
                sended_mail: current.sended_mail,
                token_budget: current.token_budget,
                providerBudget: current.provider_budget,
                isTransport: true,
                table: 'event_transports',
                table_id: current.id,
                status: current.status_his[0]?.status
            });
        }
    }, {});

    return groups;
}

const updateOrder = (event) => {
    const providers = providersByEvent(event);
    providers.forEach((provider, index) => {
        provider.order = index;
    });

    // Enviar a nova ordem para o backend
    axios.post(route('update-provider-order'), {
        event_id: event.id,
        providers: providers
    }).then(response => {
        console.log('Ordem atualizada com sucesso');
    }).catch(error => {
        console.error('Erro ao atualizar a ordem', error);
    });
};

const startDate = ref(new Date());
const endDate = ref(new Date());


const updateFormStart = () => {
    formFilters.startDate = startDate.value ? startDate.value.toISOString().split('T')[0] : '';
};

const updateFormEnd = () => {
    formFilters.endDate = endDate.value ? endDate.value.toISOString().split('T')[0] : '';
};
watch(
    () => props.filters,
    (newEvent) => {
        if (newEvent) {
            startDate.value = new Date(newEvent.startDate);
            endDate.value = new Date(newEvent.endDate);
        }
    },
    { immediate: true }
);

</script>
<style>
.cursor-pointer {
    cursor: pointer;
}
</style>

<template>
    <AuthenticatedLayout>

        <Loader v-bind:show="isLoader" />

        <Head title="Eventos" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Eventos</h1>
            </div>
        </template>
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4 py-3 border-left-secondary">
                    <div class="card-body">
                        <form @submit.prevent="submitForm">

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">

                                        <VDatePicker v-model="dateStart" @update:modelValue="updateFormStart"
                                            :max-date="dateEnd">
                                            <template #default="{ inputValue, inputEvents }">

                                                <label for="start-date">Data de Início:</label>
                                                <TextInput class="form-control custom-datepicker" :value="inputValue"
                                                    v-on="inputEvents" />

                                            </template>
                                        </VDatePicker>

                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <VDatePicker v-model="dateEnd" @update:modelValue="updateFormEnd"
                                            :min-date="dateStart">
                                            <template #default="{ inputValue, inputEvents }">

                                                <label for="end-date">Data de Fim:</label>
                                                <TextInput class="form-control custom-datepicker" :value="inputValue"
                                                    v-on="inputEvents" />

                                            </template>
                                        </VDatePicker>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="city">Cidade:</label>
                                        <input type="text" id="city" v-model="formFilters.city" class="form-control">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="consultant">Consultor:</label>
                                        <select class="form-control" id="client" required="required">
                                            <option>.::Selecione::.</option>
                                            <option v-for="(option, index) in users"
                                                :selected="option.id == formFilters.consultant" :value="option.id">
                                                {{ option.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="client">Cliente:</label>
                                        <select class="form-control" id="customer" :required="required">
                                            <option>.::Selecione::.</option>
                                            <option v-for="(option, index) in customers"
                                                :selected="option.id == formFilters.client" :value="option.id">
                                                {{ option.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="start-date">Status hotel:</label>
                                        <select class="form-control" id="status_hotel">
                                            <option>.::Selecione::.</option>

                                            <option v-for="(key, index) in Object.keys(allStatus)" :key="index"
                                                :value="key">
                                                {{ allStatus[key].label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="code">Código do Zendesk:</label>
                                        <input type="text" id="code" v-model="formFilters.eventCode"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn-sm btn-primary">Filtrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-lg-12">

                <div class="card mb-4 py-3 border-left-secondary">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Código do Zendesk</th>
                                        <th scope="col">Data</th>
                                        <th scope="col">Data Final</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(event, index) in events.data">
                                        <tr class="table-active cursor-pointer">
                                            <th @click="showHideEventDetails(event.id)" scope="row">{{ event.id }}
                                            </th>
                                            <td @click="showHideEventDetails(event.id)">{{ event.customer != null ?
                                                event.customer.name : ' - ' }}</td>
                                            <td @click="showHideEventDetails(event.id)">{{ event.name }}</td>
                                            <td @click="showHideEventDetails(event.id)">{{ event.code }}</td>
                                            <td @click="showHideEventDetails(event.id)">{{ new
                                                Date(event.date).toLocaleDateString() }}</td>
                                            <td @click="showHideEventDetails(event.id)">{{ new
                                                Date(event.date_final).toLocaleDateString() }}</td>
                                            <td>
                                                <EventActions :event="event" :index="index" />

                                            </td>
                                        </tr>
                                        <template v-if="showEventDetails == event.id">
                                            <draggable :list="providersByEvent(event)" @end="updateOrder(event)">
                                                <tr v-for="(prov, index) in providersByEvent(event)" :key="prov.id">
                                                    <th scope="row"></th>
                                                    <td colspan="3">{{ prov.type }}: {{ prov.name }}</td>
                                                    <td colspan="2">status: <b>{{ getStatusLabel(prov.status) }}</b>
                                                    </td>
                                                    <td>
                                                        <ProviderActions :event="event" :index="index" :prov="prov"
                                                            :get-status-label="getStatusLabel" :allStatus="allStatus" />
                                                    </td>
                                                </tr>
                                            </draggable>
                                            <tr v-if="providersByEvent(event).length === 0">
                                                <th scope="row"></th>
                                                <td colspan="6">Sem registro</td>
                                            </tr>
                                        </template>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Navegação da página -->
                        <nav aria-label="Page navigation example">
                            <ul class="pagination">
                                <li class="page-item" :class="{ 'disabled': !events.prev_page_url }">
                                    <a class="page-link" :href="route('event-list', { page: events.current_page - 1 })">
                                        Anterior
                                    </a>
                                </li>

                                <li class="page-item" v-for="page in events.last_page" :key="page"
                                    :class="{ 'active': page === events.current_page }">
                                    <a class="page-link" :href="route('event-list', { page: page })"
                                        v-if="page !== events.current_page">{{ page }}</a>
                                    <a class="page-link" v-if="page === events.current_page">{{ page }}</a>
                                </li>

                                <li class="page-item" :class="{ 'disabled': !events.next_page_url }">
                                    <a class="page-link" :href="route('event-list', { page: events.current_page + 1 })">
                                        Proxima
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
