import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, h } from 'vue';
import vuetify from './plugins/vuetify';

const appName = import.meta.env.VITE_APP_NAME || 'VicosaFood';

// Sem ZiggyVue de proposito -- o processo Node so recebe {component, props, url, version}
// (ver Inertia\Ssr\HttpGateway::dispatch no lado PHP), nunca o window.Ziggy que o
// @routes do Blade injeta numa requisicao normal. As paginas com <Head> renderizado aqui
// (Discover, Restaurants/Show) usam `usePage().props.appUrl` pra URL absoluta em vez de
// route() por isso. Qualquer outro uso de route() nessas paginas so roda em handler de
// clique/submit, nunca durante este render -- e paginas que dependem de route() no proprio
// template (autenticadas, atras de auth+robots.txt) simplesmente falham o SSR de forma
// silenciosa (HttpGateway cai pra CSR sozinho), sem tela de erro pro usuario.
createServer((page) =>
    createInertiaApp({
        page,
        title: (title) => `${title} - ${appName}`,
        render: renderToString,
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob('./Pages/**/*.vue'),
            ),
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(vuetify);
        },
    }),
);
