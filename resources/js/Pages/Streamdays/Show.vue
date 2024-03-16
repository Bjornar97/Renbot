<script setup lang="ts">
import { Streamday } from "@/types/Streamday";
import RendogLogo from "../../../images/rendog-logo.png";
import { computed } from "vue";
import dayjs from "dayjs";
import { StreamdaySlot } from "@/types/StreamdaySlot";
import { mdiAccount, mdiTwitch, mdiYoutube } from "@mdi/js";

const props = defineProps<{
    streamday: Streamday;
}>();

const days = computed(() => {
    const daysArray: { date: string; slots: StreamdaySlot[] }[] = [];

    props.streamday.streamday_slots?.forEach((slot) => {
        const date = dayjs(slot.start_at);

        const dayIndex = daysArray.findIndex(
            (value) => value.date === date.format("YYYY-MM-DD")
        );

        if (dayIndex < 0) {
            daysArray.push({
                date: date.format("YYYY-MM-DD"),
                slots: [slot],
            });
        } else {
            daysArray[dayIndex].slots.push(slot);
        }
    });

    return daysArray;
});
</script>

<template>
    <VApp>
        <VMain>
            <div class="page">
                <img class="logo mb-4" :src="RendogLogo" alt="Rendog logo" />

                <h1 class="mb-8 text-center">Hermitcraft Streamday!</h1>

                <div class="day-list">
                    <div v-for="day in days" :key="day.date">
                        <div class="mb-8">
                            <h2 class="text-center">
                                {{ dayjs(day.date).format("LL") }}
                            </h2>
                            <!-- <VList>
                                <VListItem
                                    v-for="slot in day.slots"
                                    :key="slot.id"
                                    :title="`${dayjs(slot.start_at).format(
                                        'HH:mm'
                                    )} - ${dayjs(slot.end_at).format('HH:mm')}`"
                                    :subtitle="slot.creator.name"
                                    :prepend-avatar="slot.creator.image_url"
                                    :prepend-icon="
                                        !slot.creator.image_url
                                            ? mdiAccount
                                            : ''
                                    "
                                ></VListItem>
                            </VList> -->

                            <VTimeline align="start" side="end">
                                <VTimelineItem
                                    v-for="slot in day.slots"
                                    :key="slot.id"
                                    dot-color="primary"
                                >
                                    <template v-slot:opposite>
                                        <p
                                            class="pt-2 headline font-weight-bold"
                                        >
                                            {{
                                                `${dayjs(slot.start_at).format(
                                                    "HH:mm"
                                                )}`
                                            }}
                                        </p>
                                    </template>
                                    <div class="slot">
                                        <VAvatar class="slot-avatar">
                                            <VImg
                                                max-width="50"
                                                :src="slot.creator.image_url"
                                            ></VImg>
                                        </VAvatar>

                                        <h3 class="slot-name">
                                            {{ slot.creator.name }}
                                        </h3>

                                        <div class="slot-buttons">
                                            <VBtn
                                                :prepend-icon="mdiYoutube"
                                                v-if="slot.creator.youtube_url"
                                                :href="slot.creator.youtube_url"
                                                >Youtube</VBtn
                                            >

                                            <VBtn
                                                color="purple"
                                                :prepend-icon="mdiTwitch"
                                                v-if="slot.creator.twitch_url"
                                                :href="slot.creator.twitch_url"
                                                >Twitch</VBtn
                                            >
                                        </div>
                                    </div>
                                </VTimelineItem>
                            </VTimeline>
                        </div>
                    </div>
                </div>
            </div>
        </VMain>

        <VFooter class="footer">
            <p class="mx-auto">Kind regards from the moderators on RendogTV</p>
        </VFooter>
    </VApp>
</template>

<style scoped>
.page {
    max-width: 110ch;
    margin: auto;
    display: grid;
}

.logo {
    width: 100px;
    justify-self: center;
}

.footer {
    flex-grow: 0;
    padding: 1rem;
}

.day-list {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

.slot {
    display: grid;
    align-items: center;
    gap: 0.5rem;
    grid-template-columns: max-content 1fr;
}

.slot-buttons {
    grid-column: span 2;
}
</style>
