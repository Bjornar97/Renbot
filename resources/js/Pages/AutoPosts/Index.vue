<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { websocket } from "@/echo";
import { AutoPost } from "@/types/AutoPost";
import Info from "@/Pages/AutoPosts/Partials/Info.vue";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    autoPosts: AutoPost[];
}>();

websocket
    .private(`App.Models.AutoPost.2`)
    .listen(".AutoPostUpdated", (e: any) => {
        console.log({ e });
    });
</script>

<template>
    <div class="pa-4">
        <h1 class="mb-4">Auto posts queues</h1>

        <div class="auto-posts">
            <VSheet
                rounded
                class="pa-4"
                v-for="autoPost in autoPosts"
                :key="autoPost.id"
            >
                <Info :autoPost="autoPost"></Info>
            </VSheet>
        </div>
    </div>
</template>

<style>
.auto-posts {
    display: grid;
    gap: 1rem;
}

@media screen and (min-width: 768px) {
    .auto-posts {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 1rem;
    }
}
</style>
