import "vuetify/styles"; // Global CSS has to be imported
import { createVuetify, type ThemeDefinition } from "vuetify";
import { aliases, mdi } from "vuetify/iconsets/mdi-svg";

const renbotTheme: ThemeDefinition = {
    dark: true,
    colors: {
        primary: "#07796a",
        "primary-darken-1": "#055e53",
        "primary-darken-2": "#064840",
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
    },
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
            // primary: mdiInformation,
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
