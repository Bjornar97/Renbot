<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import type { Command } from "@/types/Command";
import { router, useForm } from "@inertiajs/vue3";
import { mdiCancel, mdiContentSave } from "@mdi/js";
import { ref } from "vue";
import CommandResponse from "./Partials/CommandResponse.vue";
import UsableBy from "./Partials/UsableBy.vue";
import Cooldowns from "./Partials/Cooldowns.vue";
import Punishable from "./Partials/Punishable.vue";
import SpecialAction from "./Partials/SpecialAction.vue";
import AutoPost from "./Partials/AutoPost.vue";
import type { AutoPost as AutoPostType } from "@/types/AutoPost";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    command: Command;
    actions: { action: string; title: string }[];
    autoPosts: AutoPostType[];
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
    action: props.command.action,
    prepend_sender: props.command.prepend_sender,
    auto_post_enabled: props.command.auto_post_enabled,
    auto_post_id: props.command.auto_post_id,
    auto_post: {
        interval: props.command.auto_post?.interval ?? 10,
        interval_type: props.command.auto_post?.interval_type ?? "hours",
        min_posts_between: props.command.auto_post?.min_posts_between ?? 100,
    },
});

const submit = () => {
    form.put(route("commands.update", { command: props.command.id }), {
        onSuccess: () => {
            if (form.type === "punishable") {
                router.get(route("punishable-commands.index"));
                return;
            }

            if (form.type === "special") {
                router.get(route("special-commands.index"));
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
</script>

<template>
    <div class="pa-2 pa-md-4">
        <VForm class="command-form">
            <header class="header">
                <h1 class="text-md-h2">
                    Edit {{ form.type }} command !{{ props.command.command }}
                </h1>

                <VSwitch
                    color="red"
                    v-model="form.enabled"
                    label="Enabled"
                    :error-messages="form.errors.enabled"
                    hide-details
                ></VSwitch>
            </header>

            <div class="command-response">
                <VSheet class="pa-4 pa-md-6" rounded>
                    <h2 class="text-overline mb-4">Command and reponse</h2>

                    <CommandResponse
                        v-model:command="form.command"
                        v-model:response="form.response"
                        v-model:prepend-sender="form.prepend_sender"
                        :errors="form.errors"
                        :type="form.type"
                    ></CommandResponse>
                </VSheet>
            </div>

            <div class="permissions">
                <VSheet class="pa-4 pa-md-6" rounded>
                    <h2 class="text-overline mb-4">Permissions</h2>

                    <UsableBy
                        :type="form.type"
                        v-model:usable-by="form.usable_by"
                        :errors="form.errors"
                    ></UsableBy>

                    <Cooldowns
                        v-model:cooldown="form.cooldown"
                        v-model:global-cooldown="form.global_cooldown"
                        :usable-by="form.usable_by"
                        :errors="form.errors"
                    ></Cooldowns>
                </VSheet>
            </div>

            <div class="type-specific">
                <VSheet
                    class="pa-4 pa-md-6"
                    rounded
                    v-if="form.type === 'regular'"
                >
                    <h2 class="text-overline mb-4">Auto post</h2>

                    <AutoPost
                        v-model:auto-post-id="form.auto_post_id"
                        v-model:auto-post-enabled="form.auto_post_enabled"
                        v-model:auto-post-interval="form.auto_post.interval"
                        v-model:auto-post-interval-type="
                            form.auto_post.interval_type
                        "
                        v-model:auto-post-min-posts-between="
                            form.auto_post.min_posts_between
                        "
                        :errors="form.errors"
                        :auto-posts="autoPosts"
                    ></AutoPost>
                </VSheet>

                <VSheet
                    class="pa-4 pa-md-6"
                    rounded
                    v-if="form.type === 'punishable'"
                >
                    <h2 class="text-overline mb-4">Punishment</h2>

                    <Punishable
                        v-model:severity="form.severity"
                        v-model:punish-reason="form.punish_reason"
                        :errors="form.errors"
                    ></Punishable>
                </VSheet>

                <VSheet
                    class="pa-4 pa-md-6"
                    rounded
                    v-if="form.type === 'special' && actions"
                >
                    <h2 class="text-overline mb-4">Action</h2>

                    <SpecialAction
                        v-model:action="form.action"
                        :actions="actions"
                        :errors="form.errors"
                    ></SpecialAction>
                </VSheet>
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
        </VForm>
    </div>
</template>

<style scoped>
.command-form {
    display: grid;
    gap: 2rem;
}

@media screen and (min-width: 1280px) {
    .command-form {
        grid-template-columns: 1fr 1fr;
        grid-template-areas:
            "header header"
            "command-response permissions"
            "type-specific ."
            "buttons .";
    }
    .header {
        grid-area: header;
        display: grid;
        grid-template-columns: 1fr max-content;
    }

    .command-response {
        grid-area: command-response;
    }

    .permissions {
        grid-area: permissions;
    }

    .type-specific {
        grid-area: type-specific;
    }

    .buttons {
        grid-area: buttons;
    }
}

.buttons {
    display: flex;
    gap: 1rem;
}
</style>
