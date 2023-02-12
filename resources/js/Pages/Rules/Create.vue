<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import { mdiAlphaEBox, mdiCancel, mdiContentSave } from "@mdi/js";

defineOptions({
    layout: ModeratorLayout,
});

const form = useForm("CreateCommand", {
    text: "",
});

const submit = () => {
    form.post(route("rules.store"));
};

const cancel = () => {
    form.reset();
    router.get(route("rules.index"));
};
</script>

<template>
    <div class="pa-2 pa-md-4">
        <VForm>
            <div>
                <header class="header">
                    <h1 class="mb-4">Create new rule</h1>
                </header>

                <VTextarea
                    v-model="form.text"
                    label="Text"
                    :error-messages="form.errors.text"
                    class="mb-4"
                ></VTextarea>

                <div class="buttons">
                    <VBtn
                        color="green"
                        @click="submit"
                        :prepend-icon="mdiContentSave"
                        :loading="form.processing"
                    >
                        Save
                    </VBtn>
                    <VBtn @click="cancel" color="gray" :prepend-icon="mdiCancel"
                        >Cancel</VBtn
                    >
                </div>
            </div>
        </VForm>
    </div>
</template>

<style scoped>
.row {
    display: grid;
}

@media screen and (min-width: 1280px) {
    .row {
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
}

.buttons {
    display: flex;
    gap: 1rem;
}

.header {
    display: grid;
    grid-template-columns: 1fr max-content;
}
</style>
