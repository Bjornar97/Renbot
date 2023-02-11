<script setup lang="ts">
import type { Command } from "@/types/Command";
import { router, useForm } from "@inertiajs/vue3";
import { mdiTrashCan } from "@mdi/js";
import { computed, ref } from "vue";

const props = defineProps<{
    command: Command;
}>();

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
    });
};
</script>

<template>
    <tr @click="goToEdit" class="cursor-pointer">
        <td>
            <template v-if="command.usable_by === 'moderators'">
                <img
                    src="../../../images/icons/moderator.png"
                    alt="Moderator icon"
                />
            </template>
            <template v-else-if="command.usable_by === 'subscribers'">
                <img
                    src="../../../images/icons/subscriber.png"
                    alt="Moderator icon"
                />
            </template>
        </td>

        <td>!{{ command.command }}</td>

        <td class="response">{{ command.response }}</td>

        <td @click.stop>
            <VSwitch
                v-model="enabled"
                color="red-darken-2"
                hide-details
            ></VSwitch>
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
