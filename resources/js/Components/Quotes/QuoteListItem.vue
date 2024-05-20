<script setup lang="ts">
import { Quote } from "@/types/Quote";
import { router } from "@inertiajs/core";
import { mdiMenu, mdiSendVariant, mdiTrashCan } from "@mdi/js";
import dayjs from "dayjs";
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

const chatQuote = () => {
    router.post(
        route("quote.chat", { quote: props.quote }),
        {},
        {
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <VListItem
        :title="quote.quote"
        :subtitle="`@${quote.said_by}, ${dayjs(quote.said_at)
            .utc()
            .format('L')}`"
        @click.stop="goToEdit"
    >
        <template #append>
            <VBtn
                variant="text"
                color="red"
                :icon="mdiTrashCan"
                @click.stop="showDeleteDialog = true"
            ></VBtn>
            <VBtn
                variant="text"
                color="success"
                :icon="mdiSendVariant"
                @click.stop="chatQuote"
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
