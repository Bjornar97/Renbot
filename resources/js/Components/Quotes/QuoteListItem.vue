<script setup lang="ts">
import { Quote } from "@/types/Quote";
import { router } from "@inertiajs/core";
import { mdiMenu, mdiTrashCan } from "@mdi/js";
import { ref } from "vue";

const props = defineProps<{
    quote: Quote;
}>();

const goToEdit = () => {
    router.get(route("quotes.edit", { quote: props.quote.id }));
};

const showDeleteDialog = ref(false);

const deleteLoading = ref(false);

const deleteQuote = () => {
    deleteLoading.value = true;
    router.delete(route("quotes.destroy", { quote: props.quote.id }), {
        onFinish: () => {
            deleteLoading.value = false;
        },
    });
};
</script>

<template>
    <VListItem
        :title="quote.quote"
        @click.stop="goToEdit"
    >
        <template #append>
            <VBtn
                variant="text"
                color="red"
                :icon="mdiTrashCan"
                @click.stop="showDeleteDialog = true"
            ></VBtn>
        </template>

        <VDialog v-model="showDeleteDialog">
            <VCard class="delete-dialog">
                <VCardTitle>Are you sure?</VCardTitle>
                <VCardText
                    >Are you sure you want to delete this rule?</VCardText
                >

                <VCardActions>
                    <VBtn @click="showDeleteDialog = false">Cancel</VBtn>
                    <VBtn color="red" @click="deleteQuote">Delete</VBtn>
                </VCardActions>
            </VCard>
        </VDialog>
    </VListItem>
</template>
