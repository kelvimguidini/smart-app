<script setup>
import ListHotelFull from '@/Components/ListHotelFull.vue';
import ListABFull from '@/Components/ListABFull.vue';
import ListHallFull from '@/Components/ListHallFull.vue';
import ListAddFull from '@/Components/ListAddFull.vue';
import ListTransportFull from '@/Components/ListTransportFull.vue';

import InputLabel from '@/Components/InputLabel.vue';
import { onMounted } from 'vue';

const props = defineProps({
    event: Object
});

onMounted(() => {

    const tabId = `#tabs-${props.event.id}`;
    if ($(tabId).hasClass('ui-tabs')) {
        $(tabId).tabs("destroy");
    }
    $(tabId).tabs({ active: 0 });
}); 
</script>

<style>
label {
    font-weight: bold !important;
}
</style>

<template>
    <div :id="'tabs-' + event.id">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" :href="'#basic-' + event.id">Basico</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{ 'disabled': event.event_hotels == null }"
                    :href="'#hotel-' + event.id">Hotel</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{ 'disabled': event.event_abs == null }"
                    :href="'#aandb-' + event.id">A&B</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{ 'disabled': event.event_halls == null }"
                    :href="'#hall-' + event.id">Salões</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{ 'disabled': event.event_adds == null }"
                    :href="'#additional-' + event.id">Adicionais</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{ 'disabled': event.event_transports == null }"
                    :href="'#transport-' + event.id">Transporte</a>
            </li>
        </ul>

        <!-- ABA EVENTO -->
        <div :id="'basic-' + event.id">
            <div class="card mb-4 py-3 border-left-primary">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <InputLabel for="name" value="Nome do Evento:" />
                                <div class="form-control-plaintext">{{ event?.name }}</div>
                            </div>
                            <div class="form-group">
                                <InputLabel for="code" value="Código do Zendesk:" />
                                <div class="form-control-plaintext">{{ event?.code }}</div>
                            </div>

                            <div class="border rounded p-3" v-if="event?.event_locals?.length > 0">
                                <template v-for="(country, index) in event?.event_locals" :key="index">
                                    <div class="form-group">
                                        <InputLabel :for="'country-' + index" value="País:" />
                                        <div class="form-control-plaintext">{{ country.pais }}</div>
                                    </div>
                                    <div class="form-group">
                                        <InputLabel :for="'event_city-' + index" value="Cidade:" />
                                        <div class="form-control-plaintext">{{ country.cidade }}</div>
                                    </div>
                                    <hr />
                                </template>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <InputLabel for="requester" value="Solicitante:" />
                                <div class="form-control-plaintext">{{ event?.requester }}</div>
                            </div>
                            <div class="form-group">
                                <InputLabel for="customer" value="Empresa:" />
                                <div class="form-control-plaintext">{{ event?.customer?.name }}</div>
                            </div>
                            <div class="form-group">
                                <InputLabel for="sector" value="Setor:" />
                                <div class="form-control-plaintext">{{ event?.sector }}</div>
                            </div>
                            <div class="form-group">
                                <InputLabel for="paxBase" value="Base de Pax:" />
                                <div class="form-control-plaintext">{{ event?.pax_base }}</div>
                            </div>
                            <div class="form-group">
                                <InputLabel for="cc" value="Centro de Custo:" />
                                <div class="form-control-plaintext">{{ event?.cost_center }}</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <InputLabel for="date" value="Data do Evento:" />
                                <div class="form-control-plaintext">{{ event?.date }}</div>
                            </div>
                            <div class="form-group">
                                <InputLabel for="date_final" value="Data do Evento Fim:" />
                                <div class="form-control-plaintext">{{ event?.date_final }}</div>
                            </div>
                            <div class="form-group">
                                <InputLabel for="CRD" value="CRD:" />
                                <div class="form-control-plaintext">{{ event?.crd?.number }}</div>
                            </div>
                            <div class="form-group">
                                <InputLabel for="hotel_operator" value="Operador - Hotel:" />
                                <div class="form-control-plaintext">{{ event?.hotel_operator?.name }}</div>
                            </div>
                            <div class="form-group">
                                <InputLabel for="land_operator" value="Operador - Terrestre:" />
                                <div class="form-control-plaintext">{{ event?.land_operator?.name }}</div>
                            </div>
                            <div class="form-group">
                                <InputLabel for="air_operator" value="Operador - Aéreo:" />
                                <div class="form-control-plaintext">{{ event?.air_operator?.name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- FIM ABA EVENTO -->

        <!-- ABA HOTEL -->
        <div :id="'hotel-' + event.id">
            <div>
                <ListHotelFull :event-hotels="event.event_hotels" :isViewMode="true">
                </ListHotelFull>
            </div>
        </div>
        <!-- FIM ABA HOTEL -->

        <!-- ABA A&B -->
        <div :id="'aandb-' + event.id">
            <div>
                <ListABFull :event-a-bs="event.event_abs" :isViewMode="true"></ListABFull>
            </div>
        </div>
        <!-- FIM ABA A&B -->

        <!-- ABA HALL -->
        <div :id="'hall-' + event.id">
            <div>
                <ListHallFull :event-halls="event.event_halls" :isViewMode="true"></ListHallFull>
            </div>
        </div>
        <!-- FIM ABA HALL -->

        <div :id="'additional-' + event.id">
            <div>
                <ListAddFull :event-adds="event.event_adds" :isViewMode="true"></ListAddFull>
            </div>
        </div>


        <!-- ABA TRANSPORTE -->
        <div :id="'transport-' + event.id">
            <div>
                <ListTransportFull :event-transports="event.event_transports" :isViewMode="true">
                </ListTransportFull>
            </div>
        </div>
        <!-- FIM ABA TRANSPORTE -->
    </div>
</template>