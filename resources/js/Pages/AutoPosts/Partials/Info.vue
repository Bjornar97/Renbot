<script setup lang="ts">
import { websocket } from "@/echo";
import { AutoPost as AutoPostType } from "@/types/AutoPost";
import { computed, ref } from "vue";
import dayjs from "dayjs";
import { reactive } from "vue";
import { watch } from "vue";
import { mdiPencil, mdiTrashCan } from "@mdi/js";
import { Link, router } from "@inertiajs/vue3";
import Edit from "./Edit.vue";
import { route } from "ziggy-js";

const props = defineProps<{
    autoPost: AutoPostType;
}>();

const autoPost = reactive(props.autoPost);

const updateAutoPost = (model: AutoPostType) => {
    autoPost.chats_to_next = model.chats_to_next;
    autoPost.title = model.title;
    autoPost.enabled = model.enabled;
    autoPost.interval = model.interval;
    autoPost.interval_type = model.interval_type;
    autoPost.last_command_id = model.last_command_id;
    autoPost.min_posts_between = model.min_posts_between;
    autoPost.last_post = model.last_post;
};

watch(
    () => props.autoPost,
    (newValue) => {
        updateAutoPost(newValue);
    }
);

websocket
    .private(`App.Models.AutoPost.${autoPost.id}`)
    .listen(
        ".AutoPostUpdated",
        ({
            model,
            afterCommit,
        }: {
            model: AutoPostType;
            afterCommit: boolean;
        }) => {
            updateAutoPost(model);
        }
    );

const lastPosted = ref(dayjs(autoPost.last_post).fromNow());

setInterval(() => {
    lastPosted.value = dayjs(autoPost.last_post).fromNow();
}, 1000);

const getNextPosts = () => {
    if (!autoPost.commands) {
        return [];
    }

    const lastCommandId = autoPost.last_command_id;
    const commands = autoPost.commands.filter(
        (command) =>
            command.id > lastCommandId &&
            command.enabled === true &&
            command.auto_post_enabled === true
    );

    commands?.push(
        ...autoPost.commands.filter(
            (command) =>
                command.id <= lastCommandId &&
                command.enabled === true &&
                command.auto_post_enabled === true
        )
    );

    const nextPostTime = dayjs(autoPost.last_post).add(
        autoPost.interval,
        autoPost.interval_type
    );

    const posts = commands.map((command, index) => {
        const chatsToNext =
            Math.max(autoPost.chats_to_next, 0) +
            index * autoPost.min_posts_between;

        let timeToPost = dayjs(autoPost.last_post).add(
            autoPost.interval * (index + 1),
            autoPost.interval_type
        );

        if (
            index > 0 &&
            nextPostTime.diff(dayjs(), autoPost.interval_type) < 0
        ) {
            timeToPost = dayjs().add(
                autoPost.interval * index,
                autoPost.interval_type
            );
        }

        let timeToPostText = timeToPost.fromNow();

        if (timeToPost.isBefore()) {
            if (chatsToNext > 0) {
                timeToPostText = `in ${chatsToNext} chat${
                    chatsToNext > 1 ? "s" : ""
                }`;
            } else {
                timeToPostText = "After next chat";
            }
        }

        return {
            id: command.id,
            command: command.command,
            response: command.response,
            chatsToNext: chatsToNext,
            timeToPost: timeToPost,
            timeToPostText: timeToPostText,
        };
    });

    return posts;
};

const nextPosts = ref(getNextPosts());

watch(autoPost, (value) => {
    nextPosts.value = getNextPosts();
});

setInterval(() => {
    nextPosts.value = getNextPosts();
}, 1000);

const deleteOpen = ref(false);

const deleteQueue = () => {
    router.delete(route("auto-posts.destroy", { auto_post: autoPost.id }), {
        preserveScroll: true,
    });
};

