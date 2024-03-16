<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { mdiCamera, mdiContentSave } from "@mdi/js";

defineOptions({
    layout: ModeratorLayout,
});

const form = useForm({
    name: "",
    youtube_url: "",
    twitch_url: "",
    x_url: "",
    image: undefined,
});

const save = () => {
    console.log({ image: form.image });
    form.post(route("creators.store"));
};
</script>

<template>
    <div class="page">
        <header class="header mb-4">
            <h1 class="mb-2 mb-md-0">Create Creator</h1>
        </header>

        <main>
            <VForm @submit.prevent="save">
                <VTextField
                    v-model="form.name"
                    label="Name"
                    class="mb-3"
                    :error-messages="form.errors.name"
                ></VTextField>

                <VTextField
                    v-model="form.youtube_url"
                    label="Youtube Channel URL"
                    class="mb-3"
                    :error-messages="form.errors.youtube_url"
                ></VTextField>

                <VTextField
                    v-model="form.twitch_url"
                    label="Twitch Channel URL"
                    class="mb-3"
                    :error-messages="form.errors.twitch_url"
                ></VTextField>

                <VTextField
                    v-model="form.x_url"
                    label="X (Twitter) Account URL"
                    class="mb-3"
                    :error-messages="form.errors.x_url"
                ></VTextField>

                <VFileInput
                    v-model="form.image"
                    :multiple="false"
                    label="Image / Logo"
                    class="mb-3"
                    :prepend-icon="mdiCamera"
                    :error-messages="form.errors.image"
                ></VFileInput>

                <VBtn
                    type="submit"
                    color="success"
                    :prepend-icon="mdiContentSave"
                    :loading="form.processing"
                    >Save</VBtn
                >
            </VForm>
        </main>
    </div>
</template>

<style scoped>
.page {
    padding: 1rem;
}
</style>
