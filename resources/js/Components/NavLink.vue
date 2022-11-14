<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/inertia-vue3';

const props = defineProps(['href', 'active']);

const classes = computed(() => props.active
    ? 'text-blueGray-500'
    : ''
);
</script>

<template>
    <Link :href="href" :class="classes">
    <slot />
    </Link>
</template>
