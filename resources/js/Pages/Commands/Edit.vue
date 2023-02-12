<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import type { Command } from "@/types/Command";
import { router, useForm } from "@inertiajs/vue3";
import { mdiAlphaEBox, mdiCancel, mdiContentSave } from "@mdi/js";
import { computed } from "vue";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    command: Command;
}>();

const form = useForm("CreateCommand", {
    command: props.command.command,
    response: props.command.response,
    enabled: props.command.enabled,
    cooldown: props.command.cooldown,
    global_cooldown: props.command.global_cooldown,
    usable_by: props.command.usable_by,
    severity: props.command.severity,
    type: props.command.type,
    punish_reason: props.command.punish_reason,
});

const submit = () => {
    form.put(route("commands.update", { command: props.command.id }), {
        onSuccess: () => {
            if (form.type === "punishable") {
                router.get(route("punishable-commands.index"));
                return;
            }

            if (form.type === "special") {
                // TODO
                return;
            }

            router.get(route("commands.index"));
        },
    });
};

const cancel = () => {
    form.reset();
    router.get(route("commands.index"));
};

const severityColor = computed(() => {
    let color = "success";
    if (form.severity > 4) {
        color = "warning";
    }

    if (form.severity > 7) {
        color = "error";
    }

    return color;
});
</script>

<template>
    <div class="pa-2 pa-md-4">
        <VForm>
            <div>
                <header class="header">
                    <h1 class="mb-4">
                        Edit {{ command.type }} command !{{ command.command }}
                    </h1>

                    <VSwitch
                        color="red"
                        v-model="form.enabled"
                        label="Enabled"
                        :error-messages="form.errors.enabled"
                    ></VSwitch>
                </header>

                <div class="row">
                    <div class="info">
                        <VTextField
                            v-model="form.command"
                            label="Command"
                            class="mb-4"
                            :error-messages="form.errors.command"
                        >
                            <template #prepend-inner>!</template>
                        </VTextField>

                        <VTextarea
                            v-model="form.response"
                            label="Response"
                            :error-messages="form.errors.response"
                            class="mb-4"
                        ></VTextarea>
                    </div>

                    <div class="settings">
                        <VBtnToggle
                            v-model="form.usable_by"
                            divided
                            class="mb-8"
                            :disabled="form.type === 'punishable'"
                        >
                            <VBtn color="#01AD02" value="moderators">
                                <template #prepend>
                                    <img
                                        src="../../../images/icons/moderator.png"
                                        alt="Moderator icon"
                                    />
                                </template>
                                Moderators only
                            </VBtn>

                            <VBtn color="purple-darken-4" value="subscribers">
                                <template #prepend>
                                    <img
                                        src="../../../images/icons/subscriber.png"
                                        alt="Moderator icon"
                                    />
                                </template>
                                Subscribers
                            </VBtn>

                            <VBtn
                                color="red-darken-4"
                                value="everyone"
                                :prepend-icon="mdiAlphaEBox"
                            >
                                Everyone
                            </VBtn>
                        </VBtnToggle>

                        <VTextField
                            class="mb-4"
                            v-if="form.usable_by !== 'moderators'"
                            type="number"
                            v-model="form.cooldown"
                            label="Cooldown"
                            hint="If the same person runs this command twice within this duration, the second will not be allowed. Does not apply to moderators."
                            :error-messages="form.errors.cooldown"
                        ></VTextField>

                        <VTextField
                            class="mb-4"
                            v-if="form.usable_by !== 'moderators'"
                            type="number"
                            v-model="form.global_cooldown"
                            label="Global cooldown"
                            hint="If any person runs this command within this duration after previous time it was run by any person, it will not be allowed. Does not apply to moderators."
                            :error-messages="form.errors.global_cooldown"
                        ></VTextField>
                    </div>
                </div>

                <div v-if="form.type === 'punishable'">
                    <VSlider
                        class="mb-8"
                        v-model="form.severity"
                        :color="severityColor"
                        :show-ticks="true"
                        :step="1"
                        min="1"
                        max="10"
                        label="Punish severity"
                        :error-messages="form.errors.severity"
                        messages="How hard should the chatter be punished. 1 will timeout 10 seconds first time, 5 will timeout 120 seconds first time, 10 will insta-ban."
                        thumb-label="always"
                    ></VSlider>

                    <VTextField
                        v-model="form.punish_reason"
                        :error-messages="form.errors.punish_reason"
                        label="Punish reason"
                    ></VTextField>
                </div>

                <div class="buttons">
                    <VBtn
                        color="green"
                        @click="submit"
                        :prepend-icon="mdiContentSave"
                        :loading="form.processing"
                    >
                        Save
                    </VBtn>
                    <VBtn @click="cancel" color="gray" :prepend-icon="mdiCancel"
                        >Cancel</VBtn
                    >
                </div>
            </div>
        </VForm>
    </div>
</template>

<style scoped>
.row {
    display: grid;
}

@media screen and (min-width: 1280px) {
    .row {
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
}

.buttons {
    display: flex;
    gap: 1rem;
}

.header {
    display: grid;
    grid-template-columns: 1fr max-content;
}
</style>
