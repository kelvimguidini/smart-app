import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import basicSsl from '@vitejs/plugin-basic-ssl'

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
    // server: {
    //     // host: '0.0.0.0',
    //     hmr: true,
    //     watch: {
    //         usePolling: true,
    //     },
    //     proxy: {
    //         "/smart-app/public/": {
    //             target: "https://dev.eventos.com.br",
    //         }
    //     }
    // },
});
