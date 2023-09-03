<script setup lang="ts">
import { computed } from "vue";

const severity = defineModel<number>("severity", { required: true });
const punishReason = defineModel<string>("punishReason", { required: true });

const props = defineProps<{
    errors: {
        severity?: string;
        punish_reason?: string;
    };
}>();

const severityColor = computed(() => {
    let color = "success";
    if (severity.value > 4) {
        color = "warning";
    }

    if (severity.value > 7) {
        color = "error";
    }

    return color;
});
</script>

<template>
    <div>
        <p class="text-caption">Punish severity</p>

        <VSlider
            class="mb-8 mx-0"
            v-model="severity"
            :color="severityColor"
            show-ticks="always"
            :step="1"
            min="1"
            max="10"
            :error-messages="errors.severity"
            messages="How hard should the chatter be punished. 1 will timeout 10 seconds first time, 5 will timeout 120 seconds first time, 10 will insta-ban."
            :thumb-label="true"
        ></VSlider>

        <VTextField
            v-model="punishReason"
            :error-messages="errors.punish_reason"
            label="Punish reason"
        ></VTextField>
    </div>
</template>
