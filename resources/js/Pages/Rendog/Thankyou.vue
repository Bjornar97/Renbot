<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import RendogLogo from "../../../images/rendog-logo.png";

defineOptions({
    layout: ModeratorLayout,
});
</script>

<template>
    <div class="page">
        <img class="mx-auto mb-4 mt-12" :src="RendogLogo" alt="Rendog logo" />
        <h1 class="text-md-h1 mb-8 mx-auto text-center">Welcome Rendog!</h1>

        <p class="info text-center mx-auto mb-6">
            This is the portal that all moderators can use. You can create and
            edit commands, change the rules and change some settings in the bot.
            There is a menu to the left on desktop, and top left and bottom on
            mobile.
        </p>

        <p class="info text-center mx-auto">
            For a list of normal commands, you can click "Regular commands".
            "Punishable commands" are commands that when you use them, you have
            to specify a username. That username will get timed out and sent a
            message that they did something wrong.
        </p>
    </div>
</template>

<style scoped>
.page {
    display: grid;
    place-content: center;
    padding-inline: 1rem;
}

.info {
    max-width: 70ch;
    line-height: 1.75;
}
</style>
