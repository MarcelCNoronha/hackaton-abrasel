import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import vuetify from 'vite-plugin-vuetify';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            ssr: 'resources/js/ssr.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify({ autoImport: true }),
    ],
    // Empacota todas as deps no bundle SSR (vue, @vue/server-renderer, @inertiajs/vue3,
    // vuetify, ...) em vez de deixar como `require()` externo -- assim o container que roda
    // `node bootstrap/ssr/ssr.js` em produção so precisa desse arquivo, sem node_modules.
    ssr: {
        noExternal: true,
    },
});
