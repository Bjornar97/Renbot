<script setup lang="ts">
import type { Command } from "@/types/Command";
import { router } from "@inertiajs/core";
import ModeratorIcon from "../../../images/icons/moderator.png";
import SubscriberIcon from "../../../images/icons/subscriber.png";
import CommandUsableByIcon from "./CommandUsableByIcon.vue";
import SeverityChip from "./SeverityChip.vue";
import { computed } from "vue";
import { mdiClockOutline, mdiSendVariant } from "@mdi/js";
import { websocket } from "@/echo";
import { reactive } from "vue";
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

const getCommandUsableByIcon = () => {
    if (props.command.usable_by === "moderators") {
        return ModeratorIcon;
    }

    if (props.command.usable_by === "subscribers") {
        return SubscriberIcon;
    }

    return null;
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

const commandName = computed(() => {
    let result = `!${command.value.command}`;

    command.value.children?.forEach((child) => {
        result += ` | !${child.command}`;
    });

    return result;
});

const chatCommand = () => {
    router.post(
        route("commands.chat", { command: command.value.id }),
        undefined,
        { preserveScroll: true }
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
                <VTooltip open-on-hover text="Send to chat" location="top">
                    <template #activator="{ props }">
                        <VBtn
                            @click="chatCommand"
                            v-bind="props"
                            color="green"
                            variant="text"
                            :icon="mdiSendVariant"
                        ></VBtn>
                    </template>
                </VTooltip>

                <VSwitch
                    v-model="enabled"
                    color="primary"
                    class="ml-2"
                    hide-details
                ></VSwitch>
            </div>
        </template>
    </VListItem>
</template>

<style>
.v-list-item {
    align-items: center !important;
}
</style>
