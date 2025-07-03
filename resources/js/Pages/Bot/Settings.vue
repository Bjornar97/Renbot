<script setup lang="ts">
import BotStatus from "@/Components/Bot/BotStatus.vue";
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { BotStatus as BotStatusType } from "@/types/BotStatus";
import type { Command } from "@/types/Command";
import { router, useForm } from "@inertiajs/vue3";
import {
    mdiContentSave,
    mdiRestart,
    mdiRestore,
    mdiRobotExcited,
    mdiRobotOff,
} from "@mdi/js";
import { ref } from "vue";
import { route } from "ziggy-js";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    botStatus: BotStatusType;
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
    autoMaxEmotesEnabled: boolean;
    autoMaxEmotesCommand: number | null;
    autoMaxEmotes: number;
}>();

const form = useForm({
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
    autoMaxEmotesEnabled: props.autoMaxEmotesEnabled,
    autoMaxEmotesCommand: props.autoMaxEmotesCommand,
    autoMaxEmotes: props.autoMaxEmotes,
});

const openPanels = ref([0]);

const restartLoading = ref(false);
const switchPowerLoading = ref(false);
const actionsDisabled = ref(false);

const startBot = () => {
    switchPowerLoading.value = true;
    actionsDisabled.value = true;

    router.post(
        route("bot.start"),
        {},
        {
            onFinish: () => {
                switchPowerLoading.value = false;
                actionsDisabled.value = false;
            },
        }
    );
};

const stopBot = () => {
    switchPowerLoading.value = true;
    actionsDisabled.value = true;

    router.post(
        route("bot.stop"),
        {},
        {
            onFinish: () => {
                switchPowerLoading.value = false;
                actionsDisabled.value = false;
            },
        }
    );
};

const restartBot = () => {
    restartLoading.value = true;
    actionsDisabled.value = true;

    router.post(
        route("bot.restart"),
        {},
        {
            onFinish: () => {
                restartLoading.value = false;
                actionsDisabled.value = false;
            },
        }
    );
};
</script>

