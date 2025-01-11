<script setup lang="ts">
import { h as vueH } from "vue";
import { Page } from "@inertiajs/core";
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import ViewerEventLayout from "@/Layouts/ViewerLayout.vue";
import { Event } from "@/types/Event";
import dayjs from "dayjs";
import { mdiCircleSmall } from "@mdi/js";
import EventCard from "@/Components/Events/EventCard.vue";

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
</script>

<template>
    <div class="pa-4 events-page">
        <h1 class="text-h2 mb-4">Events</h1>

        <p v-if="days.length === 0">
            No upcoming events registered in Renbot. Currently only MCC events
            show up here.
        </p>

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

                <EventCard
                    v-for="event in day.events"
                    :key="event.id"
                    :event="event"
                ></EventCard>
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
    color: rgb(var(--v-theme-on-surface));
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
}
</style>
