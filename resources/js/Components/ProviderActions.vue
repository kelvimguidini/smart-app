<template>

    <Modal v-if="$page.props.auth.permissions.some((p) => p.name === 'status_level_1' || p.name === 'status_level_2')"
        :key="index" modal-title="Follow UP" :content-big="true" btn-class="btn-sm btn-success btn-icon-split mr-2">
        <template v-slot:button>
            <div v-on:click="editStatus(event.id, prov.table, prov.table_id, $page.props.auth.permissions);">
                <span class="icon text-white-50">
                    <i class="fas fa-arrows-alt-v"></i>
                </span>
                <span class="text">Follow UP</span>
            </div>
        </template>
        <template v-slot:content>
            <div class="container">


                <div class="card mb-4 py-3 border-left-primary">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">

                                    <label class="form-check-label" for="status_hotel">
                                        Status:
                                    </label>
                                    <select class="form-control s_hotel" v-model="formStatus.status_hotel"
                                        required="required">

                                        <option value="">.::Selecione::.</option>
                                        <option v-for="option in statusOptions" :key="option[0]" :value="option[0]">
                                            {{ option[1].label }}
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-check-label" for="observation_hotel">
                                        Obs:
                                    </label>
                                    <textarea class="form-control" v-model="formStatus.observation_hotel"></textarea>
                                </div>

                            </div>

                            <div class="col-6">
                                <div class="card mb-4 py-3 border-left-success">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <div class="form-group">
                                                    <div class="form-check">
                                                        <input class="form-check-input" v-model="formStatus.notify"
                                                            type="checkbox" id="autoSizingCheck-l">
                                                        <label class="form-check-label" for="autoSizingCheck-l">
                                                            Avisar Por
                                                            E-mail
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" v-if="formStatus.notify">
                                            <div class="col-12">

                                                <div class="form-group">
                                                    <InputLabel value="Enviar para:" />
                                                    <TextInput type="text" class="form-control"
                                                        v-model="formStatus.emailsLink" />
                                                </div>

                                                <div class="alert alert-warning " role="alert">
                                                    <h4
                                                        class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                        Separe os e-mails
                                                        com ;
                                                        (ponto e
                                                        vírgula)
                                                    </h4>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <div class="form-check">
                                                        <InputLabel value=" " />
                                                        <CKEditor v-model:contentCode="formStatus.messageLink"
                                                            :height="150">

                                                        </CKEditor>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <div class="form-check">
                                                        <input class="form-check-input" v-model="formStatus.copyMeLink"
                                                            type="checkbox" id="check-copyme-link">
                                                        <label class="form-check-label" for="check-copyme-link">
                                                            Enviar cópia
                                                            para mim
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-end mt-4 rigth">
                                                <button type="button" class="btn-sm btn-primary float-right m-1"
                                                    v-on:click="sendEmailNoChangeStatus(event, prov)"
                                                    data-dismiss="modal">
                                                    Enviar e-mail sem
                                                    tramitar
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="flex items-center justify-end mt-4 rigth">
                                <button type="button" class="btn-sm btn-primary float-right m-1"
                                    v-on:click="saveStatus(event, prov)" data-dismiss="modal">
                                    Tramitar
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="row" v-if="$page.props.auth.permissions.some((p) => p.name === 'status_historico')">
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
                                <tr v-for="(historyItem, index) in history" :key="index">
                                    <td>{{ new
                                        Date(historyItem.created_at).toLocaleDateString()
                                    }}</td>
                                    <td>{{
                                        getStatusLabel(historyItem.status)
                                    }}</td>
                                    <td>{{ historyItem.user?.name }}</td>
                                    <td>{{ historyItem.observation }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </Modal>


    <Modal :key="index" modal-title="Link para orçamento" v-if="!prov.isTransport" :ok-botton-callback="createLink"
        :ok-botton-callback-param="{ event_id: event.id, provider_id: prov.id, emails: emailsLink, download: !sendEmailLink, message: messageLink, copyMe: copyMeLink, attachment: attachmentLink, link: token, linkEmail: linkEmail, type: prov.table }"
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

            <div class="alert alert-danger" role="alert" v-if="prov.token_budget != null">
                <h4 class="alert-heading text-xs font-weight-bold text-primary  mb-1">
                    Já foi criado<span v-if="prov.sended_mail_link"> e
                        enviado</span> um link para preenchimento do
                    orçamento<br>
                    {{ route('budget', { token: prov.token_budget })
                    }}<br>
                    Você pode baixar o PDF ou enviar por e-mail<span v-if="prov.sended_mail_link">
                        novamente</span>!
                </h4>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" v-model="sendEmailLink" type="checkbox"
                                id="autoSizingCheck-l">
                            <label class="form-check-label" for="autoSizingCheck-l">
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
                        <TextInput type="text" class="form-control" v-model="emailsLink" />
                    </div>

                    <div class="alert alert-warning " role="alert">
                        <h4 class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Separe os e-mails com ; (ponto e vírgula)
                        </h4>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <div class="form-check">
                            <InputLabel value=" " />
                            <CKEditor v-model:contentCode="messageLink" :height="150" />
                        </div>
                    </div>

                </div>

                <div class="col-12">
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" v-model="copyMeLink" type="checkbox"
                                id="check-copyme-link-10">
                            <label class="form-check-label" for="check-copyme-link-10">
                                Enviar cópia para mim
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" v-model="attachmentLink" type="checkbox"
                                id="check-attachment-link">
                            <label class="form-check-label" for="check-attachment-link">
                                Enviar PDF junto com o e-mail
                            </label>
                        </div>
                    </div>
                </div>


                <div class="col-12">
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" v-model="linkEmail" type="checkbox" id="check-email-link">
                            <label class="form-check-label" for="check-email-link">
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
        modal-title="Aprovar ou recusar orçamento do fornecedor" :btn-blank="true" :btn-is-link="true"
        ok-botton-label="Avaliar"
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
        modal-title="Aprovação do orçamento do fornecedor" :btn-blank="true" :btn-is-link="true"
        ok-botton-label="Avaliar" :url="route('budget', { token: prov.token_budget, prove: true })"
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
                        prov.providerBudget[0].user?.name }}</p>
                    <p><strong>Data: </strong> {{ new
                        Date(prov.providerBudget[0].approval_date).toLocaleDateString()
                    }}
                    </p>
                </div>
            </div>
        </template>
    </Modal>

    <Modal modal-title="Envio de Proposta"
        v-if="prov && prov.providerBudget && $page.props.auth.permissions.some((p) => p.name === 'event_admin') && (prov.status == 'dating_with_customer' || prov.status == 'approved_by_manager' || prov.status == 'sent_to_customer')"
        :ok-botton-callback="sendProposal"
        :ok-botton-callback-param="{ event_id: event.id, provider_id: prov.id, emails: emails, download: !sendEmail, message: message, copyMe: copyMe, type: prov.table }"
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
                    <i v-if="prov.sended_mail" class="fas fa-exclamation-triangle"></i>
                </span>
                <span class="text">Proposta</span>
            </div>
        </template>
        <template v-slot:content>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" v-model="sendEmail" type="checkbox" id="autoSizingCheck">
                            <label class="form-check-label" for="autoSizingCheck">
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
                        <TextInput type="text" class="form-control" v-model="emails" />
                    </div>

                    <div class="alert alert-warning " role="alert">
                        <h4 class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Separe os e-mails com ; (ponto e vírgula)
                        </h4>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <div class="form-check">
                            <InputLabel value=" " />
                            <CKEditor v-model:contentCode="message" :height="150" />
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" v-model="copyMe" type="checkbox" id="check-copyme">
                            <label class="form-check-label" for="check-copyme">
                                Enviar cópia para mim
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-danger " role="alert" v-if="prov.sended_mail">
                <h4 class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                    Já foi enviado um e-mail com essa proposta!
                </h4>
            </div>

        </template>
    </Modal>

    <Modal modal-title="Faturamento"
        v-if="$page.props.auth.permissions.some((p) => p.name === 'event_admin') && prov.status == 'dating_with_customer'"
        :ok-botton-callback="sendInvoice"
        :ok-botton-callback-param="{ event_id: event.id, emails: emailsInvoice, download: !sendEmailInvoice, provider_id: prov.id, message: messageInvoice, copyMe: copyMeInvoice, type: prov.table }"
        :ok-botton-label="!sendEmailInvoice ? 'Baixar PDF' : 'Enviar Faturamento'"
        btn-class="btn-sm btn-secondary btn-icon-split  mr-2">
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
                            <input class="form-check-input" v-model="sendEmailInvoice" type="checkbox"
                                id="autoSizingCheck-fat">
                            <label class="form-check-label" for="autoSizingCheck-fat">
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
                        <TextInput type="text" class="form-control" v-model="emailsInvoice" />
                    </div>

                    <div class="alert alert-warning " role="alert">
                        <h4 class="alert-heading text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Separe os e-mails com ; (ponto e vírgula)
                        </h4>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <div class="form-check">
                            <InputLabel value=" " />
                            <CKEditor v-model:contentCode="messageInvoice" :height="150" />
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" v-model="copyMeInvoice" type="checkbox" id="check-copyme">
                            <label class="form-check-label" for="check-copyme">
                                Enviar cópia para mim
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </template>
    </Modal>

    <Loader v-bind:show="isLoader" />

