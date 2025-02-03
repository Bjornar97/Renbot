<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { Quote } from "@/types/Quote";
import { router, useForm } from "@inertiajs/vue3";
import { mdiAlphaEBox, mdiCancel, mdiContentSave } from "@mdi/js";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    quote: Quote;
}>();

const form = useForm("CreateCommand", {
    quote: props.quote.quote,
    said_by: props.quote.said_by,
    said_at: props.quote.said_at,
});

const submit = () => {
    form.put(route("quotes.update", { quote: props.quote }));
};

const cancel = () => {
    form.reset();
    router.get(route("quotes.index"));
};
</script>

<template>
    <div class="pa-2 pa-md-4">
        <VForm>
            <div>
                <header class="header">
                    <h1 class="mb-4">Edit quote</h1>
                </header>

                <VAlert
                    text="When adding a new quote, please only include family friendly content. If you would have deleted it if someone said it in chat, dont include it."
                    variant="tonal"
                    type="warning"
                    class="mb-8"
                >
                </VAlert>

                <VTextarea
                    v-model="form.quote"
                    label="Quote"
                    :error-messages="form.errors.quote"
                    class="mb-4"
                ></VTextarea>

                <VTextField class="mb-4" label="Said by" v-model="form.said_by">
                    <template #prepend-inner>@</template>
                </VTextField>

                <VLabel>Date said</VLabel>
                <VDatePicker class="mb-4" v-model="form.said_at"></VDatePicker>

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
