<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import ViewerLayout from "@/Layouts/ViewerLayout.vue";
import { computed, h as vueH } from "vue";
import { Page } from "@inertiajs/core";
import { Event } from "@/types/Event";
import dayjs from "dayjs";
import { mdiCalendar, mdiClock, mdiCrown } from "@mdi/js";

defineOptions({
    layout: (h: typeof vueH, page: Page) =>
        h(
            page.props.userType === "moderator"
                ? ModeratorLayout
                : ViewerLayout,
            () => page
        ),
});

const bgColor = computed(() => {
    switch (props.event.type) {
        case "mcc":
            return "deep-orange";
        case "charity-fundraising":
            return "deep-purple";
        case "twitch-rivals":
            return "purple";
        case "other":
            return "red";
    }
});

const props = defineProps<{
    event: Event;
}>();

const mccImageUrl = new URL("../../../images/mcc.png", import.meta.url).href;
</script>

<template>
    <div class="page">
        <VParallax height="30rem" :src="mccImageUrl" scale="0.8">
            <div class="hero">
                <div>
                    <VChip size="x-large" :prepend-icon="mdiCrown" color="amber"
                        >MC Championship</VChip
                    >
                </div>

                <h1 class="text-md-h2 font-weight-medium title text-amber">
                    {{ event.title }}
                </h1>

                <div class="start d-flex ga-2">
                    <VChip size="large" :prepend-icon="mdiCalendar">{{
                        dayjs(event.start).format("LL")
                    }}</VChip>

                    <VChip size="large" :prepend-icon="mdiClock">{{
                        dayjs(event.start).format("LT")
                    }}</VChip>
                </div>
            </div>
        </VParallax>

        <div class="pa-4 mt-4" v-if="event.teams">
            <div v-for="team in event.teams" :key="team.id" class="mb-4">
                {{ team.name }}

                <ul>
                    <li
                        v-for="participant in event.participants?.filter(
                            (p) => p.pivot?.event_team_id === team.id
                        )"
                    >
                        {{ participant.name }}
                    </li>
                </ul>
            </div>
        </div>

        <div class="pa-4 mt-4" v-else>
            <h2 class="text-h4 font-weight-bold">Participants</h2>

            <p v-for="participant in event.participants" :key="participant.id">
                {{ participant.name }}
            </p>
        </div>
    </div>
</template>

<style scoped>
.page {
    min-height: 200vh;
    /* margin-top: 30rem; */
}
.hero {
    display: grid;
    grid-template-columns: 1fr;
    place-content: center;
    gap: 2rem;
    padding-inline: 3rem;
    min-height: 30rem;
    backdrop-filter: blur(3px);
    background-color: hsl(0 0% 10% / 0.9);
}

.title {
    max-width: 70rem;
    text-wrap: balance;
    line-height: 1.3;
}
</style>