</template>

<script setup>
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import CKEditor from '@/Components/CKEditor.vue';
import { ref } from 'vue';
import axios from 'axios';
import { useForm } from '@inertiajs/inertia-vue3';
import { v4 as uuidv4 } from 'uuid';
import Loader from './Loader.vue';

const props = defineProps({
    event: Object,
    prov: Object,
    index: Number,
    getStatusLabel: Function,
    allStatus: Object,
});

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


const formStatus = useForm({
    table: '',
    table_id: 0,
    status_hotel: '',
    observation_hotel: '',
    notify: false,
    emailsLink: '',
    messageLink: ' ',
    copyMeLink: false
});


const editStatus = async (event_id, table, table_id, permissions) => {
    try {
        isLoader.value = true;
        const response = await axios.get(route('status-history', { table, table_id }));
        var currentStatus = response.data[0];
        var currentStatusInfo = props.allStatus[currentStatus.status] || {};
        var allowedFlows = currentStatusInfo.flow || [];

        statusOptions.value = Object.entries(props.allStatus).filter(([key, status]) =>
            allowedFlows.includes(key) &&
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
    } finally {
        isLoader.value = false;
    }
};

const saveStatus = (event, prov) => {
    isLoader.value = true;
    formStatus.messageLink += `<br><br>
    <p><b>Evento:</b> <span style="color: #333;">${event.name}</span></p>
    <p><b>Código:</b> <span style="color: #333;">${event.code}</span></p>
    <p><b>CRD:</b> <span style="color: #333;">${event.crd != null ? event.crd.name : ""}</span></p>
    <p><b>CC:</b> <span style="color: #333;">${event.cost_center}</span></p>
    <p><b>Solicitante:</b> <span style="color: #333;">${event.requester}</span></p>
    <p><b>Base de pax:</b> <span style="color: #333;">${event.pax_base}</span></p>
    <p><b>Fornecedor:</b> <span style="color: #333;">${prov.name}</span></p>
  `;

    if (!formStatus.status_hotel || formStatus.status_hotel === '.::Selecione::.') {
        alert("Por favor, selecione um status de hotel válido.");
        isLoader.value = false;
    } else {
        formStatus.post(route('event-status-save'), {
            onFinish: () => {
                isLoader.value = false;
                formStatus.reset();
            },
        });
    }
};

const sendEmailNoChangeStatus = (event, prov) => {
    isLoader.value = true;
    formStatus.messageLink += `<br><br>
    <p><b>Evento:</b> <span style="color: #333;">${event.name}</span></p>
    <p><b>Código:</b> <span style="color: #333;">${event.code}</span></p>
    <p><b>CRD:</b> <span style="color: #333;">${event.crd != null ? event.crd.name : ""}</span></p>
    <p><b>CC:</b> <span style="color: #333;">${event.cost_center}</span></p>
    <p><b>Solicitante:</b> <span style="color: #333;">${event.requester}</span></p>
    <p><b>Base de pax:</b> <span style="color: #333;">${event.pax_base}</span></p>
    <p><b>Fornecedor:</b> <span style="color: #333;">${prov.name}</span></p>
  `;
    formStatus.post(route('event-status-send-email'), {
        onFinish: () => {
            isLoader.value = false;
            formStatus.reset();
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
            link: array['link'],
            type: array['type'],
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
        const formLink = useForm({
            download: array['download'],
            provider_id: array['provider_id'],
            event_id: array['event_id'],
            message: array['message'] == '' ? '!' : array['message'],
            emails: array['emails'],
            copyMe: array['copyMe'],
            attachment: array['attachment'],
            link: array['link'],
            linkEmail: array['linkEmail'],
            type: array['type'],
        });

        formLink.post(route('create-link-email'), {
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
            type: array['type'],
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
            download: array['download'],
            provider_id: array['provider_id'],
            event_id: array['event_id'],
            message: array['message'],
            emails: array['emails'],
            copyMe: array['copyMe'],
            type: array['type'],
        });

        formProposal.post(route('proposal-hotel-email'), {
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
            type: array['type'],
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
        const formInvoice = useForm({
            download: array['download'],
            provider_id: array['provider_id'],
            event_id: array['event_id'],
            message: array['message'],
            emails: array['emails'],
            copyMe: array['copyMe'],
            type: array['type'],
        });

        formInvoice.post(route('invoice-email'), {
            onFinish: () => {
                isLoader.value = false;
            },
        });
    }
};

const flash = ref(null);
const history = ref([]);
const statusOptions = ref({});
</script>