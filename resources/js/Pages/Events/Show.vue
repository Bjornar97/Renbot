<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import ViewerLayout from "@/Layouts/ViewerLayout.vue";
import { computed, h as vueH } from "vue";
import { Page } from "@inertiajs/core";
import { Event } from "@/types/Event";
import dayjs from "dayjs";
import {
    mdiCalendar,
    mdiClock,
    mdiCrown,
    mdiStarCircle,
    mdiTwitch,
} from "@mdi/js";
import Participant from "@/Components/Events/Participant.vue";
import Countdown from "@/Components/Events/Countdown.vue";
import { EventTeam } from "@/types/EventTeam";

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

const generateMultiStreamLink = (team: EventTeam) => {
    let url = "https://multistre.am/";

    props.event.participants
        ?.filter(
            (creator) =>
                creator.twitch_url && creator.pivot?.event_team_id === team.id
        )
        .forEach((creator) => {
            const twitchUsername = creator.twitch_url?.replace(
                "https://twitch.tv/",
                ""
            );
            url += `${twitchUsername}/`;
        });

    url += "layout10/";

    return url;
};
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

                    <div v-if="event.event_url" class="event-page">
                        <VBtn
                            :color="bgColor"
                            :href="event.event_url"
                            target="_blank"
                            :prepend-icon="mdiStarCircle"
                            >Official event page</VBtn
                        >
                    </div>
                </div>
            </VParallax>

            <div class="below-fold">
                <div class="countdown">
                    <Countdown :start-date="event.start" :end-date="event.end">
                        <VBtn :prepend-icon="mdiTwitch" size="x-large"
                            >Watch Rendog</VBtn
                        >
                    </Countdown>
                    <VDivider></VDivider>
                </div>

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

                        <div class="team-image">
                            <img v-if="team.image_url" :src="team.image_url" />
                        </div>

                        <ul class="participants">
                            <li
                                class="participant"
                                v-for="participant in event.participants?.filter(
                                    (p) => p.pivot?.event_team_id === team.id
                                )"
                            >
                                <Participant
                                    :participant="participant"
                                ></Participant>
                            </li>
                        </ul>

                        <VBtn
                            v-if="
                                event.participants?.some(
                                    (creator) =>
                                        creator.twitch_url &&
                                        creator.pivot?.event_team_id === team.id
                                )
                            "
                            class="multistream"
                            color="grey-darken-3"
                            :prepend-icon="mdiTwitch"
                            :href="generateMultiStreamLink(team)"
                            target="_blank"
                            >Multistream</VBtn
                        >
                    </div>
                </div>

                <h2
                    class="participants-header mt-8 mb-4 text-h4 font-weight-bold"
                >
                    {{ event.teams ? "All" : "" }} Participants
                </h2>

                <ul class="participants">
                    <li
                        class="participant"
                        v-for="participant in event.participants"
                        :key="participant.id"
                    >
                        <Participant :participant="participant"></Participant>
                    </li>
                </ul>
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
    gap: 0.5rem 4rem;
    padding-inline: var(--page-padding-inline);
}

.countdown {
    display: grid;
    gap: 2rem;
    margin-top: 1rem;
}

@container (min-width: 60rem) {
    .below-fold {
        grid-template-areas:
            "hero"
            "countdown"
            "participants-header"
            "participants";
    }
    .below-fold:has(.teams) {
        grid-template-columns: 1fr auto;
        grid-template-areas:
            "hero hero"
            "countdown countdown"
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

    .countdown {
        grid-area: countdown;
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
    gap: 4rem;
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

.multistream {
    width: 100%;
    margin-top: 3rem;
    z-index: 1;
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
    z-index: 2;
}

.team-image {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50%;
    right: 50%;
    transform: translateX(-50%);
    height: 100%;
    width: 100%;

    text-align: center;
    z-index: 1;
    filter: blur(10px) brightness(0.3) opacity(0.6);
}

.team-image img {
    height: 100%;
    width: 100%;
    object-fit: contain;
    padding: 1rem;
}

.participants {
    position: relative;
    list-style: none;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));
    place-items: center;
    gap: 3rem 4rem;
    z-index: 2;
}

.participant {
    width: 100%;
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
