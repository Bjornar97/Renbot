<script setup lang="ts">
import TextAreaWithTags from "@/Components/Commands/TextAreaWithTags.vue";
import type { CommandType } from "@/types/CommandType";
import { mdiPlus, mdiTrashCan } from "@mdi/js";

const command = defineModel<string>("command");
const response = defineModel<string>("response");
const prependSender = defineModel<boolean>("prependSender");
const aliases = defineModel<string[]>("aliases", { default: [] });

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
            :error-messages="props.errors.command"
        >
            <template #prepend-inner>!</template>
        </VTextField>

        <div>
            <div v-for="(alias, index) in aliases" class="d-flex">
                <VTextField v-model="aliases[index]" label="Alias">
                    <template #prepend-inner>!</template></VTextField
                >
                <VBtn
                    class="ml-2 mt-1"
                    :icon="mdiTrashCan"
                    @click="aliases.splice(index, 1)"
                ></VBtn>
            </div>
        </div>

        <VBtn
            variant="text"
            class="mb-8"
            :prepend-icon="mdiPlus"
            @click="aliases.push('')"
            >Add alias</VBtn
        >

        <TextAreaWithTags
            label="Response"
            v-model="response"
            :available-tags="[
                {
                    type: 'special',
                    key: 'random_number',
                    title: 'Random number',
                },
            ]"
        ></TextAreaWithTags>

        <!-- <VTextarea
            v-model="response"
            label="Response"
            :error-messages="props.errors.response"
            :hint="
                props.type === 'special'
                    ? 'The response might get overriden by the action'
                    : ''
            "
        ></VTextarea> -->

        <VSwitch
            v-if="type !== 'punishable'"
            v-model="prependSender"
            color="primary"
            label="Prepend username"
            messages="Prepend the user's name to the response if no user is tagged in command."
        ></VSwitch>
    </div>
</template>
