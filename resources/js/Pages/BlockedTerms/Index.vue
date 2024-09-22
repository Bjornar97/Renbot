<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { BlockedTerm } from "@/types/BlockedTerm";
import { router, useForm } from "@inertiajs/vue3";
import { mdiDelete, mdiPencil, mdiPlus } from "@mdi/js";
import dayjs from "dayjs";
import { computed, ref } from "vue";
import { useDisplay } from "vuetify/lib/framework.mjs";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    terms: BlockedTerm[];
}>();

const display = useDisplay();

const search = ref("");

const headers = computed((): any => {
    const headers: any[] = [
        {
            title: "Term",
            sortable: true,
            key: "term",
            width: "30rem",
        },
    ];

    if (display.mdAndUp.value) {
        headers.push(
            {
                title: "Comment",
                sortable: true,
                key: "comment",
            },
            {
                title: "Created date",
                sortable: true,
                sort(a: string, b: string) {
                    return dayjs(a).diff(dayjs(b));
                },
                value: (item: BlockedTerm) => {
                    return dayjs(item.created_at).format("LL");
                },
                key: "created_at",
                width: "10rem",
            }
        );
    }

    headers.push({ title: "Actions", key: "actions", sortable: false });

    return headers;
});

const editDialogOpen = ref(false);
const editForm = useForm({
    twitch_id: "",
    term: "",
    comment: "" as string | null,
});

const editTerm = (term: BlockedTerm) => {
    editForm.defaults({
        twitch_id: term.twitch_id,
        term: term.term,
        comment: term.comment,
    });

    editForm.reset();

    editDialogOpen.value = true;
};

const saveEdit = () => {
    editForm.put(
        route("blocked-terms.update", { blocked_term: editForm.twitch_id }),
        {
            preserveScroll: true,
            onSuccess: () => {
                editDialogOpen.value = false;
            },
        }
    );
};

const newDialogOpen = ref(false);
const newForm = useForm({
    term: "",
    comment: "",
});

const saveNew = () => {
    newForm.post(route("blocked-terms.store"), {
        onSuccess: () => {
            newDialogOpen.value = false;
            newForm.reset();
        },
    });
};

const deleteLoading = ref(false);
const deleteTermId = ref();

const deleteTerm = (term: BlockedTerm) => {
    deleteLoading.value = true;
    deleteTermId.value = term.twitch_id;

    router.delete(
        route("blocked-terms.destroy", {
            blocked_term: term.twitch_id,
        }),
        {
            preserveScroll: true,
            onFinish: () => {
                deleteLoading.value = false;
            },
        }
    );
};
</script>

<template>
    <div class="pa-4">
        <div class="d-flex justify-space-between align-center mb-4">
            <h1 class="mb-2">Blocked terms</h1>

            <VBtn :prepend-icon="mdiPlus" @click="newDialogOpen = true">
                New Blocked Term
            </VBtn>
        </div>

        <p class="mb-4">
            Blocked terms are not allowed to be used in chat. Terms added here
            are automatically synced to Twitch, and vice versa. If a user types
            a message that contains a blocked term, they will not be able to
            post the chat. Keep in mind that they will not get to know which
            term they used that is blocked.
        </p>

        <p class="mb-4">
            You can use wildcard * either in front or back of a word. Then it
            will match anything either before or after.
        </p>

        <VTextField label="Search" type="search" v-model="search"></VTextField>

        <VDataTable
            :items="terms"
            items-per-page="10"
            no-data-text="No terms found"
            items-per-page-text="Terms per page:"
            sticky
            must-sort
            :sort-by="[{ key: 'created_at', order: 'desc' }]"
            :headers="headers"
            :search="search"
        >
            <template #item.term="{ item }">
                <div class="py-2" v-if="display.smAndDown.value">
                    <p>{{ item.term }}</p>
                    <p class="text-medium-emphasis text-caption">
                        {{ item.comment }}
                    </p>
                    <p class="text-medium-emphasis text-caption">
                        {{ dayjs(item.created_at).format("LL") }}
                    </p>
                </div>
                <template v-else>{{ item.term }}</template>
            </template>

            <template #item.actions="{ item }">
                <VBtn
                    color="white"
                    variant="text"
                    :icon="mdiPencil"
                    @click="editTerm(item)"
                >
                </VBtn>
                <VBtn
                    variant="text"
                    color="error"
                    :icon="mdiDelete"
                    @click="deleteTerm(item)"
                    :loading="deleteLoading && item.twitch_id === deleteTermId"
                    :disabled="deleteLoading"
                >
                </VBtn>
            </template>
        </VDataTable>

        <VDialog v-model="editDialogOpen" max-width="30rem">
            <VForm @submit.prevent="saveEdit">
                <VCard :loading="editForm.processing">
                    <VCardTitle>Edit Term</VCardTitle>
                    <VCardText>
                        <VTextField
                            readonly
                            disabled
                            label="Term"
                            v-model="editForm.term"
                        ></VTextField>
                        <VTextField
                            label="Comment"
                            v-model="editForm.comment"
                            :error-messages="editForm.errors.comment"
                        ></VTextField>
                    </VCardText>

                    <VCardActions>
                        <VBtn
                            :disabled="editForm.processing"
                            color="grey"
                            @click="editDialogOpen = false"
                            >Cancel</VBtn
                        >
                        <VBtn
                            color="success"
                            type="submit"
                            :disabled="editForm.processing"
                            >Save</VBtn
                        >
                    </VCardActions>
                </VCard>
            </VForm>
        </VDialog>

        <VDialog v-model="newDialogOpen" max-width="30rem">
            <VForm @submit.prevent="saveNew">
                <VCard :loading="newForm.processing">
                    <VCardTitle>New Term</VCardTitle>
                    <VCardText>
                        <VAlert
                            class="mb-6"
                            type="error"
                            v-if="newForm.hasErrors"
                        >
                            <ul>
                                <li
                                    v-for="error in Object.values(
                                        newForm.errors
                                    )"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                        </VAlert>

                        <VTextField
                            class="mb-2"
                            label="Term"
                            v-model="newForm.term"
                            :error-messages="newForm.errors.term"
                        ></VTextField>
                        <VTextField
                            label="Comment"
                            v-model="newForm.comment"
                            :error-messages="newForm.errors.comment"
                        ></VTextField>
                    </VCardText>

                    <VCardActions>
                        <VBtn
                            :disabled="newForm.processing"
                            color="grey"
                            @click="newDialogOpen = false"
                            >Cancel</VBtn
                        >
                        <VBtn
                            color="success"
                            type="submit"
                            :disabled="newForm.processing"
                            >Save</VBtn
                        >
                    </VCardActions>
                </VCard>
            </VForm>
        </VDialog>
    </div>
</template>
