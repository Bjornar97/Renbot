<script setup lang="ts">
import CommandRow from "@/Components/Commands/CommandRow.vue";
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import type { Command } from "@/types/Command";
import { router } from "@inertiajs/core";
import { mdiPlus } from "@mdi/js";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    commands: Command[];
    type: "regular" | "punishable" | "special";
}>();

const newCommand = () => {
    if (props.type === "punishable") {
        router.get(route("punishable-commands.create"));
        return;
    } else if (props.type === "special") {
        router.get(route("special-commands.create"));
        return;
    }

    router.get(route("commands.create"));
};
</script>

<template>
    <div class="page">
        <header class="header">
            <div class="mb-2 mb-md-0">
                <h1 class="mb-2">
                    {{ type.charAt(0).toUpperCase()
                    }}{{ type.slice(1) }} commands
                </h1>
                <p>Commands with a simple response</p>
            </div>

            <div class="add-button">
                <VBtn @click="newCommand" color="green" :prepend-icon="mdiPlus"
                    >Add command</VBtn
                >
            </div>
        </header>

        <main>
            <VTable class="mt-8" hover>
                <thead>
                    <tr>
                        <th></th>
                        <th>Command</th>
                        <th>Response</th>
                        <th v-if="type === 'punishable'">Severity</th>
                        <th>Enabled</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <CommandRow
                        v-for="command in commands"
                        :command="command"
                        :key="command.id"
                        :type="type"
                    ></CommandRow>
                </tbody>
            </VTable>
        </main>
    </div>
</template>

<style scoped>
.page {
    padding: 1rem;
}

.header {
    display: grid;
}

@media screen and (min-width: 768px) {
    .page {
        padding: 1rem 2rem;
    }

    .header {
        grid-template-columns: 1fr max-content;
    }

    .add-button {
        align-self: flex-end;
    }
}
</style>
