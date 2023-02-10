<script setup lang="ts">
import type { Rule } from "@/types/Rule";
import { router, useForm } from "@inertiajs/vue3";
import { mdiMenu, mdiTrashCan } from "@mdi/js";
import { computed, ref } from "vue";

const props = defineProps<{
    rule: Rule;
}>();

console.log(props);

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
    <tr @click="goToEdit" class="cursor-pointer">
        <td>
            <VIcon :icon="mdiMenu"></VIcon>
        </td>

        <td>{{ rule.order + 1 }}</td>

        <td>{{ rule.text }}</td>

        <td @click.stop>
            <VBtn
                color="error"
                :icon="mdiTrashCan"
                variant="text"
                @click="showDeleteDialog = true"
            ></VBtn>
        </td>

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
