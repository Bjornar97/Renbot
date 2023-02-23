<script setup lang="ts">
import type { Command } from "@/types/Command";
import ModeratorIcon from "../../../images/icons/moderator.png";
import SubscriberIcon from "../../../images/icons/subscriber.png";

const props = defineProps<{
    command: Command;
}>();
</script>

<template>
    <template v-if="command.usable_by === 'moderators'">
        <VImg width="1.2rem" :src="ModeratorIcon" alt="Moderator icon" />
    </template>
    <template v-else-if="command.usable_by === 'subscribers'">
        <VImg width="1.2rem" :src="SubscriberIcon" alt="Moderator icon" />
    </template>
</template>
