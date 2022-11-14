
<script setup>
const imageUrl = new URL('/resources/images/logo.png', import.meta.url).href;
</script>

<template>
    <img :src="imageUrl">
</template>
