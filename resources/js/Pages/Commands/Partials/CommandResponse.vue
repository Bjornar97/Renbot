<script setup lang="ts">
import type { CommandType } from "@/types/CommandType";

const command = defineModel<string>("command");
const response = defineModel<string>("response");
const prependSender = defineModel<boolean>("prependSender");

const props = defineProps<{
    errors: {
        command?: string;
        response?: string;
    };
    type: CommandType;
}>();
</script>

<template>
    <div>
        <VTextField
            v-model="command"
            label="Command"
            class="mb-4"
            :error-messages="props.errors.command"
        >
            <template #prepend-inner>!</template>
        </VTextField>

        <VTextarea
            v-model="response"
            label="Response"
            :error-messages="props.errors.response"
            :hint="
                props.type === 'special'
                    ? 'The response might get overriden by the action'
                    : ''
            "
        ></VTextarea>

        <VSwitch
            v-if="type !== 'punishable'"
            v-model="prependSender"
            color="primary"
            label="Prepend username"
            messages="Prepend the user's name to the response if no user is tagged in command."
        ></VSwitch>
    </div>
</template>
