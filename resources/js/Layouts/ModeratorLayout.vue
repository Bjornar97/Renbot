<script setup lang="ts">
import Messages from "@/Components/Shared/Messages.vue";
import { router } from "@inertiajs/core";
import { usePage } from "@inertiajs/vue3";
import {
    mdiArrowDecisionAuto,
    mdiHeartPulse,
    mdiMenu,
    mdiMessageReplyText,
    mdiScaleBalance,
    mdiTargetAccount,
} from "@mdi/js";
import route from "ziggy-js";
import { computed, ref } from "vue";
import { useDisplay } from "vuetify/lib/framework.mjs";

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

        return null;
    },
    set: (v) => {
        if (v === "commands") {
            return router.get(route("commands.index"));
        }

        if (v === "rules") {
            return router.get(route("rules.index"));
        }
    },
});
</script>

<template>
    <VApp>
        <VLayout>
            <Messages></Messages>

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

                <VChip pill class="mr-4" v-if="mdAndUp">
                    <VAvatar start>
                        <VImg :src="user.avatar" alt="Avatar" />
                    </VAvatar>

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
                        :prepend-icon="mdiHeartPulse"
                    ></VListItem>

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

                    <VListSubheader>Information</VListSubheader>
                    <VListItem
                        title="Rules"
                        :prepend-icon="mdiScaleBalance"
                        :active="route().current('rules.index')"
                        @click="goTo('rules.index')"
                    ></VListItem>
                </VList>
            </VNavigationDrawer>

            <VBottomNavigation
                color="primary"
                v-model="bottomNav"
                v-if="!mdAndUp"
            >
                <VBtn value="commands">
                    <VIcon :icon="mdiMessageReplyText"></VIcon>
                    Commands
                </VBtn>

                <VBtn value="rules">
                    <VIcon :icon="mdiScaleBalance"></VIcon>
                    Rules
                </VBtn>
            </VBottomNavigation>

            <VMain>
                <slot></slot>
            </VMain>
        </VLayout>
    </VApp>
</template>

<style>
.moderator-icon {
    object-fit: contain;
    width: 1.3rem;
}
</style>
