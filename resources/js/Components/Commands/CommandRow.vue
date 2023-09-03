<script setup lang="ts">
import type { Command } from "@/types/Command";
import { router, useForm } from "@inertiajs/vue3";
import { mdiClockOutline, mdiTrashCan } from "@mdi/js";
import { computed, ref } from "vue";
import CommandUsableByIcon from "./CommandUsableByIcon.vue";
import SeverityChip from "./SeverityChip.vue";

const props = defineProps<{
    command: Command;
    type: "regular" | "punishable" | "special";
}>();

const switchLoading = ref(false);

const enabled = computed({
    get: () => props.command.enabled,
    set: (v: boolean) => {
        switchLoading.value = true;
        router.patch(
            route("commands.update", { command: props.command.id }),
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
    router.get(route("commands.edit", { command: props.command.id }));
};

const showDelete = ref(false);
const deleteLoading = ref(false);

const deleteCommand = () => {
    deleteLoading.value = true;
    router.delete(route("commands.destroy", { command: props.command.id }), {
        onFinish: () => {
            showDelete.value = false;
            deleteLoading.value = false;
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <tr @click="goToEdit" class="cursor-pointer">
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

        <td>!{{ command.command }}</td>

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
            <VBtn
                color="red"
                variant="text"
                :icon="mdiTrashCan"
                @click="showDelete = true"
            ></VBtn>
        </td>

        <VDialog v-model="showDelete">
            <VCard class="delete-dialog">
                <VCardTitle>Are you sure?</VCardTitle>

                <VCardText>
                    Are you sure you want to delete this command?
                </VCardText>

                <VCardActions>
                    <VBtn @click="showDelete = false">Cancel</VBtn>
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
</style>
