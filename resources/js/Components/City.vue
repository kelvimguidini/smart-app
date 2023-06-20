<template>
    <div class="form-group">
        <InputLabel for="country" value="País:" />
        <input type="text" v-model="selectedCountry" @input="fetchCountries" class="form-control" placeholder="País">
        <ul>
            <li v-for="country in countries" :key="country.geonameId" @click="selectCountry(country)">
                {{ country.name }}
            </li>
        </ul>
    </div>
    <div class="form-group">
        <InputLabel for="country" value="Cidade:" />
        <input type="text" v-model="selectedCity" placeholder="Cidade">
    </div>
</template>

<script setup>
import axios from 'axios';
import { watch } from 'vue';

let selectedCountry = '';
let selectedCity = '';
let countries = [];

const fetchCountries = () => {
    if (selectedCountry.length >= 3) {
        axios.get(`http://api.geonames.org/searchJSON?q=${selectedCountry}&maxRows=10&username=kelvim`)
            .then(response => {
                countries = response.data.geonames;
            })
            .catch(error => {
                console.error(error);
            });
    } else {
        countries = [];
    }
};

const selectCountry = (country) => {
    selectedCountry = country.name;
    countries = [];
    // Aqui você pode fazer uma nova solicitação para buscar as cidades do país selecionado
    // e preencher o campo "Cidade" com as opções retornadas
};

watch(() => selectedCity, (newValue) => {
    emit('cidadeSelecionada', newValue);
});

watch(() => selectedCountry, (newValue) => {
    emit('paisSelecionado', newValue);
});

</script>
