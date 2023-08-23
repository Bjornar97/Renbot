import "vuetify/styles"; // Global CSS has to be imported
import { createVuetify, type ThemeDefinition } from "vuetify";
import { aliases, mdi } from "vuetify/iconsets/mdi-svg";
import { mdiInformation } from "@mdi/js";
import { md3 } from "vuetify/blueprints";

const renbotTheme: ThemeDefinition = {
    dark: true,
    colors: {
        primary: "#CF6679",
        "primary-darken-1": "#e04770",
        "primary-darken-2": "#b52a4f",
        secondary: "#f36d29",
        "secondary-darken-1": "#d75e20",
    },
};

export const vuetify = createVuetify({
    defaults: {
        VTextField: {
            variant: "outlined",
        },
        VTextarea: {
            variant: "outlined",
        },
        VAutocomplete: {
            variant: "outlined",
        },
        VSwitch: {
            inset: true,
        },
    },
    blueprint: md3,
    theme: {
        defaultTheme: "renbotTheme",
        variations: {
            colors: ["primary", "secondary"],
            lighten: 1,
            darken: 2,
        },
        themes: { renbotTheme },
    },
    icons: {
        defaultSet: "mdi",
        aliases: {
            ...aliases,
            primary: mdiInformation,
        },
        sets: {
            mdi,
        },
    },
    display: {
        mobileBreakpoint: "sm",
        thresholds: {
            xs: 0,
            sm: 600,
            md: 960,
            lg: 1264,
            xl: 1904,
        },
    },
    locale: {
        locale: "en",
    },
});
