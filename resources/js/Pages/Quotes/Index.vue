<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { router } from "@inertiajs/vue3";
import { mdiPlus } from "@mdi/js";
import { route } from "ziggy-js";
import { useDisplay } from "vuetify/lib/framework.mjs";
import { Quote } from "@/types/Quote";
import QuoteListItem from "@/Components/Quotes/QuoteListItem.vue";
import QuoteRow from "@/Components/Quotes/QuoteRow.vue";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    quotes: Quote[];
}>();

const newQuote = () => {
    router.get(route("quotes.create"));
};

const { mdAndUp } = useDisplay();
</script>

<template>
    <div class="page">
        <header class="header">
            <div class="mb-2 mb-md-0">
                <h1 class="mb-2">Quotes</h1>
          
            </div>

            <div class="add-button">
                <VBtn @click="newQuote" color="green" :prepend-icon="mdiPlus"
                    >Add quote</VBtn
                >
            </div>
        </header>

        <main>
            <VTable class="mt-8" hover v-if="mdAndUp">
                <thead>
                    <tr>
                        <th>Said by</th>
                        <th>Quote</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-if="quotes.length <= 0">
                        <td colspan="4">No quotes created yet</td>
                    </tr>

                    <QuoteRow v-for="quote in quotes" :quote="quote"></QuoteRow>
                </tbody>
            </VTable>

            <VList v-else-if="quotes.length > 0" class="mt-8" lines="three">
                <QuoteListItem
                    v-for="quote in quotes"
                    :quote="quote"
                ></QuoteListItem>

                <!-- <Draggable v-model="order" item-key="id" id="draggable">
                    <template #item="{ element }">
                        <RuleListItem :rule="element" :moveable="true"></RuleListItem>
                    </template>
                </Draggable> -->
            </VList>

            <p v-else class="my-4">No quotes created yet</p>
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
</style>
