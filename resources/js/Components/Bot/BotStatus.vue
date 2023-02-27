<script setup lang="ts">
import { BotStatus } from "@/types/BotStatus";
import {
    mdiAlertCircle,
    mdiRobotConfused,
    mdiRobotDead,
    mdiRobotExcited,
    mdiRobotOff,
} from "@mdi/js";
import { computed } from "vue";

const props = defineProps<{
    status: BotStatus;
}>();

const color = computed(() => {
    switch (props.status) {
        case BotStatus.RUNNING:
            return "success";
        case BotStatus.STOPPED:
            return "warning";
        case BotStatus.FAILED:
            return "error";
        case BotStatus.ERROR:
            return "error";
        case BotStatus.UNKNOWN:
            return "grey";

        default:
            return "grey";
    }
});

const icon = computed(() => {
    switch (props.status) {
        case BotStatus.RUNNING:
            return mdiRobotExcited;
        case BotStatus.STOPPED:
            return mdiRobotOff;
        case BotStatus.FAILED:
            return mdiRobotDead;
        case BotStatus.ERROR:
            return mdiAlertCircle;
        case BotStatus.UNKNOWN:
            return mdiRobotConfused;

        default:
            return mdiRobotConfused;
    }
});

const text = computed(() => {
    switch (props.status) {
        case BotStatus.RUNNING:
            return "Running";
        case BotStatus.STOPPED:
            return "Turned off";
        case BotStatus.FAILED:
            return "Has crashed!";
        case BotStatus.ERROR:
            return "Error getting status";
        case BotStatus.UNKNOWN:
            return "Unknown status";

        default:
            return mdiRobotConfused;
    }
});
</script>

<template>
    <VChip :color="color" :prepend-icon="icon">{{ text }}</VChip>
</template>
