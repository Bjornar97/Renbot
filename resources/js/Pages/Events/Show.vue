<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import ViewerLayout from "@/Layouts/ViewerLayout.vue";
import { computed, h as vueH } from "vue";
import { Page } from "@inertiajs/core";
import { Event } from "@/types/Event";
import dayjs from "dayjs";
import { mdiCalendar, mdiClock, mdiCrown } from "@mdi/js";
import Participant from "@/Components/Events/Participant.vue";

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
    <div class="container">
        <div class="page">
            <VParallax
                class="hero-parallax"
                height="30rem"
                :src="mccImageUrl"
                scale="0.8"
            >
                <div class="hero">
                    <div>
                        <VChip
                            size="x-large"
                            :prepend-icon="mdiCrown"
                            color="amber"
                            >MC Championship</VChip
                        >
                    </div>

                    <h1 class="text-md-h2 font-weight-medium title">
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

            <div class="below-fold">
                <h2 class="teams-header mt-8 mb-4" v-if="event.teams">Teams</h2>
                <div class="teams" v-if="event.teams">
                    <div
                        v-for="team in event.teams.filter((team) =>
                            event.participants?.some(
                                (p) => p.pivot?.event_team_id === team.id
                            )
                        )"
                        :key="team.id"
                        :style="{
                            borderColor: team.color ?? '#777',
                        }"
                        class="mb-4 team"
                    >
                        <VSheet
                            class="team-name"
                            :style="{
                                borderColor: team.color ?? '#777',
                            }"
                        >
                            <h3>
                                {{ team.name }}
                            </h3>
                        </VSheet>

                        <ul class="team-participants">
                            <li
                                class="team-participant"
                                v-for="participant in event.participants?.filter(
                                    (p) => p.pivot?.event_team_id === team.id
                                )"
                            >
                                <Participant
                                    :participant="participant"
                                ></Participant>
                            </li>
                        </ul>
                    </div>
                </div>

                <h2
                    class="participants-header mt-8 mb-4 text-h4 font-weight-bold"
                >
                    {{ event.teams ? "All" : "" }} Participants
                </h2>

                <div class="participants">
                    <Participant
                        v-for="participant in event.participants"
                        :key="participant.id"
                        :participant="participant"
                    ></Participant>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.container {
    container-type: inline-size;
}

.page {
    --page-padding-inline: 1rem;
    margin-bottom: 10rem;
}

.below-fold {
    display: grid;
    gap: 0.5rem 2rem;
    padding-inline: var(--page-padding-inline);
}

@container (min-width: 60rem) {
    .below-fold {
        grid-template-areas:
            "participants-header"
            "participants";
    }
    .below-fold:has(.teams) {
        grid-template-columns: 1fr auto;
        grid-template-areas:
            "hero hero"
            "teams-header participants-header"
            "teams participants";
    }

    .hero-parallax {
        grid-area: hero;
    }

    .teams-header {
        grid-area: teams-header;
    }

    .participants-header {
        grid-area: participants-header;
    }

    .teams {
        padding-inline-end: 0;
        grid-area: teams;
    }

    .participants {
        grid-area: participants;
    }
}

.hero {
    display: grid;
    grid-template-columns: 1fr;
    place-content: center;
    gap: 2rem;
    min-height: 30rem;
    backdrop-filter: blur(5px);
    background-color: hsl(0 0% 10% / 0.95);
    padding-inline: var(--page-padding-inline);
}

.title {
    max-width: 33ch;
    text-wrap: balance;
    line-height: 1.3;
}

.teams-header {
    font-size: 2rem;
}

.teams {
    display: grid;
    gap: 2rem;
    height: fit-content;
}

.team {
    position: relative;
    border-width: 2px;
    border-radius: 1rem;
    border-style: solid;
    padding: 2rem 1rem;
    box-shadow: 0px 0px 5px #ccc;
}

.team-name {
    position: absolute;
    top: -1.25em;
    left: 1.5em;
    padding: 0.25rem 0.75em;
    border-radius: 1rem;
    border-width: 1px;
    border-style: solid;
    box-shadow: 0px 0px 5px #ccc;
}

.team-participants {
    list-style: none;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));
    gap: 2rem;
}

.participants {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));
}

.below-fold:has(.teams) .participants {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

@container (min-width: 60rem) {
    .page {
        --page-padding-inline: 3rem;
    }

    .teams {
        grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
    }
}
</style>
