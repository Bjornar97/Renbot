<script setup lang="ts">
import type { CommandType } from "@/types/CommandType";
import type { UsableBy } from "@/types/UsableBy";
import { mdiAlphaEBox } from "@mdi/js";
import { useDisplay } from "vuetify/lib/framework.mjs";

const usableBy = defineModel<UsableBy>("usableBy");

const props = defineProps<{
    type: CommandType;
    errors: {
        usable_by?: string;
    };
}>();

const { smAndUp } = useDisplay();
</script>

<template>
    <div>
        <VBtnToggle
            :elevation="2"
            v-model="usableBy"
            divided
            class="mb-8"
            :disabled="type === 'punishable'"
        >
            <VBtn
                color="#01AD02"
                value="moderators"
                :stacked="!smAndUp"
                :size="smAndUp ? 'large' : 'small'"
            >
                <template #prepend>
                    <img
                        src="../../../../images/icons/moderator.png"
                        alt="Moderator icon"
                    />
                </template>
                <span v-if="smAndUp">Moderators only</span>
                <span v-else>Moderators</span>
            </VBtn>

            <VBtn
                color="purple-darken-4"
                value="subscribers"
                :size="smAndUp ? 'large' : 'small'"
                :stacked="!smAndUp"
            >
                <template #prepend>
                    <img
                        src="../../../../images/icons/subscriber.png"
                        alt="Moderator icon"
                    />
                </template>
                <span v-if="smAndUp">Subscribers</span>
                <span v-else>Subs</span>
            </VBtn>

            <VBtn
                color="red-darken-4"
                value="everyone"
                :size="smAndUp ? 'large' : 'small'"
                :prepend-icon="mdiAlphaEBox"
                :stacked="!smAndUp"
            >
                Everyone
            </VBtn>
        </VBtnToggle>

        <p class="text-red mb-6" v-if="errors.usable_by">
            {{ errors.usable_by }}
        </p>
    </div>
</template>
