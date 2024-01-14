<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import { mdiCancel, mdiContentSave } from "@mdi/js";
import CommandResponse from "./Partials/CommandResponse.vue";
import type { CommandType } from "@/types/CommandType";
import UsableBy from "./Partials/UsableBy.vue";
import type { UsableBy as UsableByType } from "@/types/UsableBy";
import Cooldowns from "./Partials/Cooldowns.vue";
import Punishable from "./Partials/Punishable.vue";
import type { CommandAction } from "@/types/CommandAction";
import SpecialAction from "./Partials/SpecialAction.vue";
import AutoPost from "./Partials/AutoPost.vue";
import type { AutoPost as AutoPostType } from "@/types/AutoPost";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    type: CommandType;
    actions?: CommandAction[];
    autoPosts: AutoPostType[];
}>();

const form = useForm("CreateCommand", {
    command: "",
    aliases: [],
    response: "",
    enabled: true,
    cooldown: 0,
    global_cooldown: 0,
    usable_by: "moderators" as UsableByType,
    type: props.type,
    severity: 5,
    punish_reason: "",
    action: undefined,
    prepend_sender: false,
    auto_post_enabled: false,
    auto_post_id: null,
    auto_post: {
        interval: 1,
        interval_type: "hours",
        min_posts_between: 100,
    },
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
</script>

<template>
    <div class="pa-2 pa-md-4">
        <VForm class="command-form">
            <header class="header">
                <h1 class="text-md-h2">Create new {{ form.type }} command</h1>

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
                        v-model:aliases="form.aliases"
                        v-model:response="form.response"
                        v-model:prepend-sender="form.prepend_sender"
                        :errors="form.errors"
                        :type="type"
                    ></CommandResponse>
                </VSheet>
            </div>

            <div class="permissions">
                <VSheet class="pa-4 pa-md-6" :rounded="100">
                    <h2 class="text-overline mb-4">Permissions</h2>

                    <UsableBy
                        :type="type"
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
