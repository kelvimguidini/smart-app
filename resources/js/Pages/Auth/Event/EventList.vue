<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Loader from '@/Components/Loader.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import { ref, onMounted, computed } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import CKEditor from '@/Components/CKEditor.vue';
import Datepicker from 'vue3-datepicker';
import { ptBR } from 'date-fns/locale'
import { v4 as uuidv4 } from 'uuid';
import axios from 'axios';

const props = defineProps({
    events: Array,
    filters: Object,
    customers: Array,
    users: Array,
    allStatus: Object
});

const formDelete = useForm({
    id: 0
});

const formStatus = useForm({

    table: '',
    table_id: 0,
    status_hotel: '',
    observation_hotel: '',
    notify: false,
    emailsLink: '',
    messageLink: '',
    copyMeLink: false
});

const formFilters = useForm({
    startDate: '',
    endDate: '',
    city: '',
    consultant: '',
    client: '',
    status: '',
});

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

const showInvoicement = (status) => {
    if (Array.isArray(status) && status.length > 0) {
        status = status[0];
    }
    return false;
};

const getStatusLabel = (status) => {
    return props.allStatus[status] ? props.allStatus[status].label : 'Status Desconhecido';
};

const editStatus = async (event_id, table, table_id, permissions) => {
    try {
        // Inicie o carregamento
        isLoader.value = true;

        // Faça a chamada ao endpoint
        const response = await axios.get(route('status-history', {
            table: table, // Substitua 'events' pelo nome real da tabela
            table_id: table_id
        }));
        var currentStatus = response.data[0];
        var currentStatusInfo = props.allStatus[currentStatus.status] || {};
        var allowedFlows = currentStatusInfo.flow || [];


        statusOptions.value = Object.entries(props.allStatus).filter(([key, status]) =>
            allowedFlows.includes(key)
            &&
            (
                (status.level === 1 && permissions.some((p) => p.name === 'status_level_1' || p.name === 'status_level_2')) ||
                (status.level === 2 && permissions.some((p) => p.name === 'status_level_2'))
            )
        );

        formStatus.table = table;
        formStatus.table_id = table_id;
        formStatus.event_id = event_id;

        history.value = response.data;

    } catch (error) {
        console.error('Erro ao obter o histórico de status:', error);
        // Adicione a lógica para lidar com o erro, se necessário
    } finally {
        // Encerre o carregamento, mesmo se ocorrer um erro
        isLoader.value = false;
    }
};

const history = ref([]);
const statusOptions = ref({});

const saveStatus = () => {
    isLoader.value = true;
    formStatus.post(route('event-status-save'), {
        onFinish: () => {
            isLoader.value = false;
            formStatus.reset();
        },
    });
}
const isLoader = ref(false);
const emails = ref('');
const sendEmail = ref(true);
const message = ref('');
const copyMe = ref(false);


const emailsLink = ref('');
const sendEmailLink = ref(true);
const messageLink = ref('');
const copyMeLink = ref(false);
const attachmentLink = ref(false);
const linkEmail = ref(true);
const token = ref(uuidv4());
const link = ref('');


const emailsInvoice = ref('');
const sendEmailInvoice = ref(true);
const messageInvoice = ref('');
const copyMeInvoice = ref(false);


const flash = ref(null);

const showEventDetails = ref(0);

const showHideEventDetails = (id) => {

    if (showEventDetails.value == id) {
        showEventDetails.value = 0;
    } else {
        showEventDetails.value = id;
    }
};

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

const createLink = (array) => {
    isLoader.value = true;
    flash.value = null;

    if (array['download']) {
        // Abrir uma nova guia com a URL de download
        const downloadUrl = route('create-link', {
            download: array['download'],
            provider_id: array['provider_id'],
            event_id: array['event_id'],
            link: array['link']
        });

        const downloadWindow = window.open(downloadUrl, '_blank');

        // Verificar periodicamente se a guia foi fechada
        const checkDownloadWindow = setInterval(() => {
            if (downloadWindow.closed) {
                isLoader.value = false;
                flash.value = {
                    message: "Link gerado com sucesso!  \n\n" + route('budget', { token: array['link'] }),
                    type: 'success'
                };
                clearInterval(checkDownloadWindow);
            }
        }, 1000);
    } else {
        const formLink = useForm({});

        formLink.get(route('create-link', {
            download: array['download'],
            provider_id: array['provider_id'],
            event_id: array['event_id'],
            message: array['message'] == '' ? '!' : array['message'],
            emails: array['emails'],
            copyMe: array['copyMe'],
            attachment: array['attachment'],
            link: array['link'],
            linkEmail: array['linkEmail'],
        }), {
            onFinish: () => {
                isLoader.value = false;
                flash.value = {
                    message: "Link gerado com sucesso!  \n\n" + route('budget', { token: array['link'] }),
                    type: 'success'
                };
            },
        });
    }

};

