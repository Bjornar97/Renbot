<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import { mdiAlphaEBox, mdiCancel, mdiContentSave } from "@mdi/js";
import { computed } from "vue";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    type: "regular" | "punishable" | "special";
    actions: { action: string; title: string }[];
}>();

console.log(props.actions);

const form = useForm("CreateCommand", {
    command: "",
    response: "",
    enabled: true,
    cooldown: 0,
    global_cooldown: 0,
    usable_by: "moderators",
    type: props.type,
    severity: 5,
    punish_reason: "",
    action: null,
});

const submit = () => {
    if (props.type === "punishable") {
        form.post(route("punishable-commands.store"));
        return;
    }

    if (props.type === "special") {
        form.post(route("special-commands.store"));
        return;
    }

    form.post(route("commands.store"));
};

const cancel = () => {
    form.reset();

    if (props.type === "punishable") {
        router.get(route("punishable-commands.index"));
        return;
    }

    if (props.type === "special") {
        router.get(route("special-commands.index"));
        return;
    }

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
                    <h1 class="mb-4">Create new {{ form.type }} command</h1>

                    <VSwitch
                        color="red"
                        v-model="form.enabled"
                        label="Enabled"
                        class="mb-4"
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
                            :hint="
                                form.type === 'special'
                                    ? 'The response might get overriden by the action'
                                    : ''
                            "
                        ></VTextarea>
                    </div>

                    <div class="settings">
                        <VBtnToggle
                            v-model="form.usable_by"
                            divided
                            class="mb-4"
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

                        <p class="text-red mb-6" v-if="form.errors.usable_by">
                            {{ form.errors.usable_by }}
                        </p>

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

                <div v-if="form.type === 'special'">
                    <VAutocomplete
                        v-model="form.action"
                        class="mb-4"
                        :items="actions"
                        item-title="title"
                        item-value="action"
                        label="Action"
                        hint="The action to run when this command is excecuted"
                        persistent-hint
                        :error-messages="form.errors.action"
                    ></VAutocomplete>
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
