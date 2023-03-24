<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Loader from '@/Components/Loader.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import { onMounted, ref } from 'vue';


const props = defineProps({
    event: {
        type: Object,
        default: {}
    },
    provider: {
        type: Object,
        default: {}
    },
});





const isLoader = ref(false);

</script>

<template>
    <AuthenticatedLayout>
    <Loader v-bind:show="isLoader"></Loader>

    <Head title="Orçamento Hotel" />
    <template #header>
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Orçamento Hotel</h1>
        </div>
    </template>
    <div class="row">
        <div class="col-lg-12">

            <div class="row">

                <div class="col-lg-12">
                    <div class="card mb-4 py-3 border-left-secondary">
                        <div class="card-body">
                            {{ provider }}
                            <!-- <div class="table-responsive">
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
                                                                                <Link
                                                                                    v-if="$page.props.auth.permissions.some((p) => p.name === 'event_admin')"
                                                                                    class="btn btn-secondary btn-icon-split mr-2" :disabled="true"
                                                                                    :href="route('budget', { provider_id: prov.id, event_id: event.id })">
                                                                                <span class="icon text-white-50">
                                                                                    <i class="fas fa-donate"></i>
                                                                                </span>
                                                                                <span class="text">Orçamento</span>
                                                                                </Link>

                                                                            </td>
                                                                        </tr>
                                                                    </template>
                                                                </tbody>
                                                            </table>
                                                        </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
