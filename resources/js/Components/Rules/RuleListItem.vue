<script setup lang="ts">
import type { Rule } from "@/types/Rule";
import { router } from "@inertiajs/core";
import { mdiMenu, mdiTrashCan } from "@mdi/js";
import { ref } from "vue";

const props = defineProps<{
    rule: Rule;
    moveable: boolean;
}>();

const goToEdit = () => {
    router.get(route("rules.edit", { rule: props.rule.id }));
};

const showDeleteDialog = ref(false);

const deleteLoading = ref(false);

const deleteRule = () => {
    deleteLoading.value = true;
    router.delete(route("rules.destroy", { rule: props.rule.id }), {
        onFinish: () => {
            deleteLoading.value = false;
        },
    });
};
</script>

<template>
    <VListItem
        :title="`${rule.order + 1}.`"
        :subtitle="rule.text"
        @click.stop="goToEdit"
    >
        <template #prepend v-if="moveable">
            <VIcon :icon="mdiMenu"></VIcon>
        </template>

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
                    <VBtn color="red" @click="deleteRule">Delete</VBtn>
                </VCardActions>
            </VCard>
        </VDialog>
    </VListItem>
</template>
