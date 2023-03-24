import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import basicSsl from '@vitejs/plugin-basic-ssl';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            ssr: 'resources/js/ssr.js',
            refresh: true,
        }),
        basicSsl(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        })
    ],
    ssr: {
        noExternal: ['@inertiajs/server'],
    },
    server: {
        watch: {
            // Recarrega a página sempre que um arquivo for alterado
            usePolling: true,
            interval: 100,
            // Função de callback que será executada sempre que um arquivo for alterado
            // Essa função pode ser usada para realizar uma ação específica
            // Neste caso, estamos recarregando a página
            onWatched: (event, path) => {
                server.ws.send({ type: 'full-reload' })
            },
        },
        //cors: true,
        // hmr: true,
        // proxy: {
        //     "/": {
        //         target: "https://smart4bts.com.br",
        //     }
        // },
        //https: true,
    },
});
