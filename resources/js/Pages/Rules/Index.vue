<script setup lang="ts">
import RuleRow from "@/Components/Rules/RuleRow.vue";
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import type { Rule } from "@/types/Rule";
import { Link, router } from "@inertiajs/vue3";
import { mdiPlus } from "@mdi/js";
import route from "ziggy-js";
import Draggable from "vuedraggable";
import { computed } from "vue";

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
</script>

<template>
    <div class="page">
        <header class="header">
            <div class="mb-2 mb-md-0">
                <h1 class="mb-2">Rules</h1>
                <p>
                    The rules that show up on the rules page
                    <Link :href="route('rules')"></Link>
                </p>
            </div>

            <div class="add-button">
                <VBtn @click="newRule" color="green" :prepend-icon="mdiPlus"
                    >Add rule</VBtn
                >
            </div>
        </header>

        <main>
            <VTable class="mt-8" hover>
                <thead>
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>Text</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <!-- <RuleRow v-for="rule in rules" :rule="rule"></RuleRow> -->
                    <Draggable v-model="order" item-key="id" id="draggable">
                        <template #item="{ element }">
                            <RuleRow :rule="element"></RuleRow>
                        </template>
                    </Draggable>
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
