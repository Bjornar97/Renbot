<script setup lang="ts">
import type { Command } from "@/types/Command";
import { router } from "@inertiajs/core";
import CommandUsableByIcon from "./CommandUsableByIcon.vue";
import SeverityChip from "./SeverityChip.vue";
import { computed } from "vue";
import {
    mdiBullhornVariant,
    mdiChat,
    mdiClockOutline,
    mdiDotsVertical,
} from "@mdi/js";
import { websocket } from "@/echo";
import { ref } from "vue";
import { watch } from "vue";

const props = defineProps<{
    command: Command;
}>();

const command = ref(props.command);

watch(
    () => props.command,
    (value) => {
        command.value = value;
    },
    {
        deep: true,
    }
);

websocket
    .private(`App.Models.Command.${props.command.id}`)
    .listen(".CommandUpdated", ({ model }: { model: Command }) => {
        console.log({ model });
        command.value = model;
    });

const goToCommand = () => {
    router.get(route("commands.edit", { command: props.command.id }));
};

const enabled = computed({
    get: () => command.value.enabled,
    set: (v: boolean) => {
        router.patch(
            route("commands.update", { command: command.value.id }),
            {
                enabled: v,
            },
            {
                preserveScroll: true,
                preserveState: true,
            }
        );
    },
});

type ChatType = "chat" | "announcement";
type AnnouncementColor = "blue" | "green" | "orange" | "purple" | "primary";

const chatLoading = ref(false);

const chatCommand = (
    type: ChatType,
    announcement_color?: AnnouncementColor
) => {
    chatLoading.value = true;

    router.post(
        route("commands.chat", { command: command.value.id }),
        {
            type,
            announcement_color,
        },
        {
            preserveScroll: true,
            onFinish: () => (chatLoading.value = false),
        }
    );
};
</script>

<template>
    <VListItem @click="goToCommand" :subtitle="command.response">
        <template #prepend>
            <SeverityChip
                v-if="command.type === 'punishable'"
                :command="command"
                class="mr-2 text-body-1 mt-2"
                size="default"
            ></SeverityChip>

            <CommandUsableByIcon
                v-else
                width="1.2rem"
                class="mr-4 mt-3"
                :command="command"
            ></CommandUsableByIcon>
        </template>

        <template #title>
            <div class="d-flex align-center">
                <VIcon
                    size="small"
                    color="grey-darken-1 mr-1"
                    :icon="mdiClockOutline"
                    v-if="command.auto_post_enabled"
                ></VIcon>

                <VChipGroup>
                    <VChip>!{{ command.command }}</VChip>
                    <VChip v-for="child in command.children" :key="child.id"
                        >!{{ child.command }}</VChip
                    >
                </VChipGroup>
            </div>
        </template>

        <template #append>
            <div @click.stop class="d-flex align-center ga-2">
                <VSwitch
                    v-model="enabled"
                    color="primary"
                    class="ml-2"
                    hide-details
                ></VSwitch>

                <VMenu>
                    <template #activator="{ props }">
                        <VBtn
                            variant="text"
                            color="green"
                            v-bind="props"
                            :loading="chatLoading"
                            :icon="mdiDotsVertical"
                        ></VBtn>
                    </template>
                    <VList>
                        <VListItem
                            :prepend-icon="mdiChat"
                            @click="() => chatCommand('chat')"
                        >
                            <VListItemTitle>Chat</VListItemTitle>
                        </VListItem>
                        <VDivider></VDivider>
                        <VListItem
                            color="primary"
                            base-color="primary"
                            :prepend-icon="mdiBullhornVariant"
                            @click="
                                () => chatCommand('announcement', 'primary')
                            "
                        >
                            <VListItemTitle>Announce Primary</VListItemTitle>
                        </VListItem>
                        <VListItem
                            color="blue"
                            base-color="blue"
                            :prepend-icon="mdiBullhornVariant"
                            @click="() => chatCommand('announcement', 'blue')"
                        >
                            <VListItemTitle>Announce Blue</VListItemTitle>
                        </VListItem>
                        <VListItem
                            color="green"
                            base-color="green"
                            :prepend-icon="mdiBullhornVariant"
                            @click="() => chatCommand('announcement', 'green')"
                        >
                            <VListItemTitle>Announce Green</VListItemTitle>
                        </VListItem>
                        <VListItem
                            color="orange"
                            base-color="orange"
                            :prepend-icon="mdiBullhornVariant"
                            @click="() => chatCommand('announcement', 'orange')"
                        >
                            <VListItemTitle>Announce Orange</VListItemTitle>
                        </VListItem>
                        <VListItem
                            color="purple"
                            base-color="purple"
                            :prepend-icon="mdiBullhornVariant"
                            @click="() => chatCommand('announcement', 'purple')"
                        >
                            <VListItemTitle>Announce Purple</VListItemTitle>
                        </VListItem>
                    </VList>
                </VMenu>
            </div>
        </template>
    </VListItem>
</template>

<style>
.v-list-item {
    align-items: center !important;
}
</style>
