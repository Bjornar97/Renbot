<script setup lang="ts">
import type { Command } from "@/types/Command";
import { computed } from "vue";

const props = defineProps<{
    command: Command;
}>();

const severityColor = computed(() => {
    let color = "success";
    if (props.command.severity > 4) {
        color = "warning";
    }

    if (props.command.severity > 7) {
        color = "error";
    }

    return color;
});
</script>

<template>
    <VChip :color="severityColor" size="x-large" class="text-h6">
        {{ command.severity }}
    </VChip>
</template>