const newObjcts = (tokenLocal, email) => {

    emailsLink.value = email;
    sendEmailLink.value = true;
    if (tokenLocal != null) {
        link.value = tokenLocal;
    } else {
        link.value = token.value;
    }
    messageLink.value = '';
    copyMeLink.value = false;
    attachmentLink.value = false;
    linkEmail.value = true;
}

const sendProposal = (array) => {
    isLoader.value = true;

    if (array['download']) {
        // Abrir uma nova guia com a URL de download
        const downloadUrl = route('proposal-hotel', {
            download: array['download'],
            provider_id: array['provider_id'],
            event_id: array['event_id'],
        });

        const downloadWindow = window.open(downloadUrl, '_blank');

        // Verificar periodicamente se a guia foi fechada
        const checkDownloadWindow = setInterval(() => {
            if (downloadWindow.closed) {
                // Quando a guia for fechada, parar o loader e limpar o temporizador
                isLoader.value = false;
                clearInterval(checkDownloadWindow);
            }
        }, 1000);
    } else {
        const formProposal = useForm({

        });

        formProposal.get(route('proposal-hotel', {
            download: array['download'],
            provider_id: array['provider_id'],
            event_id: array['event_id'],
            message: array['message'],
            emails: array['emails'],
            copyMe: array['copyMe']
        }), {
            onFinish: () => {
                isLoader.value = false;
            },
        });
    }


};

const sendInvoice = (array) => {
    isLoader.value = true;

    if (array['download']) {
        // Abrir uma nova guia com a URL de download
        const downloadUrl = route('invoice', {
            download: array['download'],
            provider_id: array['provider_id'],
            event_id: array['event_id'],
        });

        const downloadWindow = window.open(downloadUrl, '_blank');

        // Verificar periodicamente se a guia foi fechada
        const checkDownloadWindow = setInterval(() => {
            if (downloadWindow.closed) {
                // Quando a guia for fechada, parar o loader e limpar o temporizador
                isLoader.value = false;
                clearInterval(checkDownloadWindow);
            }
        }, 1000);
    } else {
        const formInvoice = useForm({});

        formInvoice.get(route('invoice', {
            download: array['download'],
            provider_id: array['provider_id'],
            event_id: array['event_id'],
            message: array['message'],
            emails: array['emails'],
            copyMe: array['copyMe']
        }), {
            onFinish: () => {
                isLoader.value = false;
            },
        });
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
                table_id: current.id
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
                table_id: current.id
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
                table_id: current.id
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
                table_id: current.id
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
                table_id: current.id
            });
        }
    }, {});

    return groups;
}
</script>
<style>
.cursor-pointer {
    cursor: pointer;
}
</style>

