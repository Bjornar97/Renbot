<script setup lang="ts">
import { Creator } from "@/types/Creator";
import { mdiTwitch, mdiYoutube } from "@mdi/js";

const props = defineProps<{
    participant: Creator;
}>();
</script>

<template>
    <div class="participant">
        <VAvatar class="avatar" :image="participant.image_url"></VAvatar>
        <p class="name">{{ participant.name }}</p>

        <VBtn
            v-if="participant.twitch_url"
            :href="participant.twitch_url"
            class="watch"
            :prepend-icon="mdiTwitch"
            color="grey-darken-4"
            >Watch</VBtn
        >
        <VBtn
            v-else-if="participant.youtube_url"
            :href="participant.youtube_url"
            class="watch"
            :prepend-icon="mdiYoutube"
            color="grey-darken-4"
        >
            Watch
        </VBtn>
    </div>
</template>

<style scoped>
.participant {
    display: grid;
    grid-template-columns: max-content 1fr;
    grid-template-areas:
        "avatar name"
        "watch watch";
    align-items: center;
    gap: 0.5rem 1rem;
}

.avatar {
    grid-area: avatar;
}

.name {
    grid-area: name;
    font-size: 1.5rem;
    word-break: break-word;
    hyphens: auto;
    line-height: 1;
}

.watch {
    grid-area: watch;
}
</style>
