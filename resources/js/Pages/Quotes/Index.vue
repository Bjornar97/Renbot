<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { router } from "@inertiajs/vue3";
import { mdiPlus } from "@mdi/js";
import { route } from "ziggy-js";
import { useDisplay } from "vuetify/lib/framework.mjs";
import { Quote } from "@/types/Quote";
import QuoteListItem from "@/Components/Quotes/QuoteListItem.vue";
import QuoteRow from "@/Components/Quotes/QuoteRow.vue";
import { computed, ref } from "vue";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    quotes: Quote[];
}>();

const search = ref("");

const newQuote = () => {
    router.get(route("quotes.create"));
};

const searchedQuotes = computed(() => {
    return props.quotes.filter(
        (quote) =>
            quote.quote.includes(search.value) ||
            quote.said_by.includes(search.value)
    );
});

const { mdAndUp } = useDisplay();
</script>

<template>
    <div class="page">
        <header class="header">
            <h1>Quotes</h1>

            <div class="add-button">
                <VBtn @click="newQuote" color="green" :prepend-icon="mdiPlus"
                    >Add quote</VBtn
                >
            </div>
        </header>

        <main>
            <VTextField
                class="search"
                v-model="search"
                label="Search"
            ></VTextField>

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

                    <tr v-else-if="searchedQuotes.length <= 0">
                        <td colspan="4">No results</td>
                    </tr>

                    <QuoteRow
                        v-for="quote in searchedQuotes"
                        :quote="quote"
                    ></QuoteRow>
                </tbody>
            </VTable>

            <VList v-else-if="searchedQuotes.length > 0" lines="three">
                <QuoteListItem
                    v-for="quote in searchedQuotes"
                    :quote="quote"
                ></QuoteListItem>
            </VList>

            <p v-else-if="searchedQuotes.length <= 0">No results</p>

            <p v-else>No quotes created yet</p>
        </main>
    </div>
</template>

<style scoped>
.page {
    padding: 1rem;
}

.header {
    display: grid;
    align-items: end;
    gap: 1rem;
    margin-bottom: 1rem;
}

.add-button {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 0.5rem;
}

@media screen and (min-width: 768px) {
    .page {
        padding: 1rem 2rem;
    }

    .header {
        grid-template-columns: 1fr max-content;
    }
}
</style>
