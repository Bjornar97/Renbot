<script setup lang="ts">
import dayjs from "dayjs";
import { computed, onMounted, ref } from "vue";

const props = defineProps<{
    startDate: string | Date;
    endDate?: string | Date;
}>();

const now = ref(dayjs());

onMounted(() => {
    setInterval(() => {
        now.value = dayjs();
    }, 1000);
});

const duration = computed(() => {
    const diffInMs = dayjs(props.startDate).diff(now.value);
    return dayjs.duration(diffInMs);
});

const isLive = computed(() => {
    return dayjs().isAfter(props.startDate) && dayjs().isBefore(props.endDate);
});

const isDone = computed(() => {
    return dayjs().isAfter(props.endDate);
});
</script>

<template>
    <div class="wrapper">
        <div class="is-over" v-if="isDone">
            <p>The event is over</p>
        </div>

        <div class="is-live" v-else-if="isLive">
            <p>Event is live!</p>
            <div>
                <slot></slot>
            </div>
        </div>

        <div class="countdown-grid" v-else>
            <div class="column">
                <p class="duration">{{ duration.days() }}</p>
                <p class="label">Days</p>
            </div>

            <div class="column">
                <p class="colon">:</p>
            </div>

            <div class="column">
                <p class="duration">{{ duration.hours() }}</p>
                <p class="label">Hours</p>
            </div>

            <div class="column">
                <p class="colon">:</p>
            </div>

            <div class="column">
                <p class="duration">{{ duration.minutes() }}</p>
                <p class="label">Minutes</p>
            </div>

            <div class="column">
                <p class="colon">:</p>
            </div>

            <div class="column">
                <p class="duration">{{ duration.seconds() }}</p>
                <p class="label">Seconds</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.wrapper {
    container-type: inline-size;
}

.countdown-grid {
    display: grid;
    grid-template-columns: repeat(7, max-content);
    gap: 1rem;
    justify-content: space-between;
}

.column {
    display: grid;
    justify-items: center;
}

.duration {
    font-size: clamp(1.5rem, 10cqi, 5rem);
}

.label {
    font-size: clamp(1rem, 5cqi, 1.5rem);
}

.colon {
    font-size: clamp(1.5rem, 10cqi, 5rem);
}

.is-live,
.is-over {
    display: grid;
    gap: 1rem;
    font-size: clamp(2rem, 12cqi, 4rem);
}

@container (min-width: 50rem) {
    .is-live,
    .is-over {
        grid-template-columns: 1fr max-content;
    }
}
</style>
