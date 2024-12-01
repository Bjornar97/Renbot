<script setup lang="ts">
import Messages from "@/Components/Shared/Messages.vue";
import { router } from "@inertiajs/core";
import { Link, usePage } from "@inertiajs/vue3";
import { mdiCalendarStar, mdiHome, mdiScaleBalance } from "@mdi/js";
import { route } from "ziggy-js";
import { computed, ref } from "vue";
import { useDisplay } from "vuetify/lib/framework.mjs";

const page = usePage();

const user = computed(() => {
    return (page.props as any)?.user;
});

const { mdAndUp } = useDisplay();

const showMenu = ref(false);

const goTo = (routeName: string) => {
    if (!mdAndUp.value) {
        showMenu.value = false;
    }

    router.get(route(routeName));
};

const currentRoute = ref(route().current());

router.on("navigate", () => {
    currentRoute.value = route().current();
});

const bottomNav = computed({
    get: () => {
        if (currentRoute.value?.includes("home")) {
            return "home";
        }

        if (currentRoute.value?.includes("rules")) {
            return "rules";
        }

        if (currentRoute.value?.includes("events")) {
            return "events";
        }

        if (currentRoute.value?.includes("quotes")) {
            return "quotes";
        }

        return null;
    },
    set: (v) => {
        if (v === "home") {
            return router.get(route("home"));
        }

        if (v === "rules") {
            return router.get(route("rules"));
        }

        if (v === "events") {
            return router.get(route("events.index"));
        }

        if (v === "quotes") {
            return router.get(route("quotes.index"));
        }
    },
});

const moderatorIcon = new URL(
    "../../images/icons/moderator.png",
    import.meta.url
).href;
</script>

<template>
    <VApp>
        <VAppBar>
            <div class="d-flex align-center ml-4">
                <img
                    class="mx-2 mx-md-0 rendog-icon"
                    src="../../images/icons/rendog.png"
                    alt="Rendog Icon"
                />

                <p class="ml-2">Renbot</p>

                <VDivider v-if="mdAndUp" class="ml-8 mr-4" vertical></VDivider>

                <div class="d-flex ga-2" v-if="mdAndUp">
                    <Link class="link" :href="route('home')">
                        <VBtn
                            :active="route().current('home')"
                            :color="
                                route().current('home') ? 'primary' : undefined
                            "
                            :prepend-icon="mdiHome"
                            >Home</VBtn
                        >
                    </Link>

                    <Link class="link" :href="route('rules')">
                        <VBtn
                            :active="route().current('rules')"
                            :color="
                                route().current('rules') ? 'primary' : undefined
                            "
                            :prepend-icon="mdiScaleBalance"
                            >Rules</VBtn
                        >
                    </Link>

                    <Link class="link" :href="route('events.index')">
                        <VBtn
                            :active="route().current('events.index')"
                            :color="
                                route().current('events.index')
                                    ? 'primary'
                                    : undefined
                            "
                            :prepend-icon="mdiCalendarStar"
                            >Events</VBtn
                        >
                    </Link>

                    <!-- <Link class="link" :href="route('quotes.index')">
                        <VBtn
                            :active="route().current('quotes.index')"
                            :color="
                                route().current('quotes.index')
                                    ? 'primary'
                                    : undefined
                            "
                            :prepend-icon="mdiCommentQuoteOutline"
                            >Quotes</VBtn
                        >
                    </Link> -->
                </div>
            </div>

            <VSpacer></VSpacer>

            <Link :href="route('commands.index')" class="link">
                <VBtn color="green-lighten-2">
                    <template #prepend>
                        <VAvatar size="1rem" :image="moderatorIcon"></VAvatar>
                    </template>
                    Moderators</VBtn
                >
            </Link>
        </VAppBar>

        <VBottomNavigation
            color="primary"
            v-model="bottomNav"
            v-if="!mdAndUp"
            class="bottom-nav"
        >
            <VBtn value="home">
                <VIcon :icon="mdiHome"></VIcon>
                Home
            </VBtn>

            <VBtn value="rules">
                <VIcon :icon="mdiScaleBalance"></VIcon>
                Rules
            </VBtn>

            <VBtn value="events">
                <VIcon :icon="mdiCalendarStar"></VIcon>
                Events
            </VBtn>

            <!-- <VBtn value="quotes">
                <VIcon :icon="mdiCommentQuoteOutline"></VIcon>
                Quotes
            </VBtn> -->
        </VBottomNavigation>

        <VMain class="app-main">
            <Messages class="global-messages"></Messages>
            <slot></slot>
        </VMain>
    </VApp>
</template>

<style scoped>
.rendog-icon {
    object-fit: contain;
    width: 1.3rem;
}

.bottom-nav {
    position: fixed !important;
    bottom: 0;
    left: 0;
    right: 0;
}

.link {
    text-decoration: none;
    color: rgb(var(--v-theme-on-surface));
}
</style>
