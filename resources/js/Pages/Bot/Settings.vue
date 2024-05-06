<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import type { Command } from "@/types/Command";
import { useForm } from "@inertiajs/vue3";
import { mdiContentSave, mdiRestore } from "@mdi/js";
import { computed } from "vue";
import { route } from "ziggy-js";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    announceRestart: boolean;
    punishableBansEnabled: boolean;
    punishableTimeoutsEnabled: boolean;
    autoCapsEnabled: boolean;
    autoBanBots: boolean;
    punishDebugEnabled: boolean;
    punishableCommands: Command[];
    autoCapsCommand: number | null;
    autoCapsTotalCapsThreshold: number;
    autoCapsTotalLengthThreshold: number;
    autoCapsWordCapsThreshold: number;
    autoCapsWordLengthThreshold: number;
}>();

const form = useForm({
    announceRestart: props.announceRestart,
    punishableBansEnabled: props.punishableBansEnabled,
    punishableTimeoutsEnabled: props.punishableTimeoutsEnabled,
    autoCapsEnabled: props.autoCapsEnabled,
    autoCapsCommand: props.autoCapsCommand,
    autoBanBots: props.autoBanBots,
    punishDebugEnabled: props.punishDebugEnabled,
    autoCapsTotalCapsThreshold: props.autoCapsTotalCapsThreshold,
    autoCapsTotalLengthThreshold: props.autoCapsTotalLengthThreshold,
    autoCapsWordCapsThreshold: props.autoCapsWordCapsThreshold,
    autoCapsWordLengthThreshold: props.autoCapsWordLengthThreshold,
});
</script>

