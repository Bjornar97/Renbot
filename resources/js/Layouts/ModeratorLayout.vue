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

const { smAndUp } = useDisplay();

const showMenu = ref(false);

const goTo = (routeName: string) => {
    router.get(route(routeName));
};
</script>

<template>
    <VApp>
        <VLayout>
            <Messages></Messages>

            <VAppBar>
                <VAppBarTitle>
                    <div class="d-flex align-center">
                        <VBtn
                            v-if="!smAndUp"
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

                <VSpacer v-if="smAndUp"></VSpacer>

                <VChip pill class="mr-4" v-if="smAndUp">
                    <VAvatar start>
                        <VImg :src="user.avatar" alt="Avatar" />
                    </VAvatar>

                    {{ user.username }}
                </VChip>

                <VBtn color="primary-lighten-2" @click="logout">Logout</VBtn>
            </VAppBar>

            <VNavigationDrawer :model-value="showMenu || smAndUp">
                <VList color="primary-lighten-2">
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
                        :prepend-icon="mdiTargetAccount"
                    ></VListItem>

                    <VListItem
                        title="Special commands"
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
