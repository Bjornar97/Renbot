<script setup lang="ts">
import type { Command } from "@/types/Command";
import { router, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

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

        <td>{{ command.response }}</td>

        <td @click.stop>
            <VSwitch
                v-model="enabled"
                color="red-darken-2"
                hide-details
            ></VSwitch>
        </td>
    </tr>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
