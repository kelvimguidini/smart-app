<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Loader from '@/Components/Loader.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import { ref } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import CKEditor from '@/Components/CKEditor.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    events: Array
});

const formDelete = useForm({
    id: 0
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

const flash = ref(null);

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

const providersByEvent = (event) => {

    var groups = [];

    event.event_hotels && event.event_hotels.map((current) => {
        if (!groups.some(g => g.id == current.hotel.id)) {
            groups.push({
                id: current.hotel.id,
                name: current.hotel.name,
                city: current.hotel.city,
                email: current.hotel.email,
                sended_mail: current.sended_mail_link,
                token_budget: current.token_budget,
                providerBudget: current.provider_budget
            });
        }
    }, {});

    event.event_abs && event.event_abs.map((current) => {
        if (!groups.some(g => g.id == current.ab.id)) {
            groups.push({
                id: current.ab.id,
                name: current.ab.name,
                city: current.ab.city,
                email: current.ab.email,
                sended_mail: current.sended_mail_link,
                token_budget: current.token_budget,
                providerBudget: current.provider_budget
            });
        }
    }, {});

    event.event_halls && event.event_halls.map((current) => {
        if (!groups.some(g => g.id == current.hall.id)) {
            groups.push({
                id: current.hall.id,
                name: current.hall.name,
                city: current.hall.city,
                email: current.add.email,
                sended_mail: current.sended_mail_link,
                token_budget: current.token_budget,
                providerBudget: current.provider_budget
            });
        }
    }, {});

    event.event_adds && event.event_adds.map((current) => {
        if (!groups.some(g => g.id == current.add.id)) {
            groups.push({
                id: current.add.id,
                name: current.add.name,
                city: current.add.city,
                email: current.add.email,
                sended_mail: current.sended_mail_link,
                token_budget: current.token_budget,
                providerBudget: current.provider_budget
            });
        }
    }, {});

    return groups;
}
</script>

<template>
    <AuthenticatedLayout>
        <Loader v-bind:show="isLoader"></Loader>

        <FlashMessage v-if="flash != null" :show="flash != null" :message="flash.message" :type="flash.type"></FlashMessage>

        <Head title="Eventos" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Eventos</h1>
            </div>
        </template>
        <div class="row">
            <div class="col-lg-12">

                <div class="row">

                    <div class="col-lg-12">
                        <div class="card mb-4 py-3 border-left-secondary">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
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
                                            <template v-for="(event, index) in events">
                                                <tr>
                                                    <th scope="row">{{ event.id }}</th>
                                                    <td>{{ event.customer != null ? event.customer.name : ' - ' }}</td>
                                                    <td>{{ event.name }}</td>
                                                    <td>{{ event.code }}</td>
                                                    <td>{{ new Date(event.date).toLocaleDateString() }}</td>
                                                    <td>{{ new Date(event.date_final).toLocaleDateString() }}</td>
                                                    <td>

                                                        <Link
                                                            v-if="$page.props.auth.permissions.some((p) => p.name === 'event_admin')"
                                                            class="btn btn-info btn-icon-split mr-2"
                                                            :href="route('event-edit', { id: event.id })">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                        <span class="text">Editar</span>
                                                        </Link>

                                                        <Modal
                                                            v-if="$page.props.auth.permissions.some((p) => p.name === 'event_admin')"
                                                            :key="index"
                                                            :modal-title="'Confirmar Exclusão de ' + event.name"
                                                            :ok-botton-callback="deleteEvent"
                                                            :ok-botton-callback-param="event.id"
                                                            btn-class="btn btn-danger btn-icon-split">
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
                                                <tr v-for="(prov, index) in providersByEvent(event)">
                                                    <th scope="row"></th>
                                                    <td colspan="5">Hotel: {{ prov.name }} | {{ prov.city }}</td>

                                                    <td>

                                                        <Modal :key="index" modal-title="Link para orçamento"
                                                            :ok-botton-callback="createLink"
                                                            :ok-botton-callback-param="{ event_id: event.id, provider_id: prov.id, emails: emailsLink, download: !sendEmailLink, message: messageLink, copyMe: copyMeLink, attachment: attachmentLink, link: token, linkEmail: linkEmail }"
                                                            :ok-botton-label="sendEmailLink ? 'Enviar link por e-mail' : 'Baixar PDF'"
                                                            :btn-class="prov.sended_mail ? 'btn btn-danger btn-icon-split mr-2' : 'btn btn-secondary btn-icon-split mr-2'">

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
                                                                            v-if="prov.sended_mail_link"> novamente</span>!
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
                                                            v-if="prov.providerBudget[0] && !prov.providerBudget[0].evaluated && $page.props.auth.permissions.some((p) => p.name === 'prove_budget_hotel')"
                                                            modal-title="Aprovar ou recusar orçamento do fornecedor"
                                                            :btn-blank="true" :btn-is-link="true" ok-botton-label="Avaliar"
                                                            :url="route('budget', { token: prov.token_budget, prove: true, user: $page.props.auth.user.id })"
                                                            btn-class="btn btn-info btn-icon-split mr-2">

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
                                                            v-if="prov.providerBudget[0] && prov.providerBudget[0].evaluated"
                                                            modal-title="Aprovação do orçamento do fornecedor"
                                                            :btn-blank="true" :btn-is-link="true" ok-botton-label="Avaliar"
                                                            :url="route('budget', { token: prov.token_budget, prove: true })"
                                                            btn-class="btn btn-info btn-icon-split mr-2">

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
                                                                            }">
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
                                                            v-if="$page.props.auth.permissions.some((p) => p.name === 'event_admin')"
                                                            :ok-botton-callback="sendProposal"
                                                            :ok-botton-callback-param="{ event_id: event.id, provider_id: prov.id, emails: emails, download: !sendEmail, message: message, copyMe: copyMe }"
                                                            :ok-botton-label="!sendEmail ? 'Baixar PDF' : 'Enviar Proposta'"
                                                            :btn-class="prov.sended_mail ? 'btn btn-danger btn-icon-split mr-2' : 'btn btn-secondary btn-icon-split mr-2'">
                                                            <template v-slot:button>
                                                                <div @click="{
                                                                    emails = event.customer != null ? event.customer.email : '';
                                                                    sendEmail = true;
                                                                    message = '';
                                                                    copyMe = false;
                                                                }">
                                                                    <span class="icon text-white-50">
                                                                        <i v-if="!prov.sended_mail"
                                                                            class="fas fa-file-pdf"></i>
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
                                                                                <input class="form-check-input"
                                                                                    v-model="copyMe" type="checkbox"
                                                                                    id="check-copyme">
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

                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
