import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { vuetify } from "./vuetify";
import dayjs from "dayjs";
import "dayjs/locale/en-gb";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";
import localizedFormat from "dayjs/plugin/localizedFormat";
import relativeTime from "dayjs/plugin/relativeTime";
import updateLocale from "dayjs/plugin/updateLocale";

dayjs.extend(localizedFormat);
dayjs.extend(relativeTime);

dayjs.extend(utc);
dayjs.extend(timezone);

dayjs.extend(updateLocale);
dayjs.locale("en-gb");
dayjs.updateLocale("en-gb", {
    relativeTime: {
        future: "in %s",
        past: "%s ago",
        s: "%d seconds",
        m: "a minute",
        mm: "%d minutes",
        h: "an hour",
        hh: "%d hours",
        d: "a day",
        dd: "%d days",
        M: "a month",
        MM: "%d months",
        y: "a year",
        yy: "%d years",
    },
});

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
