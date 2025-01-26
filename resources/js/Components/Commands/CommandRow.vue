<script setup lang="ts">
import type { Command } from "@/types/Command";
import { router } from "@inertiajs/vue3";
import {
    mdiBullhornVariant,
    mdiClockOutline,
    mdiMenuDown,
    mdiSendVariant,
    mdiTrashCan,
} from "@mdi/js";
import { computed, ref } from "vue";
import CommandUsableByIcon from "./CommandUsableByIcon.vue";
import SeverityChip from "./SeverityChip.vue";
import { websocket } from "@/echo";
import { watch } from "vue";

const props = defineProps<{
    command: Command;
    type: "regular" | "punishable" | "special";
}>();

const switchLoading = ref(false);

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

const flash = ref(false);

websocket
    .private(`App.Models.Command.${props.command.id}`)
    .listen(".CommandUpdated", ({ model }: { model: Command }) => {
        command.value = model;

        flash.value = true;
        setTimeout(() => {
            flash.value = false;
        }, 1000);
    });

const enabled = computed({
    get: () => command.value.enabled,
    set: (v: boolean) => {
        switchLoading.value = true;
        router.patch(
            route("commands.update", { command: command.value.id }),
            {
                enabled: v,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onFinish: () => {
                    switchLoading.value = false;
                },
            }
        );
    },
});

const goToEdit = () => {
    router.get(route("commands.edit", { command: command.value.id }));
};

type ChatType = "chat" | "announcement";
type AnnouncementColor = "blue" | "green" | "orange" | "purple" | "primary";

const chatLoading = ref(false);

const chatCommand = (
    type?: ChatType,
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

const showDelete = ref(false);
const deleteLoading = ref(false);

const deleteCommand = () => {
    deleteLoading.value = true;
    router.delete(route("commands.destroy", { command: command.value.id }), {
        onFinish: () => {
            showDelete.value = false;
            deleteLoading.value = false;
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <tr
        @click="goToEdit"
        class="cursor-pointer command-row"
        :class="{
            flash: flash,
        }"
    >
        <td>
            <SeverityChip
                v-if="command.type === 'punishable'"
                :command="command"
            ></SeverityChip>
            <CommandUsableByIcon
                v-else
                :command="command"
            ></CommandUsableByIcon>
        </td>

        <td>
            <VTooltip location="top">
                <template #activator="{ props }">
                    <VIcon
                        v-bind="props"
                        color="grey"
                        :icon="mdiClockOutline"
                        v-if="command.auto_post_enabled"
                    ></VIcon>
                </template>
                Auto post enabled
            </VTooltip>
        </td>

        <td class="py-2">
            !{{ command.command }}

            <template v-for="child in command.children" :key="child.id">
                <br />!{{ child.command }}
            </template>
        </td>

        <td @click.stop>
            <VSwitch
                v-model="enabled"
                color="primary"
                hide-details
                :loading="switchLoading"
            ></VSwitch>
        </td>

        <td class="response" :style="{ 'max-width': '66ch' }">
            {{ command.response }}
        </td>

        <td @click.stop>
            <VBtnGroup divided rounded="md" variant="outlined" color="green">
                <VBtn
                    color="red"
                    variant="outlined"
                    rounded="md"
                    @click="showDelete = true"
                >
                    <VIcon :icon="mdiTrashCan"></VIcon>
                </VBtn>

                <VBtn
                    @click="chatCommand"
                    :prepend-icon="mdiSendVariant"
                    text="Chat"
                    :loading="chatLoading"
                    :disabled="chatLoading"
                >
                </VBtn>

                <VMenu location="bottom right">
                    <template #activator="{ props }">
                        <VBtn
                            variant="outlined"
                            v-bind="props"
                            :icon="mdiMenuDown"
                            :loading="chatLoading"
                            :disabled="chatLoading"
                        ></VBtn>
                    </template>
                    <VList>
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
            </VBtnGroup>
        </td>

        <VDialog v-model="showDelete">
            <VCard class="delete-dialog">
                <VCardTitle>Are you sure?</VCardTitle>

                <VCardText>
                    Are you sure you want to delete this command?
                </VCardText>

                <VCardActions>
                    <VBtn @click="showDelete = false" color="grey">Cancel</VBtn>
                    <VBtn
                        color="red"
                        @click="deleteCommand"
                        :loading="deleteLoading"
                        >Delete</VBtn
                    >
                </VCardActions>
            </VCard>
        </VDialog>
    </tr>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.delete-dialog {
    max-width: 50rem;
    margin: auto;
}

.response {
    padding-block: 1rem !important;
}

.command-row {
    transition: all 250ms ease;
}

.flash {
    background-color: rgb(var(--v-theme-secondary-darken-1));
}
</style>