const enabled = computed({
    get: (): boolean => autoPost.enabled,
    set: async (v: boolean) => {
        autoPost.enabled = v;

        router.patch(
            route("auto-posts.update", { auto_post: autoPost.id }),
            {
                enabled: v,
            },
            {
                preserveScroll: true,
            }
        );
    },
});
</script>

<template>
    <div>
        <div class="header">
            <h2>{{ autoPost.title }}</h2>
            <VBtn
                :prepend-icon="mdiTrashCan"
                color="error"
                @click="deleteOpen = true"
                >Delete</VBtn
            >
        </div>

        <p>Last posted {{ lastPosted }}</p>

        <VSwitch
            hide-details
            label="Enabled"
            v-model="enabled"
            color="primary"
        ></VSwitch>

        <VExpansionPanels class="my-4" color="primary-darken-2">
            <VExpansionPanel title="Edit">
                <template #text>
                    <Edit class="my-2" :auto-post="autoPost"></Edit>
                </template>
            </VExpansionPanel>
        </VExpansionPanels>

        <h3 class="mt-4 mb-2">Next posts</h3>

        <TransitionGroup tag="ul" name="fade" class="container">
            <li
                class="command-info"
                v-for="(command, index) in nextPosts"
                :key="command.id"
            >
                <div class="edit-button">
                    <Link
                        :href="route('commands.edit', { command: command.id })"
                    >
                        <VBtn
                            size="small"
                            variant="text"
                            :icon="mdiPencil"
                        ></VBtn>
                    </Link>
                </div>
                <div>
                    <p>!{{ command.command }}</p>
                    <p class="text-caption">{{ command.response }}</p>
                </div>

                <div class="chips">
                    <VChip color="green">
                        <template v-if="enabled">
                            {{
                                command.timeToPostText.charAt(0).toUpperCase() +
                                command.timeToPostText.slice(1)
                            }}
                        </template>
                        <template v-else> ∞ </template>
                    </VChip>
                    <VChip
                        >Chats needed:
                        {{ enabled ? command.chatsToNext : "∞" }}</VChip
                    >
                </div>

                <VDivider
                    v-if="index != nextPosts.length - 1"
                    class="my-4 divider"
                ></VDivider>
            </li>
        </TransitionGroup>

        <VDialog v-model="deleteOpen" max-width="35rem">
            <VCard>
                <VCardTitle>Are you sure?</VCardTitle>
                <VCardText
                    >Are you sure you want to delete this queue? All commands in
                    the queue will stop auto posting.</VCardText
                >

                <VCardActions>
                    <VBtn @click="deleteOpen = false" color="grey">Cancel</VBtn>
                    <VBtn
                        color="error"
                        :prepend-icon="mdiTrashCan"
                        @click="deleteQueue"
                        >Delete</VBtn
                    >
                </VCardActions>
            </VCard>
        </VDialog>
    </div>
</template>

<style scoped>
.command-info {
    display: grid;
    grid-template-columns: max-content 1fr max-content;
    height: 100px;
    gap: 0.5rem;
    overflow: hidden;
}

.command-info:hover {
    background-color: #292929;
}

.header {
    display: flex;
    justify-content: space-between;
}

.command-info .text-caption {
    height: 50px;
    color: #ccc;
    overflow: hidden;
    position: relative;
    word-break: break-all;
}

.chips {
    display: flex;
    flex-direction: column;
    align-self: center;
}

.divider {
    grid-column: 1/4;
}

.container {
    position: relative;
    padding: 0;
}

/* 1. declare transition */
.fade-move,
.fade-enter-active,
.fade-leave-active {
    transition: all 0.5s cubic-bezier(0.55, 0, 0.1, 1);
}

/* 2. declare enter from and leave to state */
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scaleY(0.01) translate(30px, 0);
}

/* 3. ensure leaving items are taken out of layout flow so that moving
      animations can be calculated correctly. */
.fade-leave-active {
    position: absolute;
}
</style>
