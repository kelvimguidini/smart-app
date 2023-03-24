<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Loader from '@/Components/Loader.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import { onMounted, ref } from 'vue';


const props = defineProps({
    events: Array
});

const formDelete = useForm({
    id: 0
});

const isLoader = ref(false);

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

const providersByEvent = (event) => {

    var groups = [];

    event.event_hotels && event.event_hotels.map((current) => {
        if (!groups.some(g => g.id == current.hotel.id)) {
            groups.push({
                id: current.hotel.id,
                name: current.hotel.name,
                city: current.hotel.city,
            });
        }
    }, {});

    event.event_abs && event.event_abs.map((current) => {
        if (!groups.some(g => g.id == current.ab.id)) {
            groups.push({
                id: current.ab.id,
                name: current.ab.name,
                city: current.ab.city,
            });
        }
    }, {});

    event.event_halls && event.event_halls.map((current) => {
        if (!groups.some(g => g.id == current.hall.id)) {
            groups.push({
                id: current.hall.id,
                name: current.hall.name,
                city: current.hall.city,
            });
        }
    }, {});

    event.event_adds && event.event_adds.map((current) => {
        if (!groups.some(g => g.id == current.add.id)) {
            groups.push({
                id: current.add.id,
                name: current.add.name,
                city: current.add.city,
            });
        }
    }, {});

    return groups;
}
</script>

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
                                                    <td>{{ event.customer.name }}</td>
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

                                                        <Modal :key="index" v-if="false"
                                                            :modal-title="'Link para ser enviado ao contato do hotel para preencher o orçamento.'"
                                                            :ok-botton-callback="createLink"
                                                            :ok-botton-callback-param="{ event: event.id, provider: prov.id }"
                                                            ok-botton-label="Enviar link por email"
                                                            btn-class="btn btn-secondary btn-icon-split mr-2">
                                                            <template v-slot:button>
                                                                <span class="icon text-white-50">
                                                                    <i class="fas fa-link"></i>
                                                                </span>
                                                                <span class="text">Criar link Orçamento</span>
                                                            </template>
                                                            <template v-slot:content>

                                                                <!-- Earnings (Monthly) Card Example -->
                                                                <div class="col-xl-3 col-md-6 mb-4">
                                                                    <div class="card border-left-info shadow h-100 py-2">
                                                                        <div class="card-body">
                                                                            <div class="row no-gutters align-items-center">
                                                                                <div class="col mr-2">
                                                                                    <div
                                                                                        class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                                                        {{ link }}</div>
                                                                                    <div
                                                                                        class="row no-gutters align-items-center">
                                                                                        <div class="col-auto">
                                                                                            <div
                                                                                                class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                                                                                50%</div>
                                                                                        </div>
                                                                                        <div class="col">
                                                                                            <div
                                                                                                class="progress progress-sm mr-2">
                                                                                                <div class="progress-bar bg-info"
                                                                                                    role="progressbar"
                                                                                                    style="width: 50%"
                                                                                                    aria-valuenow="50"
                                                                                                    aria-valuemin="0"
                                                                                                    aria-valuemax="100">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-auto">
                                                                                    <i
                                                                                        class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                Esse link para ser enviado ao contato no hotel () para
                                                                preenchimento da proposta do
                                                                orçamento. Você também pode pedir para enviar para "" no
                                                                template padrão do sistema clicando no
                                                                botão abaixo.
                                                            </template>
                                                        </Modal>
                                                        <a target="blank"
                                                            v-if="$page.props.auth.permissions.some((p) => p.name === 'event_admin')"
                                                            class="btn btn-secondary btn-icon-split mr-2"
                                                            :href="route('proposal-hotel', { event_id: event.id, provider_id: prov.id })">
                                                            <span class="icon text-white-50">
                                                                <i class="fas fa-file-pdf"></i>
                                                            </span>
                                                            <span class="text">Proposta Hotel</span>
                                                        </a>

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
