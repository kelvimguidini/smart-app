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
        host: 'https://smart4bts.com.br',
        // host: 'local.api-smart.com',
        watch: {
            // Recarrega a página sempre que um arquivo for alterado
            usePolling: true,
            interval: 100,

            onWatched: (event, path) => {
                server.ws.send({ type: 'full-reload' })
            },
        },
        cors: true,
        hmr: true,
        proxy: {
            "/": {
                // target: "local.api-smart.com",
                target: "https://smart4bts.com.br",
            }
        },
        https: true,
    },
});
