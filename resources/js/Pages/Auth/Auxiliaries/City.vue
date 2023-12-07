<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import Loader from '@/Components/Loader.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { onMounted, ref } from 'vue';


const props = defineProps({
    cities: Array,
    ufs: Array
});

const cityInEdition = ref(0);

const form = useForm({
    id: 0,
    name: '',
    states: '',
    country: ''
});

const formDelete = useForm({
    id: 0
});


onMounted(() => {

    $('table').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json',
        },
    });

    $('#uf').select2({
        theme: "bootstrap4", language: "pt-Br"
    }).on('select2:select', (e) => {
        form.states = e.params.data.id;
    });
});

const isLoader = ref(false);

const deleteCity = (id) => {
    isLoader.value = true;
    formDelete.id = id;
    formDelete.delete(route('city-delete'), {
        onFinish: () => {
            isLoader.value = false;
            formDelete.reset();
        },
    });
};

const edit = (city) => {
    cityInEdition.value = city.id;
    form.name = city.name;
    if (city.country.toLowerCase() != 'brasil') {
        form.states = city.states;
        $('#uf').val(form.states).trigger('change');
    }
    form.country = city.country;
    form.id = city.id;

};

const submit = () => {
    form.post(route('city-save'), {
        onSuccess: () => {
            $('#uf').val().trigger('change');
            cityInEdition.value = 0;
            form.reset();
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Loader v-bind:show="isLoader"></Loader>

        <Head title="Cidades" />
        <template #header>
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Cidades</h1>
            </div>
        </template>
        <div class="row">
            <div class="col-lg-12">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4 py-3 border-left-primary">
                            <div class="card-body">

                                <form @submit.prevent="submit">

                                    <div class="row">

                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="number" value="País:" />
                                                <TextInput type="text" class="form-control number" v-model="form.country"
                                                    required autofocus autocomplete="number" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.country" />
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="customer_id" value="Estado:" />

                                                <select v-if="form.country.toLowerCase() == 'brasil'" class="form-control"
                                                    id="uf">
                                                    <option>.::Selecione::.</option>
                                                    <option v-for="(option, index) in ufs"
                                                        :selected="option.uf == form.states" :value="option.uf">
                                                        {{ option.name }}
                                                    </option>
                                                </select>
                                                <TextInput v-else type="text" class="form-control number"
                                                    v-model="form.states" autofocus autocomplete="number" />

                                                <InputError class="mt-2 text-danger" :message="form.errors.states" />
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="form-group">
                                                <InputLabel for="name" value="Cidade:" />
                                                <TextInput type="text" class="form-control" v-model="form.name" required
                                                    autofocus autocomplete="name" />
                                                <InputError class="mt-2 text-danger" :message="form.errors.name" />
                                            </div>
                                        </div>

                                    </div>
                                    <div class="flex items-center justify-end mt-4 rigth">
                                        <PrimaryButton css-class="btn btn-primary float-right m-1"
                                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            <span v-if="form.processing" class="spinner-border spinner-border-sm"
                                                role="status" aria-hidden="true"></span>
                                            Salvar
                                        </PrimaryButton>
                                        <PrimaryButton v-if="cityInEdition > 0" css-class="btn btn-info float-right m-1"
                                            v-on:click="form.reset(); cityInEdition = 0; $('#uf').val().trigger('change');"
                                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            Novo
                                        </PrimaryButton>
                                    </div>

                                </form>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-12">
                        <div class="card mb-4 py-3 border-left-secondary">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Cidade</th>
                                                <th scope="col">Estado</th>
                                                <th scope="col">País</th>
                                                <th scope="col">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(city, index) in cities"
                                                :class="{ 'table-info': cityInEdition == city.id }">
                                                <th scope="row">{{ city.id }}</th>
                                                <td>{{ city.name }}</td>
                                                <td>{{
                                                    city.states !== null
                                                    ? (
                                                        city.country.toLowerCase() === 'brasil'
                                                            ? (ufs.find((s) => s.uf === city.states)?.name || ' - ')
                                                            : city.states
                                                    )
                                                    : ' - '
                                                }}
                                                </td>
                                                <td>{{ city.country }}</td>
                                                <td>
                                                    <button class="btn btn-info btn-icon-split mr-2"
                                                        v-on:click="edit(city)">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                        <span class="text">Editar</span>
                                                    </button>

                                                    <Modal :key="index" :modal-title="'Confirmar Exclusão de ' + city.name"
                                                        :ok-botton-callback="deleteCity" :ok-botton-callback-param="city.id"
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
