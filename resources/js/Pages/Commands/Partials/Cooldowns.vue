<script setup lang="ts">
import type { UsableBy } from "@/types/UsableBy";

const cooldown = defineModel<number>("cooldown");
const globalCooldown = defineModel<number>("globalCooldown");

const props = defineProps<{
    errors: {
        cooldown?: string;
        global_cooldown?: string;
    };
    usableBy: UsableBy;
}>();
</script>

<template>
    <div>
        <VTextField
            class="mb-4"
            v-if="usableBy !== 'moderators'"
            type="number"
            v-model="cooldown"
            label="Cooldown"
            hint="If the same person runs this command twice within this duration, the second will not be allowed. Does not apply to moderators."
            :error-messages="errors.cooldown"
        ></VTextField>

        <VTextField
            class="mb-4"
            v-if="usableBy !== 'moderators'"
            type="number"
            v-model="globalCooldown"
            label="Global cooldown"
            hint="If any person runs this command within this duration after previous time it was run by any person, it will not be allowed. Does not apply to moderators."
            :error-messages="errors.global_cooldown"
        ></VTextField>
    </div>
</template>
