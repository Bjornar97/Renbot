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
        <h1 class="text-md-h1 mb-8 mx-auto text-center">Thank you Rendog</h1>

        <p class="info text-center mx-auto">
            That is all you have to do. You can now leave this site if you want,
            or you can see commands and chat rules. There is a menu to the left.
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
}
</style>
