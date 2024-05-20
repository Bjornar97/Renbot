<script setup lang="ts">
import { Quote } from "@/types/Quote";
import { router } from "@inertiajs/vue3";
import { mdiMenu, mdiSendCircle, mdiSendVariant, mdiTrashCan } from "@mdi/js";
import dayjs from "dayjs";
import { computed, ref } from "vue";

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
            showDeleteDialog.value = false;
        },
    });
};

const date = computed(() => {
    return dayjs(props.quote.said_at).utc().format("L");
});

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
    <tr @click="goToEdit" class="cursor-pointer">
        <td>@{{ quote.said_by }}</td>
        <td>{{ quote.quote }}</td>
        <td>{{ date }}</td>

        <td @click.stop>
            <div class="d-flex">
                <VBtn
                    color="error"
                    :icon="mdiTrashCan"
                    variant="text"
                    @click="showDeleteDialog = true"
                ></VBtn>

                <VTooltip open-on-hover text="Send to chat" location="top">
                    <template #activator="{ props }">
                        <VBtn
                            @click="chatQuote"
                            v-bind="props"
                            color="success"
                            variant="text"
                            :icon="mdiSendVariant"
                        ></VBtn>
                    </template>
                </VTooltip>
            </div>
        </td>

        <VDialog v-model="showDeleteDialog">
            <VCard class="delete-dialog">
                <VCardTitle>Are you sure?</VCardTitle>
                <VCardText
                    >Are you sure you want to delete this quote?</VCardText
                >

                <VCardActions>
                    <VBtn @click="showDeleteDialog = false">Cancel</VBtn>
                    <VBtn color="red" @click="deleteQuote">Delete</VBtn>
                </VCardActions>
            </VCard>
        </VDialog>
    </tr>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.delete-dialog {
    max-width: 50rem;
    margin: auto;
}
</style>