<template>
    <div class="pa-2 pa-md-4">
        <div class="mb-8">
            <h1 class="text-h3 font-weight-bold mb-2">Bot Settings</h1>
            <p>Settings for the bot</p>
        </div>

        <div class="sections">
            <section class="general">
                <h2 class="text-h5">General</h2>

                <VSwitch
                    v-model="form.announceRestart"
                    class="mb-4"
                    label="Announce restart"
                    :disabled="form.processing"
                    :error-messages="form.errors.announceRestart"
                    color="primary"
                    messages="If enabled, the bot will tell chat that it is restarting"
                ></VSwitch>

                <VSwitch
                    v-model="form.autoBanBots"
                    class="mb-4"
                    label="Auto ban bots"
                    :disabled="form.processing"
                    :error-messages="form.errors.autoBanBots"
                    color="primary"
                    messages="If enabled, Renbot will get bots from Twitch Insights and automatically ban all bots that are in Rendogs chat every 30 minutes, excluding some bots (RenTheBot, Moobot, StreamElements)"
                ></VSwitch>
            </section>

            <section class="punishable">
                <h2 class="text-h5">Punishables</h2>

                <VSwitch
                    v-model="form.punishableBansEnabled"
                    class="mb-4"
                    label="Punishable Bans"
                    :disabled="form.processing"
                    :error-messages="form.errors.punishableBansEnabled"
                    color="primary"
                    messages="If enabled, a user will get automatically get banned if timeout reaches 30 000 seconds"
                ></VSwitch>

                <VDivider></VDivider>

                <VSwitch
                    v-model="form.punishableTimeoutsEnabled"
                    class="mb-4"
                    label="Punishable Timeouts"
                    :disabled="form.processing"
                    :error-messages="form.errors.punishableTimeoutsEnabled"
                    color="primary"
                    messages="If enabled, a user will get timed out when using punishable command"
                ></VSwitch>

                <VDivider></VDivider>

                <VSwitch
                    v-model="form.punishDebugEnabled"
                    class="mb-4"
                    label="Punish Debug"
                    :disabled="form.processing"
                    :error-messages="form.errors.punishDebugEnabled"
                    color="primary"
                    messages="If enabled, no users will get timed out or banned, but the bot says in chat what it would do if debug was disabled"
                ></VSwitch>
            </section>

            <section class="auto-caps">
                <h2 class="text-h5">Automatic caps punishment</h2>

                <VSwitch
                    v-model="form.autoCapsEnabled"
                    class="mb-4"
                    label="Enabled"
                    :disabled="form.processing"
                    :error-messages="form.errors.autoCapsEnabled"
                    color="primary"
                    messages="If enabled, the bot will punish users automatically for using too much caps"
                ></VSwitch>

                <template v-if="form.autoCapsEnabled">
                    <VDivider class="mb-4"></VDivider>

                    <VAutocomplete
                        v-model="form.autoCapsCommand"
                        :items="punishableCommands"
                        label="Command to use for response"
                        :disabled="form.processing"
                        class="mb-4"
                        hint="The command that will be used for response and calculating timeout length"
                        persistent-hint
                        item-value="id"
                        item-title="command"
                        clearable
                        :error-messages="form.errors.autoCapsCommand"
                    >
                        <template #prepend-inner>!</template>
                        <template #item="{ props, item }">
                            <VListItem
                                v-bind="props"
                                :key="item.raw.id"
                                :title="`!${item.raw.command}`"
                                :subtitle="item.raw.response"
                            >
                            </VListItem>
                        </template>
                    </VAutocomplete>

                    <VLabel>Minimim caps</VLabel>
                    <VSlider
                        v-model="form.autoCapsTotalLengthThreshold"
                        class="mx-0 mb-4"
                        min="0"
                        max="30"
                        step="1"
                        :disabled="form.processing"
                        :error-messages="
                            form.errors.autoCapsTotalLengthThreshold
                        "
                        color="primary"
                        messages="The minimum number of caps letters in message before punishment"
                        thumb-label="always"
                        thumb-color="primary"
                    >
                    </VSlider>

                    <VDivider class="mb-4"></VDivider>

                    <VLabel>Caps percentage</VLabel>
                    <VSlider
                        v-model="form.autoCapsTotalCapsThreshold"
                        class="mx-0 mb-4"
                        min="0"
                        max="1"
                        step="0.01"
                        :disabled="form.processing"
                        :error-messages="form.errors.autoCapsTotalCapsThreshold"
                        color="primary"
                        messages="The percentage of the whole message that has to be caps for punishment"
                        thumb-label="always"
                        thumb-color="primary"
                    >
                        <template #thumb-label>
                            {{
                                Math.round(
                                    form.autoCapsTotalCapsThreshold * 100
                                )
                            }}%
                        </template>
                    </VSlider>

                    <VDivider class="mb-4"></VDivider>

                    <VLabel>Word caps</VLabel>
                    <VSlider
                        v-model="form.autoCapsWordLengthThreshold"
                        class="mx-0 mb-4"
                        min="0"
                        max="30"
                        step="1"
                        :disabled="form.processing"
                        :error-messages="
                            form.errors.autoCapsWordLengthThreshold
                        "
                        color="primary"
                        messages="The minimum number of caps in a word for punishment"
                        thumb-label="always"
                        thumb-color="primary"
                    >
                    </VSlider>

                    <VDivider class="mb-4"></VDivider>

                    <VLabel>Word caps percentage</VLabel>
                    <VSlider
                        v-model="form.autoCapsWordCapsThreshold"
                        class="mx-0 mb-4"
                        min="0"
                        max="1"
                        step="0.01"
                        :disabled="form.processing"
                        :error-messages="form.errors.autoCapsWordCapsThreshold"
                        color="primary"
                        messages="The percentage of a word that has to be caps for punishment"
                        thumb-label="always"
                        thumb-color="primary"
                    >
                        <template #thumb-label>
                            {{
                                Math.round(
                                    form.autoCapsWordCapsThreshold * 100
                                )
                            }}%
                        </template>
                    </VSlider>
                </template>
            </section>
        </div>

        <div class="buttons">
            <VBtn
                type="submit"
                color="success"
                :loading="form.processing"
                :prepend-icon="mdiContentSave"
                @click="form.put(route('bot.settings.update'))"
            >
                Save
            </VBtn>

            <VBtn
                type="button"
                color="warning"
                :prepend-icon="mdiRestore"
                :loading="form.processing"
                @click="form.reset()"
                :disabled="form.processing"
            >
                Reset changes
            </VBtn>
        </div>
    </div>
</template>

<style scoped>
@media screen and (min-width: 768px) {
    .sections {
        display: grid;
        gap: 2rem;
        grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
        grid-template-areas:
            "general auto-caps"
            "punishable auto-caps";
    }

    .general {
        grid-area: general;
    }

    .auto-caps {
        grid-area: auto-caps;
    }

    .punishable {
        grid-area: punishable;
    }
}

.buttons {
    display: flex;
    justify-content: space-between;
}
</style>
