<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-red-600">Erro</h1>
            <p class="mt-4 text-lg text-gray-700">{{ $page.props.error }}</p>
            <Link href="/" class="mt-6 inline-block text-blue-500 underline">Voltar para a página inicial</Link>
        </div>
    </div>
</template>