<template>
    <AuthenticatedLayout>
        <Loader v-bind:show="isLoader"></Loader>

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
                                        <label for="start-date">Data de Início:</label>
                                        <datepicker v-model="formFilters.startDate" class="form-control" :locale="ptBR"
                                            inputFormat="dd/MM/yyyy" weekdayFormat="EEEEEE" />
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="end-date">Data de Fim:</label>
                                        <datepicker v-model="formFilters.endDate" class="form-control" :locale="ptBR"
                                            inputFormat="dd/MM/yyyy" weekdayFormat="EEEEEE" />
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
                                        <th scope="col">Código do Evento</th>
                                        <th scope="col">Data</th>
                                        <th scope="col">Data Final</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(event, index) in  events.data">
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
                                                <Link
                                                    v-if="$page.props.auth.permissions.some((p) => p.name === 'event_admin')"
                                                    class="btn-sm btn-info btn-icon-split mr-2"
                                                    :href="route('event-edit', { id: event.id })">
                                                <span class="icon text-white-50">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                                <span class="text">Editar</span>
                                                </Link>

                                                <Modal
                                                    v-if="$page.props.auth.permissions.some((p) => p.name === 'event_admin')"
                                                    :key="index" :modal-title="'Confirmar Exclusão de ' + event.name"
                                                    :ok-botton-callback="deleteEvent" :ok-botton-callback-param="event.id"
                                                    btn-class="btn-sm btn-danger btn-icon-split mr-2">
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

                                            </td>
                                        </tr>
                                        <template v-if="showEventDetails == event.id">
                                            <tr v-for="(prov, index) in  providersByEvent(event)">
                                                <th scope="row"></th>
                                                <td colspan="5">{{ prov.type }}:
                                                    {{ prov.name }} | {{ prov.city }}</td>
                                                <td>

                                                    <Modal
                                                        v-if="$page.props.auth.permissions.some((p) => p.name === 'status_level_1' || p.name === 'status_level_2')"
                                                        :key="index" modal-title="Follow UP" :content-big="true"
                                                        btn-class="btn-sm btn-success btn-icon-split mr-2">
                                                        <template v-slot:button>
                                                            <div
                                                                v-on:click="editStatus(event.id, prov.table, prov.table_id, $page.props.auth.permissions)">
                                                                <span class="icon text-white-50">
                                                                    <i class="fas fa-arrows-alt-v"></i>
                                                                </span>
                                                                <span class="text">Follow UP</span>
                                                            </div>
                                                        </template>
                                                        <template v-slot:content>
                                                            <div class="container">
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <div class="form-group">

                                                                            <label class="form-check-label"
                                                                                for="status_hotel">
                                                                                Status:
                                                                            </label>
                                                                            <select class="form-control s_hotel"
                                                                                v-model="formStatus.status_hotel">

                                                                                <option>.::Selecione::.</option>
                                                                                <option v-for="option in statusOptions"
                                                                                    :key="option[0]" :value="option[0]">
                                                                                    {{ option[1].label }}
                                                                                </option>
                                                                            </select>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label class="form-check-label"
                                                                                for="observation_hotel">
                                                                                Obs:
                                                                            </label>
                                                                            <textarea class="form-control"
                                                                                v-model="formStatus.observation_hotel"></textarea>
                                                                        </div>

                                                                    </div>

                                                                    <div class="col-6">
                                                                        <div class="row">
                                                                            <div class="col">
                                                                                <div class="form-group">
                                                                                    <div class="form-check">
                                                                                        <input class="form-check-input"
                                                                                            v-model="formStatus.notify"
                                                                                            type="checkbox"
                                                                                            id="autoSizingCheck-l">
                                                                                        <label class="form-check-label"
                                                                                            for="autoSizingCheck-l">
                                                                                            Avisar Por E-mail
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row" v-if="formStatus.notify">
                                                                            <div class="col-12">

                                                                                <div class="form-group">
                                                                                    <InputLabel value="Enviar para:" />
                                                                                    <TextInput type="text"
                                                                                        class="form-control"
                                                                                        v-model="formStatus.emailsLink" />
                                                                                </div>

                                                                                <div class="alert alert-warning "
                                                                                    role="alert">
                                                                                    <h4
                                                                                        class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                                                        Separe os e-mails com ; (ponto e
                                                                                        vírgula)
                                                                                        caso tenha mais de 1
                                                                                    </h4>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-12">
                                                                                <div class="form-group">
                                                                                    <div class="form-check">
                                                                                        <InputLabel value=" " />
                                                                                        <CKEditor
                                                                                            v-model:contentCode="formStatus.messageLink"
                                                                                            :height="150" />
                                                                                    </div>
                                                                                </div>

                                                                            </div>

                                                                            <div class="col-12">
                                                                                <div class="form-group">
                                                                                    <div class="form-check">
                                                                                        <input class="form-check-input"
                                                                                            v-model="formStatus.copyMeLink"
                                                                                            type="checkbox"
                                                                                            id="check-copyme-link">
                                                                                        <label class="form-check-label"
                                                                                            for="check-copyme-link">
                                                                                            Enviar cópia para mim
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>

                                                                    <div class="flex items-center justify-end mt-4 rigth">
                                                                        <button type="button"
                                                                            class="btn-sm btn-primary float-right m-1"
                                                                            v-on:click="saveStatus()" data-dismiss="modal">
                                                                            Tramitar
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <!-- Tabela de Histórico de Status -->
                                                                    <div class="col-12">
                                                                        <h2>Histórico de Status</h2>
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Data da Alteração</th>
                                                                                    <th>Status</th>
                                                                                    <th>Alterado Por</th>
                                                                                    <th>Observação</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <!-- Dados fictícios no histórico (substitua pelos dados reais) -->
                                                                                <tr v-for="(historyItem, index) in history"
                                                                                    :key="index">
                                                                                    <td>{{ new
                                                                                        Date(historyItem.created_at).toLocaleDateString()
                                                                                    }}</td>
                                                                                    <td>{{
                                                                                        getStatusLabel(historyItem.status)
                                                                                    }}</td>
                                                                                    <td>{{ historyItem.user.name }}</td>
                                                                                    <td>{{ historyItem.observation }}</td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </Modal>


                                                    <Modal :key="index" modal-title="Link para orçamento"
                                                        v-if="!prov.isTransport" :ok-botton-callback="createLink"
                                                        :ok-botton-callback-param="{ event_id: event.id, provider_id: prov.id, emails: emailsLink, download: !sendEmailLink, message: messageLink, copyMe: copyMeLink, attachment: attachmentLink, link: token, linkEmail: linkEmail }"
                                                        :ok-botton-label="sendEmailLink ? 'Enviar link por e-mail' : 'Baixar PDF'"
                                                        :btn-class="prov.sended_mail_link ? 'btn-sm btn-danger btn-icon-split mr-2' : 'btn-sm btn-secondary btn-icon-split mr-2'">

                                                        <template v-slot:button>
                                                            <div v-on:click="newObjcts(prov.token_budget, prov.email)">
                                                                <span class="icon text-white-50">
                                                                    <i class="fas fa-link"></i>
                                                                </span>
                                                                <span class="text">Criar link Orçamento</span>
                                                            </div>
                                                        </template>
                                                        <template v-slot:content>

                                                            <div class="alert alert-danger" role="alert"
                                                                v-if="prov.token_budget != null">
                                                                <h4
                                                                    class="alert-heading text-xs font-weight-bold text-primary  mb-1">
                                                                    Já foi criado<span v-if="prov.sended_mail_link"> e
                                                                        enviado</span> um link para preenchimento do
                                                                    orçamento<br>
                                                                    {{ route('budget', { token: prov.token_budget })
                                                                    }}<br>
                                                                    Você pode baixar o PDF ou enviar por e-mail<span
                                                                        v-if="prov.sended_mail_link">
                                                                        novamente</span>!
                                                                </h4>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input"
                                                                                v-model="sendEmailLink" type="checkbox"
                                                                                id="autoSizingCheck-l">
                                                                            <label class="form-check-label"
                                                                                for="autoSizingCheck-l">
                                                                                Enviar E-mail
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row" v-if="sendEmailLink">
                                                                <div class="col-12">

                                                                    <div class="form-group">
                                                                        <InputLabel value="Enviar para:" />
                                                                        <TextInput type="text" class="form-control"
                                                                            v-model="emailsLink" />
                                                                    </div>

                                                                    <div class="alert alert-warning " role="alert">
                                                                        <h4
                                                                            class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                                            Separe os e-mails com ; (ponto e vírgula)
                                                                            caso tenha mais de 1
                                                                        </h4>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <InputLabel value=" " />
                                                                            <CKEditor v-model:contentCode="messageLink"
                                                                                :height="150" />
                                                                        </div>
                                                                    </div>

                                                                </div>

                                                                <div class="col-12">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input"
                                                                                v-model="copyMeLink" type="checkbox"
                                                                                id="check-copyme-link">
                                                                            <label class="form-check-label"
                                                                                for="check-copyme-link">
                                                                                Enviar cópia para mim
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input"
                                                                                v-model="attachmentLink" type="checkbox"
                                                                                id="check-attachment-link">
                                                                            <label class="form-check-label"
                                                                                for="check-attachment-link">
                                                                                Enviar PDF junto com o e-mail
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>


                                                                <div class="col-12">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input"
                                                                                v-model="linkEmail" type="checkbox"
                                                                                id="check-email-link">
                                                                            <label class="form-check-label"
                                                                                for="check-email-link">
                                                                                Enviar link no e-mail
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </template>
                                                    </Modal>

                                                    <Modal :key="index"
                                                        v-if="prov && !prov.isTransport && prov.providerBudget && prov.providerBudget[0] && !prov.providerBudget[0].evaluated && $page.props.auth.permissions.some((p) => p.name === 'prove_budget_hotel')"
                                                        modal-title="Aprovar ou recusar orçamento do fornecedor"
                                                        :btn-blank="true" :btn-is-link="true" ok-botton-label="Avaliar"
                                                        :url="route('budget', { token: prov.token_budget, prove: true, user: $page.props.auth.user.id })"
                                                        btn-class="btn-sm btn-info btn-icon-split mr-2">

                                                        <template v-slot:button>
                                                            <div>
                                                                <span class="icon text-white-50">
                                                                    <i class="fas fa-comments-dollar"></i>
                                                                </span>
                                                                <span class="text">Avaliar Orçamento</span>
                                                            </div>
                                                        </template>

                                                        <template v-slot:content>

                                                            <div class="alert alert-info" role="alert">
                                                                <p>
                                                                    Clique no link, avalie os valores preenchidos e
                                                                    clique em aprovar ou recusar! <br> <br>
                                                                </p>
                                                            </div>

                                                        </template>
                                                    </Modal>

                                                    <Modal :key="index"
                                                        v-if="prov && !prov.isTransport && prov.providerBudget && prov.providerBudget[0] && prov.providerBudget[0].evaluated"
                                                        modal-title="Aprovação do orçamento do fornecedor" :btn-blank="true"
                                                        :btn-is-link="true" ok-botton-label="Avaliar"
                                                        :url="route('budget', { token: prov.token_budget, prove: true })"
                                                        btn-class="btn-sm btn-info btn-icon-split mr-2">

                                                        <template v-slot:button>
                                                            <div>
                                                                <span class="icon text-white-50">
                                                                    <i class="fas fa-comments-dollar"></i>
                                                                </span>
                                                                <span class="text">Ver</span>
                                                            </div>
                                                        </template>

                                                        <template v-slot:content>

                                                            <div class="row">
                                                                <div class="col-md-6 offset-md-3">
                                                                    <h1>Dados do aprovação</h1>
                                                                    <p><strong>Aprovado: </strong>
                                                                        <span :class="{
                                                                            'text-info': prov.providerBudget[0].approved, 'text- danger': prov.providerBudget.approved
                                                                        }
                                                                            ">
                                                                            {{ prov.providerBudget[0].approved ? 'Sim' :
                                                                                'Não' }}
                                                                        </span>
                                                                    </p>
                                                                    <p><strong>Usuário: </strong> {{
                                                                        prov.providerBudget[0].user.name }}</p>
                                                                    <p><strong>Data: </strong> {{ new
                                                                        Date(prov.providerBudget[0].approval_date).toLocaleDateString()
                                                                    }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </Modal>

                                                    <Modal modal-title="Envio de Proposta"
                                                        v-if="prov && prov.providerBudget && $page.props.auth.permissions.some((p) => p.name === 'event_admin')"
                                                        :ok-botton-callback="sendProposal"
                                                        :ok-botton-callback-param="{ event_id: event.id, provider_id: prov.id, emails: emails, download: !sendEmail, message: message, copyMe: copyMe }"
                                                        :ok-botton-label="!sendEmail ? 'Baixar PDF' : 'Enviar Proposta'"
                                                        :btn-class="prov.sended_mail ? 'btn-sm btn-danger btn-icon-split mr-2' : 'btn-sm btn-secondary btn-icon-split mr-2'">
                                                        <template v-slot:button>
                                                            <div @click="{
                                                                emails = event.customer != null ? event.customer.email : '';
                                                                sendEmail = true;
                                                                message = '';
                                                                copyMe = false;
                                                            }">
                                                                <span class="icon text-white-50">
                                                                    <i v-if="!prov.sended_mail" class="fas fa-file-pdf"></i>
                                                                    <i v-if="prov.sended_mail"
                                                                        class="fas fa-exclamation-triangle"></i>
                                                                </span>
                                                                <span class="text">Proposta Hotel</span>
                                                            </div>
                                                        </template>
                                                        <template v-slot:content>
                                                            <div class="row">
                                                                <div class="col">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input"
                                                                                v-model="sendEmail" type="checkbox"
                                                                                id="autoSizingCheck">
                                                                            <label class="form-check-label"
                                                                                for="autoSizingCheck">
                                                                                Enviar E-mail
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <div class="row" v-if="sendEmail">
                                                                <div class="col-12">

                                                                    <div class="form-group">
                                                                        <InputLabel value="Enviar para:" />
                                                                        <TextInput type="text" class="form-control"
                                                                            v-model="emails" />
                                                                    </div>

                                                                    <div class="alert alert-warning " role="alert">
                                                                        <h4
                                                                            class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                                            Separe os e-mails com ; (ponto e vírgula)
                                                                            caso tenha mais de 1
                                                                        </h4>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <InputLabel value=" " />
                                                                            <CKEditor v-model:contentCode="message"
                                                                                :height="150" />
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" v-model="copyMe"
                                                                                type="checkbox" id="check-copyme">
                                                                            <label class="form-check-label"
                                                                                for="check-copyme">
                                                                                Enviar cópia para mim
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="alert alert-danger " role="alert"
                                                                v-if="prov.sended_mail">
                                                                <h4
                                                                    class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                                    Já foi enviado um e-mail com essa proposta!
                                                                </h4>
                                                            </div>

                                                        </template>
                                                    </Modal>

                                                    <Modal modal-title="Faturamento"
                                                        v-if="$page.props.auth.permissions.some((p) => p.name === 'event_admin') && showInvoicement(event.event_status)"
                                                        :ok-botton-callback="sendInvoice"
                                                        :ok-botton-callback-param="{ event_id: event.id, emails: emailsInvoice, download: !sendEmailInvoice, provider_id: prov.id, message: messageInvoice, copyMe: copyMeInvoice }"
                                                        :ok-botton-label="!sendEmailInvoice ? 'Baixar PDF' : 'Enviar Faturamento'"
                                                        btn-class="btn-sm btn-secondary btn-icon-split ">
                                                        <template v-slot:button>
                                                            <div @click="{
                                                                emailsInvoice = '';
                                                                sendEmailInvoice = false;
                                                                messageInvoice = '';
                                                                copyMeInvoice = false;
                                                            }">
                                                                <span class="icon text-white-50">
                                                                    <i class="fas fa-file-pdf"></i>
                                                                </span>
                                                                <span class="text">Faturamento</span>
                                                            </div>
                                                        </template>
                                                        <template v-slot:content>
                                                            <div class="row">
                                                                <div class="col">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input"
                                                                                v-model="sendEmailInvoice" type="checkbox"
                                                                                id="autoSizingCheck">
                                                                            <label class="form-check-label"
                                                                                for="autoSizingCheck">
                                                                                Enviar E-mail
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row" v-if="sendEmailInvoice">
                                                                <div class="col-12">

                                                                    <div class="form-group">
                                                                        <InputLabel value="Enviar para:" />
                                                                        <TextInput type="text" class="form-control"
                                                                            v-model="emailsInvoice" />
                                                                    </div>

                                                                    <div class="alert alert-warning " role="alert">
                                                                        <h4
                                                                            class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                                            Separe os e-mails com ; (ponto e vírgula)
                                                                            caso tenha mais de 1
                                                                        </h4>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <InputLabel value=" " />
                                                                            <CKEditor v-model:contentCode="messageInvoice"
                                                                                :height="150" />
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input"
                                                                                v-model="copyMeInvoice" type="checkbox"
                                                                                id="check-copyme">
                                                                            <label class="form-check-label"
                                                                                for="check-copyme">
                                                                                Enviar cópia para mim
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </template>
                                                    </Modal>

                                                </td>
                                            </tr>
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
