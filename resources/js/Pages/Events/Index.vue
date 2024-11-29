<script setup lang="ts">
import { h as vueH } from "vue";
import { Page, PageProps } from "@inertiajs/core";
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import ViewerEventLayout from "@/Layouts/ViewerLayout.vue";
import { Event } from "@/types/Event";
import dayjs from "dayjs";
import {
    mdiCalendar,
    mdiCharity,
    mdiChevronRight,
    mdiCircleSmall,
    mdiClock,
    mdiCrown,
    mdiPencil,
    mdiTwitch,
} from "@mdi/js";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps<{
    days: { date: string; events: Event[] }[];
    userType: "moderator" | "viewer";
}>();

defineOptions({
    layout: (h: typeof vueH, page: Page) =>
        h(
            page.props.userType === "moderator"
                ? ModeratorLayout
                : ViewerEventLayout,
            () => page
        ),
});

const goToEvent = (event: Event) => {};
</script>

<template>
    <div class="pa-4 events-page">
        <h1 class="text-h2">Events</h1>

        <div class="events-list">
            <template v-for="day in days" :key="day.date">
                <div class="date-heading mt-4">
                    <h2>
                        {{ dayjs(day.date).format("LL") }}
                    </h2>
                    <VIcon
                        class="date-heading-dot"
                        size="2rem"
                        :icon="mdiCircleSmall"
                    ></VIcon>
                    <p class="text-medium-emphasis">
                        {{ dayjs(day.date).format("dddd") }}
                    </p>
                </div>

                <VCard
                    v-for="event in day.events"
                    :key="event.id"
                    class="event"
                    link
                >
                    <Link :href="route('events.show', { event: event.id })">
                        <VCardText is="article" class="event-article">
                            <div class="event-start-date">
                                <VChip :prepend-icon="mdiCalendar">{{
                                    dayjs(event.start).format("LL")
                                }}</VChip>
                            </div>
                            <div class="event-start-time">
                                <VChip :prepend-icon="mdiClock">{{
                                    dayjs(event.start).format("LT")
                                }}</VChip>
                            </div>

                            <div class="event-type">
                                <VChip
                                    v-if="event.type === 'mcc'"
                                    color="amber-darken-2"
                                    :prepend-icon="mdiCrown"
                                    >MC Championship</VChip
                                >
                                <VChip
                                    v-if="event.type === 'charity-fundraising'"
                                    color="deep-purple-lighten-4"
                                    :prepend-icon="mdiCharity"
                                    >Charity Fundraising</VChip
                                >
                                <VChip
                                    v-if="event.type === 'twitch-rivals'"
                                    color="purple-lighten-4"
                                    :prepend-icon="mdiTwitch"
                                    >Twitch Rivals</VChip
                                >
                            </div>

                            <h3
                                class="text-h5 font-weight-bold text-primary event-title"
                            >
                                {{ event.title }}
                            </h3>

                            <p class="event-description">
                                <template
                                    v-if="
                                        event.description &&
                                        event.description.length > 350
                                    "
                                >
                                    {{ event.description?.slice(0, 300) }}...
                                </template>
                                <template v-else>{{
                                    event.description
                                }}</template>
                            </p>

                            <div class="event-actions">
                                <VBtn
                                    variant="text"
                                    color="grey-lighten-2"
                                    :prepend-icon="mdiChevronRight"
                                    >Details</VBtn
                                >

                                <VBtn
                                    v-if="userType === 'moderator'"
                                    variant="text"
                                    :prepend-icon="mdiPencil"
                                    >Edit</VBtn
                                >
                            </div>
                        </VCardText>
                    </Link>
                </VCard>
            </template>
        </div>
    </div>
</template>

<style scoped>
.events-page {
    container-type: inline-size;
}

.events-list {
    display: grid;
    gap: 1rem;
}

.events-list a {
    text-decoration: none;
    color: initial;
}

.date-heading {
    grid-column: 1/-1;
    display: grid;
    align-items: center;
}

.date-heading :is(h2, p) {
    font-size: 2.125em;
}

.date-heading-dot {
    display: none;
}

.event {
    container-type: inline-size;
    border-radius: 0.5rem;
}

.event-article {
    display: grid;
    grid-template-columns: max-content max-content 1fr;
    grid-template-areas:
        "start-date start-time ."
        "type type type"
        "title title title"
        "description description description"
        "actions actions actions";
    height: 100%;
    gap: 0.5rem;
    cursor: pointer;
}

.event-start-date {
    grid-area: start-date;
}

.event-start-time {
    grid-area: start-time;
}

.event-type {
    grid-area: type;
}

.event-title {
    grid-area: title;
}

.event-description {
    grid-area: description;
}

.event-actions {
    grid-area: actions;
    display: flex;
    justify-content: space-between;
}

@container (min-width: 30rem) {
    .events-list {
        grid-template-columns: repeat(auto-fill, minmax(30rem, 1fr));
    }

    .date-heading {
        font-size: 1rem;
        grid-template-columns: max-content max-content 1fr;
    }

    .date-heading-dot {
        display: initial;
    }

    .event-article {
        grid-template-columns: max-content max-content 1fr max-content;
        grid-template-rows: max-content 1fr max-content;
        grid-template-areas:
            "start-date start-time . type"
            "title title title title"
            "description description description description"
            "actions actions actions actions";
    }
}
</style>
