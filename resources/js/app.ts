import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { vuetify } from "./vuetify";

createInertiaApp({
    progress: {
        color: "#07796a",
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            (import.meta as any).glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vuetify);

        app.mount(el);
    },
});
