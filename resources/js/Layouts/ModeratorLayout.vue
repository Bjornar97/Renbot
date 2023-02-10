<script setup lang="ts">
import Messages from "@/Components/Shared/Messages.vue";
import { router } from "@inertiajs/core";
import { usePage } from "@inertiajs/vue3";
import {
    mdiArrowDecisionAuto,
    mdiHeartPulse,
    mdiMessageReplyText,
    mdiScaleBalance,
    mdiTargetAccount,
} from "@mdi/js";
import route from "ziggy-js";
import { computed } from "vue";

const logout = () => {
    router.post(route("logout"));
};

const page = usePage();

const user = computed(() => {
    return (page.props as any)?.user;
});
</script>

<template>
    <VApp>
        <VLayout>
            <Messages></Messages>

            <VAppBar>
                <VAppBarTitle>
                    <div class="d-flex items-center">
                        <img
                            class="mr-2 moderator-icon"
                            src="../../images/icons/moderator.png"
                            alt="Moderator Icon"
                        />
                        Renbot - Moderators
                    </div>
                </VAppBarTitle>

                <VSpacer></VSpacer>

                <VChip pill class="mr-4">
                    <VAvatar start>
                        <VImg :src="user.avatar" alt="Avatar" />
                    </VAvatar>

                    {{ user.username }}
                </VChip>

                <VBtn color="primary-lighten-2" @click="logout">Logout</VBtn>
            </VAppBar>

            <VNavigationDrawer>
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
                        :href="route('commands.index')"
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
                        :href="route('rules.index')"
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
