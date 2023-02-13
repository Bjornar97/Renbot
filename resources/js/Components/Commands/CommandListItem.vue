<script setup lang="ts">
import type { Command } from "@/types/Command";
import { router } from "@inertiajs/core";
import ModeratorIcon from "../../../images/icons/moderator.png";
import SubscriberIcon from "../../../images/icons/subscriber.png";
import CommandUsableByIcon from "./CommandUsableByIcon.vue";
import SeverityChip from "./SeverityChip.vue";
import { computed } from "vue";

const props = defineProps<{
    command: Command;
}>();

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
    get: () => props.command.enabled,
    set: (v: boolean) => {
        router.patch(
            route("commands.update", { command: props.command.id }),
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
</script>

<template>
    <VListItem
        @click="goToCommand"
        :title="`!${command.command}`"
        :subtitle="command.response"
    >
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

        <template #append>
            <div @click.stop>
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