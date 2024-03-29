<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { Creator } from "@/types/Creator";
import { Link, router } from "@inertiajs/vue3";
import { mdiAccount, mdiPencil, mdiPlus, mdiTrashCan } from "@mdi/js";
import { ref } from "vue";
import { route } from "ziggy-js";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    creators: Creator[];
}>();

const openDelete = (creator: Creator) => {
    creatorToDelete.value = creator;
    showDelete.value = true;
};

const showDelete = ref(false);
const creatorToDelete = ref<Creator | null>(null);
const deleteLoading = ref(false);
const deleteCreator = () => {
    deleteLoading.value = true;
    router.delete(
        route("creators.destroy", { creator: creatorToDelete.value }),
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
                <h1 class="mb-2">Creators</h1>
                <p>A list of creators Rendog interacts with.</p>
            </div>

            <div class="add-button">
                <Link :href="route('creators.create')">
                    <VBtn color="green" :prepend-icon="mdiPlus"
                        >Add Creator</VBtn
                    >
                </Link>
            </div>
        </header>

        <main>
            <VList lines="two" v-if="creators.length > 0">
                <VListItem
                    v-for="creator in creators"
                    :key="creator.id"
                    :title="creator.name"
                    :prepend-avatar="creator.image_url"
                    :prepend-icon="!creator.image_url ? mdiAccount : undefined"
                >
                    <template #append>
                        <div>
                            <Link
                                color="warning"
                                :href="route('creators.edit', { creator })"
                            >
                                <VBtn variant="text" :icon="mdiPencil"></VBtn>
                            </Link>

                            <VBtn
                                variant="text"
                                :icon="mdiTrashCan"
                                @click="openDelete(creator)"
                            ></VBtn>
                        </div>
                    </template>
                </VListItem>
            </VList>

            <p v-else class="mt-4">No creators created yet</p>
        </main>

        <VDialog v-model="showDelete" max-width="35rem">
            <VCard>
                <VCardTitle>Are you sure?</VCardTitle>
                <VCardText>
                    Are you sure you want to delete the creator "{{
                        creatorToDelete?.name
                    }}"?
                </VCardText>
                <VCardActions>
                    <VBtn color="grey" @click="showDelete = false">Cancel</VBtn>
                    <VBtn
                        color="error"
                        @click="deleteCreator"
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