<template>
    <div class="pa-2 pa-md-4">
        <div class="mb-4">
            <h1 class="text-h3 font-weight-bold mb-2">RenBot</h1>
        </div>

        <div class="mb-4 d-flex ga-2">
            <VBtn
                v-if="botStatus === BotStatusType.STOPPED"
                color="success"
                :prepend-icon="mdiRobotExcited"
                @click="startBot"
                :loading="
                    botStatus === BotStatusType.STOPPED && switchPowerLoading
                "
                :disabled="actionsDisabled"
            >
                Start bot
            </VBtn>
            <VBtn
                v-if="botStatus === BotStatusType.RUNNING"
                color="error"
                :prepend-icon="mdiRobotOff"
                @click="stopBot"
                :loading="
                    botStatus === BotStatusType.RUNNING && switchPowerLoading
                "
                :disabled="actionsDisabled"
            >
                Stop bot
            </VBtn>

            <VBtn
                color="primary-darken-2"
                @click="restartBot"
                :loading="restartLoading"
                :disabled="actionsDisabled"
                :prepend-icon="mdiRestart"
                >Restart</VBtn
            >
        </div>

        <h2>Status</h2>
        <BotStatus class="mb-4" :status="botStatus" size="x-large"></BotStatus>

        <h2 class="mb-2">Settings</h2>

        <div class="sections">
            <section class="general">
                <VExpansionPanels v-model="openPanels">
                    <VExpansionPanel title="General">
                        <template #text>
                            <VSwitch
                                v-model="form.autoBanBots"
                                class="mb-4"
                                label="Auto ban bots"
                                :disabled="form.processing"
                                :error-messages="form.errors.autoBanBots"
                                color="primary"
                                messages="If enabled, Renbot will get bots from Twitch Insights and automatically ban all bots that are in Rendogs chat every 30 minutes, excluding some bots (RenTheBot, Moobot, StreamElements)"
                            ></VSwitch>
                        </template>
                    </VExpansionPanel>

                    <VExpansionPanel title="Punishables">
                        <template #text>
                            <VSwitch
                                v-model="form.punishableBansEnabled"
                                class="mb-4"
                                label="Punishable Bans"
                                :disabled="form.processing"
                                :error-messages="
                                    form.errors.punishableBansEnabled
                                "
                                color="primary"
                                messages="If enabled, a user will get automatically get banned if timeout reaches 30 000 seconds"
                            ></VSwitch>

                            <VDivider></VDivider>

                            <VSwitch
                                v-model="form.punishableTimeoutsEnabled"
                                class="mb-4"
                                label="Punishable Timeouts"
                                :disabled="form.processing"
                                :error-messages="
                                    form.errors.punishableTimeoutsEnabled
                                "
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
                        </template>
                    </VExpansionPanel>
                </VExpansionPanels>
            </section>

            <section class="auto-punishment">
                <VExpansionPanels>
                    <VExpansionPanel title="Auto caps punishment">
                        <template #text>
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
                                    :error-messages="
                                        form.errors.autoCapsCommand
                                    "
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
                                    :error-messages="
                                        form.errors.autoCapsTotalCapsThreshold
                                    "
                                    color="primary"
                                    messages="The percentage of the whole message that has to be caps for punishment"
                                    thumb-label="always"
                                    thumb-color="primary"
                                >
                                    <template #thumb-label>
                                        {{
                                            Math.round(
                                                form.autoCapsTotalCapsThreshold *
                                                    100
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
                                    :error-messages="
                                        form.errors.autoCapsWordCapsThreshold
                                    "
                                    color="primary"
                                    messages="The percentage of a word that has to be caps for punishment"
                                    thumb-label="always"
                                    thumb-color="primary"
                                >
                                    <template #thumb-label>
                                        {{
                                            Math.round(
                                                form.autoCapsWordCapsThreshold *
                                                    100
                                            )
                                        }}%
                                    </template>
                                </VSlider>
                            </template>
                        </template>
                    </VExpansionPanel>

                    <VExpansionPanel title="Auto max emotes punishment">
                        <template #text>
                            <VSwitch
                                v-model="form.autoMaxEmotesEnabled"
                                class="mb-4"
                                label="Enabled"
                                :disabled="form.processing"
                                :error-messages="
                                    form.errors.autoMaxEmotesEnabled
                                "
                                color="primary"
                                messages="If enabled, the bot will punish users automatically for using too many emotes"
                            ></VSwitch>

                            <template v-if="form.autoMaxEmotesEnabled">
                                <VAutocomplete
                                    v-model="form.autoMaxEmotesCommand"
                                    :items="punishableCommands"
                                    label="Command to use for response"
                                    :disabled="form.processing"
                                    class="mb-4"
                                    hint="The command that will be used for response and calculating timeout length"
                                    persistent-hint
                                    item-value="id"
                                    item-title="command"
                                    clearable
                                    :error-messages="
                                        form.errors.autoMaxEmotesCommand
                                    "
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

                                <VLabel>Max emotes</VLabel>
                                <VSlider
                                    v-model="form.autoMaxEmotes"
                                    class="mx-0 mb-4"
                                    min="0"
                                    max="30"
                                    step="1"
                                    :disabled="form.processing"
                                    :error-messages="form.errors.autoMaxEmotes"
                                    color="primary"
                                    messages="The max number of emotes allowed. If one message has more emotes than this, they will get punished."
                                    thumb-label="always"
                                    thumb-color="primary"
                                >
                                </VSlider>
                            </template>
                        </template>
                    </VExpansionPanel>
                </VExpansionPanels>
            </section>
        </div>

        <div class="buttons my-4">
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
.sections {
    display: grid;
    gap: 1rem;
    max-width: 100rem;
}

@media screen and (min-width: 768px) {
    .sections {
        display: grid;
        gap: 2rem;
        grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
        grid-template-areas:
            "general auto-punishment"
            "general auto-punishment";
    }

    .general {
        grid-area: general;
    }

    .auto-punishment {
        grid-area: auto-punishment;
    }
}

.buttons {
    display: flex;
    gap: 1rem;
}
</style>
