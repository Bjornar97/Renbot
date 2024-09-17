<script setup lang="ts">
import Messages from "@/Components/Shared/Messages.vue";
import { router } from "@inertiajs/core";
import { usePage } from "@inertiajs/vue3";
import {
    mdiAccountStar,
    mdiApplicationParenthesesOutline,
    mdiArrowDecisionAuto,
    mdiBell,
    mdiChevronDown,
    mdiClockOutline,
    mdiCog,
    mdiCommentQuoteOutline,
    mdiHeartPulse,
    mdiMenu,
    mdiMessageReplyText,
    mdiMonitorAccount,
    mdiRobotExcited,
    mdiScaleBalance,
    mdiTargetAccount,
} from "@mdi/js";
import { route } from "ziggy-js";
import { computed, ref } from "vue";
import { useDisplay } from "vuetify/lib/framework.mjs";
import NotificationSound from "../../audio/RobotForeignObjectDetected.mp3";
import { websocket } from "@/echo";
import { watch } from "vue";
import dayjs, { Dayjs } from "dayjs";

const logout = () => {
    router.post(route("logout"));
};

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
        if (currentRoute.value?.includes("commands.")) {
            return "commands";
        }

        if (currentRoute.value?.includes("rules.")) {
            return "rules";
        }

        if (currentRoute.value?.includes("bot")) {
            return "bot";
        }

        return null;
    },
    set: (v) => {
        if (v === "commands") {
            return router.get(route("commands.index"));
        }

        if (v === "rules") {
            return router.get(route("rules.index"));
        }

        if (v === "bot") {
            return router.get(route("bot"));
        }
    },
});

const audio = ref(new Audio(NotificationSound));
audio.value.volume = parseFloat(
    localStorage.getItem("notificationSystem.volume") ?? "0.5"
);
const volumeValue = ref(audio.value.volume);

const volume = computed({
    get: () => volumeValue.value,
    set: (v: number) => {
        audio.value.volume = v;
        volumeValue.value = v;
        localStorage.setItem("notificationSystem.volume", v.toString());
    },
});

let defaultNotificationEnabled =
    user?.value?.username?.toLowerCase() === "rendogtv";

const localStorageNotificationEnabled = localStorage.getItem(
    "notificationSystem.enabled"
);

const notificationEnabled = ref(
    localStorageNotificationEnabled === null
        ? defaultNotificationEnabled
        : localStorageNotificationEnabled === "true"
);

watch(
    () => notificationEnabled.value,
    (newValue) => {
        localStorage.setItem(
            "notificationSystem.enabled",
            newValue ? "true" : "false"
        );
    }
);

const makeNoise = () => {
    audio.value.play();
};

const lastNoise = ref(null as Dayjs | null);

websocket.private("App.MakeNoise").listen(".makeNoise", () => {
    if (lastNoise.value && dayjs().diff(lastNoise.value, "seconds") < 10) {
        return;
    }

    if (notificationEnabled.value) {
        lastNoise.value = dayjs();
        makeNoise();
    }
});
</script>

