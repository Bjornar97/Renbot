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
}>();

const newCommand = () => {
    router.get(route("commands.create"));
};
</script>

<template>
    <div class="page">
        <header class="header">
            <div class="mb-2 mb-md-0">
                <h1 class="mb-2">Regular Commands</h1>
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
                        <th>Enabled</th>
                    </tr>
                </thead>

                <tbody>
                    <CommandRow
                        v-for="command in commands"
                        :command="command"
                        :key="command.id"
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
