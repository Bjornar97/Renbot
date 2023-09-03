<script setup lang="ts">
import type { CommandAction } from "@/types/CommandAction";

const action = defineModel<string>("action");

const props = defineProps<{
    errors: {
        action?: string;
    };
    actions: CommandAction[];
}>();
</script>

<template>
    <VAutocomplete
        v-model="action"
        class="mb-6"
        :items="actions"
        item-title="title"
        item-value="action"
        label="Action"
        hint="The action to run when this command is excecuted"
        persistent-hint
        :error-messages="errors.action"
    ></VAutocomplete>
</template>
