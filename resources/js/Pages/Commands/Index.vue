<script setup lang="ts">
import CommandListItem from "@/Components/Commands/CommandListItem.vue";
import CommandRow from "@/Components/Commands/CommandRow.vue";
import CommandUsableByIcon from "@/Components/Commands/CommandUsableByIcon.vue";
import SeverityChip from "@/Components/Commands/SeverityChip.vue";
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import type { Command } from "@/types/Command";
import { router } from "@inertiajs/core";
import {
    mdiArrowDecisionAuto,
    mdiMessageReplyText,
    mdiPlus,
    mdiTargetAccount,
} from "@mdi/js";
import { useDisplay } from "vuetify/lib/framework.mjs";
import { computed, ref } from "vue";
import debounce from "lodash.debounce";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    commands: Command[];
    type: "regular" | "punishable" | "special";
}>();

const { mdAndUp, smAndUp } = useDisplay();

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

const currentRoute = ref(route().current());

router.on("navigate", () => {
    currentRoute.value = route().current();
});

const tab = computed({
    get: () => currentRoute.value,
    set: (v) => {
        if (!v) {
            return;
        }

        router.get(
            route(v),
            {},
            {
                preserveScroll: true,
                preserveState: true,
            }
        );
    },
});

const search = ref("");

const visibleCommands = computed(() => {
    return props.commands.filter((command) =>
        command.command.toLowerCase().includes(search.value.toLowerCase())
    );
});
</script>

<template>
    <div class="page">
        <header class="header">
            <div class="mb-2 mb-md-0">
                <h1 class="mb-2">
                    {{ type.charAt(0).toUpperCase()
                    }}{{ type.slice(1) }} commands
                </h1>
                <p v-if="type === 'regular'">Commands with a simple response</p>
                <p v-if="type === 'punishable'">
                    Commands that will punish a target user
                </p>
                <p v-if="type === 'special'">
                    Commands that does a custom action with code
                </p>
            </div>

            <div class="add-button">
                <VBtn @click="newCommand" color="green" :prepend-icon="mdiPlus"
                    >Add command</VBtn
                >
            </div>

            <nav v-if="!mdAndUp" class="mt-4">
                <VCard>
                    <VTabs v-model="tab" color="primary" stacked :grow="true">
                        <VTab value="commands.index">
                            <VIcon :icon="mdiMessageReplyText"></VIcon>
                            Regular</VTab
                        >
                        <VTab value="punishable-commands.index">
                            <VIcon :icon="mdiTargetAccount"></VIcon>
                            Punishable</VTab
                        >
                        <VTab value="special-commands.index">
                            <VIcon :icon="mdiArrowDecisionAuto"></VIcon>
                            Special
                        </VTab>
                    </VTabs>
                </VCard>
            </nav>
        </header>

        <main class="mt-8">
            <VTextField label="Search" v-model="search"></VTextField>

            <VTable class="commands-table" hover v-if="mdAndUp">
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
                    <tr v-if="visibleCommands.length <= 0">
                        <td colspan="3">
                            <template v-if="search"> No results </template>
                            <template v-else>
                                No {{ type }} commands created yet
                            </template>
                        </td>
                    </tr>

                    <CommandRow
                        v-for="command in visibleCommands"
                        :command="command"
                        :key="command.id"
                        :type="type"
                    ></CommandRow>
                </tbody>
            </VTable>

            <VList lines="two" v-else>
                <CommandListItem
                    v-if="visibleCommands.length > 0"
                    v-for="command in visibleCommands"
                    :key="command.id"
                    :command="command"
                ></CommandListItem>

                <p v-else-if="search">No results</p>
                <p v-else class="ma-4">No {{ type }} commands created yet</p>
            </VList>
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

@media screen and (min-width: 1080px) {
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

.center-icon {
    display: grid;
    place-content: center;
}
</style>
