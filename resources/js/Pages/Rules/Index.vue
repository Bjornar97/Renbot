<script setup lang="ts">
import RuleRow from "@/Components/Rules/RuleRow.vue";
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import type { Rule } from "@/types/Rule";
import { Link, router } from "@inertiajs/vue3";
import { mdiPlus, mdiScaleBalance } from "@mdi/js";
import { route } from "ziggy-js";
import Draggable from "vuedraggable";
import { computed } from "vue";
import RuleListItem from "@/Components/Rules/RuleListItem.vue";
import { useDisplay } from "vuetify/lib/framework.mjs";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    rules: Rule[];
}>();

const order = computed({
    get: () => props.rules,
    set: (v: Rule[]) => {
        router.put(
            route("rules.order.update"),
            {
                order: v.map((rule) => rule.id),
            },
            {
                preserveScroll: true,
            }
        );
    },
});

const newRule = () => {
    router.get(route("rules.create"));
};

const showRulesPage = () => {
    router.get(route("rules"));
};

const { mdAndUp } = useDisplay();
</script>

<template>
    <div class="page">
        <header class="header">
            <div class="mb-2 mb-md-0">
                <h1 class="mb-2">Rules</h1>
                <p>
                    The rules that show up on the
                    <Link :href="route('rules')">rules page</Link>
                </p>
            </div>

            <div class="add-button">
                <VBtn
                    color="primary"
                    :prepend-icon="mdiScaleBalance"
                    @click="showRulesPage"
                    >See rules page</VBtn
                >

                <VBtn @click="newRule" color="green" :prepend-icon="mdiPlus"
                    >Add rule</VBtn
                >
            </div>
        </header>

        <main>
            <p v-if="!mdAndUp" class="my-4">
                You cannot change the order of rules on mobile. If you want to
                change the order, use a computer.
            </p>

            <VTable class="mt-8" hover v-if="mdAndUp">
                <thead>
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>Text</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-if="rules.length <= 0">
                        <td colspan="2">No rules created yet</td>
                    </tr>

                    <!-- <RuleRow v-for="rule in rules" :rule="rule"></RuleRow> -->
                    <Draggable v-model="order" item-key="id" id="draggable">
                        <template #item="{ element }">
                            <RuleRow :rule="element"></RuleRow>
                        </template>
                    </Draggable>
                </tbody>
            </VTable>

            <VList v-else-if="rules.length > 0" class="mt-8" lines="three">
                <RuleListItem
                    v-for="rule in rules"
                    :rule="rule"
                    :moveable="false"
                ></RuleListItem>

                <!-- <Draggable v-model="order" item-key="id" id="draggable">
                    <template #item="{ element }">
                        <RuleListItem :rule="element" :moveable="true"></RuleListItem>
                    </template>
                </Draggable> -->
            </VList>

            <p v-else class="my-4">No rules created yet</p>
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

.add-button {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
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

#draggable {
    display: contents;
}
</style>

<style>
#draggable td {
    padding: 16px 16px;
}
#draggable tr:hover {
    background: rgba(var(--v-border-color), var(--v-hover-opacity));
}
</style>
