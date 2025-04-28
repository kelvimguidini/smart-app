<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';


const flashRef = ref(null);

const { flash } = usePage().props.value;

onMounted(() => {
    if (flash?.message) {
        flashRef.value?.show(flash);
    }
});

</script>
<style>
@tailwind base;
@tailwind component;
@tailwind utilities;
</style>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <Link href="/">
            <ApplicationLogo class="w-20 h-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <!-- FlashMessage Component -->
            <!-- <FlashMessage :props="$page.props" :show="showMessage" :message="message" :type="type"
                @close="showMessage = false" /> -->
            <FlashMessage ref="flashRef" :dissmiss="true" />

            <!-- Conteúdo da página -->
            <slot />
        </div>
    </div>
</template>
