<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { ref } from "vue";
import { router } from "@inertiajs/core";
import route from "ziggy-js";

defineOptions({
    layout: ModeratorLayout,
});

const restartLoading = ref(false);

const restart = () => {
    restartLoading.value = true;

    router.post(
        route("bot.restart"),
        {},
        {
            onFinish: () => {
                restartLoading.value = false;
            },
        }
    );
};
</script>

<template>
    <div class="pa-2 pa-md-4">
        <h1>Bot</h1>

        <p class="py-4">
            If there is a problem with the bot, a restart can often solve it.
        </p>

        <VBtn
            color="primary-darken-2"
            @click="restart"
            :loading="restartLoading"
            >Restart the bot</VBtn
        >
    </div>
</template>

<style scoped></style>
