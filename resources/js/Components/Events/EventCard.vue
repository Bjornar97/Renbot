<script setup lang="ts">
import { Event } from "@/types/Event";
import { Link } from "@inertiajs/vue3";
import {
    mdiCalendar,
    mdiCharity,
    mdiChevronRight,
    mdiClock,
    mdiCrown,
    mdiTwitch,
} from "@mdi/js";
import dayjs from "dayjs";
import { route } from "ziggy-js";

const props = defineProps<{
    event: Event;
}>();
</script>

<template>
    <VCard class="event" link>
        <Link :href="route('events.show', { event: event.slug })">
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

                <h3 class="text-h5 font-weight-bold text-primary event-title">
                    {{ event.title }}
                </h3>

                <p class="event-description">
                    <template
                        v-if="
                            event.description && event.description.length > 350
                        "
                    >
                        {{ event.description?.slice(0, 300) }}...
                    </template>
                    <template v-else>{{ event.description }}</template>
                </p>

                <div class="event-actions">
                    <VBtn
                        variant="text"
                        color="grey-lighten-2"
                        :prepend-icon="mdiChevronRight"
                        >Details</VBtn
                    >

                    <!-- <VBtn
                                    v-if="userType === 'moderator'"
                                    variant="text"
                                    :prepend-icon="mdiPencil"
                                    >Edit</VBtn
                                > -->
                </div>
            </VCardText>
        </Link>
    </VCard>
</template>

<style scoped>
.event {
    container-type: inline-size;
    border-radius: 0.5rem;
}

.event a {
    text-decoration: none;
    color: rgb(var(--v-theme-on-surface));
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
    gap: 1rem;
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
