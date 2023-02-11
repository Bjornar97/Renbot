<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import type { Command } from "@/types/Command";
import { router, useForm } from "@inertiajs/vue3";
import { mdiAlphaEBox, mdiCancel, mdiContentSave } from "@mdi/js";

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
});

const submit = () => {
    form.put(route("commands.update", { command: props.command.id }));
};

const cancel = () => {
    form.reset();
    router.get(route("commands.index"));
};
</script>

<template>
    <div class="pa-2 pa-md-4">
        <VForm>
            <div>
                <header class="header">
                    <h1 class="mb-4">Create new command</h1>

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

<style>
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
