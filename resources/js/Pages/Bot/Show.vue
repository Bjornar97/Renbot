<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { ref } from "vue";
import { router } from "@inertiajs/core";
import route from "ziggy-js";
import { BotStatus as BotStatusType } from "@/types/BotStatus";
import BotStatus from "@/Components/Bot/BotStatus.vue";
import { mdiRestart, mdiRobotExcited, mdiRobotOff } from "@mdi/js";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    status: BotStatusType;
}>();

const actionsDisabled = ref(false);

const restartLoading = ref(false);

const restart = () => {
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

const startLoading = ref(false);

const start = () => {
    startLoading.value = true;
    actionsDisabled.value = true;

    router.post(
        route("bot.start"),
        {},
        {
            onFinish: () => {
                startLoading.value = false;
                actionsDisabled.value = false;
            },
        }
    );
};

const stopLoading = ref(false);

const stop = () => {
    stopLoading.value = true;
    actionsDisabled.value = true;

    router.post(
        route("bot.stop"),
        {},
        {
            onFinish: () => {
                stopLoading.value = false;
                actionsDisabled.value = false;
            },
        }
    );
};
</script>

<template>
    <div class="pa-2 pa-md-4">
        <h1 class="text-h2 font-weight-bold mb-4">
            <span class="mr-4">Bot</span>
            <BotStatus :status="status" size="x-large"></BotStatus>
        </h1>

        <div class="bot-action mb-8">
            <VBtn
                v-if="status === BotStatusType.STOPPED"
                color="success"
                :prepend-icon="mdiRobotExcited"
                @click="start"
                :loading="startLoading"
                :disabled="actionsDisabled"
            >
                Start bot
            </VBtn>
            <VBtn
                v-if="status === BotStatusType.RUNNING"
                color="error"
                :prepend-icon="mdiRobotOff"
                @click="stop"
                :loading="stopLoading"
                :disabled="actionsDisabled"
            >
                Stop bot
            </VBtn>
        </div>

        <div v-if="status !== BotStatusType.STOPPED">
            <p class="mb-2">
                If there is a problem with the bot, a restart can often solve
                it.
            </p>

            <VBtn
                color="primary-darken-2"
                @click="restart"
                :loading="restartLoading"
                :disabled="actionsDisabled"
                :prepend-icon="mdiRestart"
                >Restart the bot</VBtn
            >
        </div>
    </div>
</template>

<style scoped></style>