<template>
    <VApp>
        <VAppBar>
            <VAppBarTitle>
                <div class="d-flex align-center">
                    <VBtn
                        v-if="!mdAndUp"
                        variant="text"
                        :icon="mdiMenu"
                        @click="showMenu = !showMenu"
                    ></VBtn>

                    <img
                        class="mr-2 moderator-icon"
                        src="../../images/icons/moderator.png"
                        alt="Moderator Icon"
                    />
                    <p>Renbot - Moderators</p>
                </div>
            </VAppBarTitle>

            <VSpacer v-if="mdAndUp"></VSpacer>

            <VMenu :close-on-content-click="false" v-if="mdAndUp">
                <template #activator="{ props }">
                    <VBtn
                        :prepend-icon="mdiBell"
                        class="mr-2"
                        color="secondary"
                        variant="tonal"
                        v-bind="props"
                        :append-icon="mdiChevronDown"
                        >Alarm system</VBtn
                    >
                </template>

                <VList>
                    <VListItem>
                        <VSwitch
                            color="primary"
                            v-model="notificationEnabled"
                            label="Enabled"
                        ></VSwitch>
                    </VListItem>
                    <VListItem>
                        <div>
                            <VLabel>Volume</VLabel>
                            <VSlider
                                :max="1"
                                :min="0"
                                :step="0.01"
                                v-model="volume"
                            ></VSlider>
                        </div>
                    </VListItem>
                    <VListItem @click="makeNoise">Make test noise</VListItem>
                </VList>
            </VMenu>

            <VChip
                pill
                rounded
                class="mr-4"
                v-if="mdAndUp"
                :prepend-avatar="user.avatar"
            >
                {{ user.username }}
            </VChip>

            <VBtn color="primary" @click="logout">Logout</VBtn>
        </VAppBar>

        <VNavigationDrawer
            :model-value="showMenu || mdAndUp"
            :disable-resize-watcher="true"
        >
            <VList color="primary">
                <VListSubheader>Bot</VListSubheader>
                <VListItem
                    title="Health"
                    :active="route().current('bot')"
                    @click="goTo('bot')"
                    :prepend-icon="mdiHeartPulse"
                ></VListItem>
                <VListItem
                    title="Settings"
                    :active="route().current('bot.settings')"
                    @click="goTo('bot.settings')"
                    :prepend-icon="mdiCog"
                >
                </VListItem>

                <VListSubheader>Commands</VListSubheader>
                <VListItem
                    title="Regular commands"
                    :active="route().current('commands.index')"
                    @click="goTo('commands.index')"
                    :prepend-icon="mdiMessageReplyText"
                ></VListItem>
                <VListItem
                    title="Punishable commands"
                    :active="route().current('punishable-commands.index')"
                    @click="goTo('punishable-commands.index')"
                    :prepend-icon="mdiTargetAccount"
                ></VListItem>

                <VListItem
                    title="Special commands"
                    :active="route().current('special-commands.index')"
                    @click="goTo('special-commands.index')"
                    :prepend-icon="mdiArrowDecisionAuto"
                ></VListItem>

                <VListItem
                    title="Auto posts"
                    :active="route().current('auto-posts.index')"
                    @click="goTo('auto-posts.index')"
                    :prepend-icon="mdiClockOutline"
                ></VListItem>

                <VListSubheader>Tools</VListSubheader>
                <VListItem
                    title="Blocked Terms"
                    :prepend-icon="mdiApplicationParenthesesOutline"
                    :active="route().current('blocked-terms.index')"
                    @click="goTo('blocked-terms.index')"
                ></VListItem>

                <VListSubheader>Information</VListSubheader>
                <VListItem
                    title="Rules"
                    :prepend-icon="mdiScaleBalance"
                    :active="route().current('rules.index')"
                    @click="goTo('rules.index')"
                ></VListItem>
                <VListItem
                    title="Creators"
                    :prepend-icon="mdiAccountStar"
                    :active="route().current('creators.index')"
                    @click="goTo('creators.index')"
                ></VListItem>
                <VListItem
                    title="Stream days"
                    :prepend-icon="mdiMonitorAccount"
                    :active="route().current('streamdays.index')"
                    @click="goTo('streamdays.index')"
                ></VListItem>
                <VListItem
                    title="Quotes"
                    :prepend-icon="mdiCommentQuoteOutline"
                    :active="route().current('quotes.index')"
                    @click="goTo('quotes.index')"
                ></VListItem>
            </VList>
        </VNavigationDrawer>

        <VBottomNavigation
            color="primary"
            v-model="bottomNav"
            v-if="!mdAndUp"
            class="bottom-nav"
        >
            <VBtn value="bot">
                <VIcon :icon="mdiRobotExcited"></VIcon>
                Bot
            </VBtn>

            <VBtn value="commands">
                <VIcon :icon="mdiMessageReplyText"></VIcon>
                Commands
            </VBtn>

            <VBtn value="rules">
                <VIcon :icon="mdiScaleBalance"></VIcon>
                Rules
            </VBtn>
        </VBottomNavigation>

        <VMain class="app-main">
            <Messages class="global-messages ma-4"></Messages>
            <slot></slot>
        </VMain>
    </VApp>
</template>

<style>
.moderator-icon {
    object-fit: contain;
    width: 1.3rem;
}

.bottom-nav {
    position: fixed !important;
    bottom: 0;
    left: 0;
    right: 0;
}

.app-main {
    margin-bottom: 15rem;
}
</style>
