<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { Streamday } from "@/types/Streamday";
import { Link, router } from "@inertiajs/vue3";
import { mdiAccount, mdiPencil, mdiPlus, mdiTrashCan } from "@mdi/js";
import dayjs from "dayjs";
import { ref } from "vue";
import { route } from "ziggy-js";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    streamdays: Streamday[];
}>();

const openDelete = (streamday: Streamday) => {
    streamdayToDelete.value = streamday;
    showDelete.value = true;
};

const showDelete = ref(false);
const streamdayToDelete = ref<Streamday | null>(null);
const deleteLoading = ref(false);
const deleteStreamday = () => {
    deleteLoading.value = true;
    router.delete(
        route("streamdays.destroy", { streamday: streamdayToDelete.value }),
        {
            onFinish: () => {
                deleteLoading.value = false;
            },
            onSuccess: () => {
                showDelete.value = false;
            },
        }
    );
};
</script>

<template>
    <div class="page">
        <header class="header mb-4">
            <div class="mb-2 mb-md-0">
                <h1 class="mb-2">Streamdays</h1>
                <p>A list of streamdays.</p>
            </div>

            <div class="add-button">
                <Link :href="route('streamdays.create')">
                    <VBtn color="green" :prepend-icon="mdiPlus"
                        >Add Streamday</VBtn
                    >
                </Link>
            </div>
        </header>

        <main>
            <VList lines="two" v-if="streamdays.length > 0">
                <VListItem
                    v-for="streamday in streamdays"
                    :key="streamday.id"
                    :title="`${dayjs(streamday.start_date).format(
                        'LL'
                    )} - ${dayjs(streamday.end_date).format('LL')}`"
                >
                    <template #append>
                        <div>
                            <Link
                                color="warning"
                                :href="route('streamdays.edit', { streamday })"
                            >
                                <VBtn variant="text" :icon="mdiPencil"></VBtn>
                            </Link>

                            <VBtn
                                variant="text"
                                :icon="mdiTrashCan"
                                @click="openDelete(streamday)"
                            ></VBtn>
                        </div>
                    </template>
                </VListItem>
            </VList>

            <p v-else class="mt-4">No streamdays created yet</p>
        </main>

        <VDialog v-model="showDelete" max-width="35rem">
            <VCard>
                <VCardTitle>Are you sure?</VCardTitle>
                <VCardText>
                    Are you sure you want to delete the streamday starting at
                    "{{ dayjs(streamdayToDelete?.start_date).format("LL") }}"?
                </VCardText>
                <VCardActions>
                    <VBtn color="grey" @click="showDelete = false">Cancel</VBtn>
                    <VBtn
                        color="error"
                        @click="deleteStreamday"
                        :loading="deleteLoading"
                        >Delete</VBtn
                    >
                </VCardActions>
            </VCard>
        </VDialog>
    </div>
</template>

<style scoped>
.page {
    padding: 1rem;
}

.header {
    display: grid;
}

@media screen and (min-width: 1080px) {
    .page {
        padding: 1rem 2rem;
    }

    .header {
        grid-template-columns: 1fr max-content;
    }

    .add-button {
        align-self: flex-end;
    }

    .search {
        grid-column: 1/3;
        margin-bottom: 1.5rem;
    }
}
</style>
