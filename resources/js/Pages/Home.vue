<script setup lang="ts">
import { computed, h as vueH } from "vue";
import { Page } from "@inertiajs/core";
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import ViewerLayout from "@/Layouts/ViewerLayout.vue";
import { Link } from "@inertiajs/vue3";
import { mdiScaleBalance, mdiTwitch } from "@mdi/js";
import { route } from "ziggy-js";
import { Event } from "@/types/Event";
import EventCard from "@/Components/Events/EventCard.vue";

defineOptions({
    layout: (h: typeof vueH, page: Page) =>
        h(
            page.props.userType === "moderator" ||
                page.props.userType === "rendog"
                ? ModeratorLayout
                : ViewerLayout,
            () => page
        ),
});

const props = defineProps<{
    events: Event[];
}>();
</script>

<template>
    <div class="page">
        <header class="header">
            <h1>Welcome to Renbot!</h1>

            <a
                href="https://www.twitch.tv/rendogtv"
                target="_blank"
                class="twitch-link"
            >
                <VBtn :prepend-icon="mdiTwitch" size="x-large"
                    >Watch Rendog</VBtn
                >
            </a>
        </header>

        <div>
            <h2>Chat Rules</h2>
            <p class="mb-2">
                In order to keep the chat family friendly and welcoming, we have
                some rules. Please read them carefully and follow them while
                chatting in Rendog's Twitch channel
            </p>
            <Link :href="route('rules')">
                <VBtn :prepend-icon="mdiScaleBalance">Read the rules</VBtn>
            </Link>
        </div>

        <div v-if="events.length > 0" class="events">
            <h2 class="mb-2">
                Upcoming event{{ events.length !== 1 ? "s" : "" }}
            </h2>

            <div class="events-list">
                <EventCard
                    v-for="event in events"
                    :key="event.id"
                    :event="event"
                ></EventCard>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page {
    container-type: inline-size;
    display: grid;
    gap: 2rem;
    padding: 0.5rem;
    max-width: 70rem;
    margin: auto;
}

h1 {
    font-size: clamp(2rem, 6.5cqi, 5rem);
}

h2 {
    font-size: clamp(1.2rem, 3cqi, 3rem);
}

p {
    max-width: 75ch;
}

.header {
    display: grid;
    gap: 1rem;
    align-items: center;
}

.events-list {
    display: grid;
}

@media screen and (min-width: 40rem) {
    .page {
        padding: 2rem;
    }
}

@container (min-width: 40rem) {
    .header {
        grid-template-columns: 1fr max-content;
    }

    .events-list {
        grid-template-columns: repeat(auto-fill, minmax(30rem, 1fr));
    }
}
</style>
