<script setup lang="ts">
import type { AutoPost } from "@/types/AutoPost";
import { useForm } from "@inertiajs/vue3";
import { mdiPlus } from "@mdi/js";
import { watch } from "vue";
import { ref } from "vue";
import { computed } from "vue";

const autoPostEnabled = defineModel<boolean>("autoPostEnabled", {
    default: false,
});

const autoPostId = defineModel<number | null>("autoPostId");

const autoPostInterval = defineModel<number>("autoPostInterval", {
    default: 1,
});
const autoPostIntervalType = defineModel<string>("autoPostIntervalType", {
    default: "hours",
});
const autoPostMinPostsBetween = defineModel<number>("autoPostMinPostsBetween", {
    default: 100,
});

const props = defineProps<{
    errors: {
        auto_post_enabled?: string;
        auto_post_id?: string;
        "auto_post.interval"?: string;
        "auto_post.interval_type"?: string;
        "auto_post.min_posts_between"?: string;
    };
    autoPosts: AutoPost[];
}>();

const intervalOptions = computed(() => {
    return [
        // {
        //     title: `Second${autoPostInterval.value > 1 ? "s" : ""}`,
        //     value: `seconds`,
        // },
        {
            title: `Minute${autoPostInterval.value > 1 ? "s" : ""}`,
            value: `minutes`,
        },
        {
            title: `Hour${autoPostInterval.value > 1 ? "s" : ""}`,
            value: `hours`,
        },
        {
            title: `Day${autoPostInterval.value > 1 ? "s" : ""}`,
            value: `days`,
        },
    ];
});

const autoPostModalOpen = ref(false);

const autoPostForm = useForm("storeAutoPost", {
    title: "",
    interval: 10,
    interval_type: "minutes",
    min_posts_between: 100,
});

const addAutoPost = () => {
    autoPostForm.post(route("auto-posts.store"), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            autoPostModalOpen.value = false;

            autoPostId.value =
                props.autoPosts.find(
                    (autoPost: AutoPost) => autoPost.title == autoPostForm.title
                )?.id ?? null;
        },
    });
};

const selectedAutoPost = computed(() => {
    return props.autoPosts.find((autoPost) => autoPost.id === autoPostId.value);
});

watch(
    () => selectedAutoPost.value?.id,
    (value, oldValue) => {
        if (!value) {
            return;
        }

        if (value == oldValue) {
            return;
        }

        if (!selectedAutoPost.value) {
            return;
        }

        autoPostInterval.value = selectedAutoPost.value?.interval;
        autoPostIntervalType.value = selectedAutoPost.value?.interval_type;
        autoPostMinPostsBetween.value =
            selectedAutoPost.value?.min_posts_between;
    }
);
</script>

<template>
    <div>
        <VSwitch
            color="primary"
            v-model="autoPostEnabled"
            label="Enable auto posting"
        ></VSwitch>

        <div v-if="autoPostEnabled">
            <div class="d-sm-flex auto-post-select mb-4">
                <VAutocomplete
                    label="Select queue"
                    v-model="autoPostId"
                    :items="autoPosts"
                    item-value="id"
                    clearable
                    :error-messages="errors.auto_post_id"
                ></VAutocomplete>

                <VBtn
                    color="info"
                    class="mt-2"
                    @click="autoPostModalOpen = true"
                    :prepend-icon="mdiPlus"
                    >Add new</VBtn
                >
            </div>

            <p class="mb-6">
                If there are multiple commands in the same queue, only one of
                those commands will be posted per interval. Settings below
                affect the whole queue, not just this command.
            </p>

            <VLabel>Interval</VLabel>
            <div class="d-flex interval mb-6">
                <VTextField
                    class="interval-number-field"
                    v-model="autoPostInterval"
                    type="number"
                    :hint="`Posts a command from the queue in chat every ${autoPostInterval} ${autoPostIntervalType}`"
                    :error-messages="errors['auto_post.interval']"
                >
                </VTextField>

                <VSelect
                    :items="intervalOptions"
                    v-model="autoPostIntervalType"
                    :error-messages="errors['auto_post.interval_type']"
                ></VSelect>
            </div>

            <VTextField
                v-model="autoPostMinPostsBetween"
                :error-messages="errors['auto_post.min_posts_between']"
                label="Minimum number of chats between each auto post"
            ></VTextField>
        </div>

        <VDialog v-model="autoPostModalOpen" max-width="30rem">
            <VCard>
                <VCardTitle>Add new queue</VCardTitle>
                <VCardText>
                    <VTextField
                        class="mb-4"
                        v-model="autoPostForm.title"
                        label="Title"
                        :error-messages="autoPostForm.errors.title"
                    ></VTextField>

                    <VLabel>Interval</VLabel>
                    <div class="d-flex interval mb-4">
                        <VTextField
                            v-model="autoPostForm.interval"
                            type="number"
                            :hint="`Posts the command in chat every ${autoPostInterval} ${autoPostIntervalType}`"
                            :error-messages="autoPostForm.errors.interval"
                        >
                        </VTextField>
                        <VSelect
                            :items="intervalOptions"
                            v-model="autoPostForm.interval_type"
                            :error-messages="autoPostForm.errors.interval_type"
                        ></VSelect>
                    </div>

                    <VTextField
                        v-model="autoPostForm.min_posts_between"
                        :error-messages="autoPostForm.errors.min_posts_between"
                        type="number"
                        label="Minimum number of chats between each auto post"
                    ></VTextField>
                </VCardText>

                <VCardActions>
                    <VBtn
                        color="success"
                        @click="addAutoPost"
                        :loading="autoPostForm.processing"
                        >Save</VBtn
                    >
                    <VBtn color="grey" @click="autoPostModalOpen = false"
                        >Cancel</VBtn
                    >
                </VCardActions>
            </VCard>
        </VDialog>
    </div>
</template>

<style scoped>
.interval {
    gap: 1rem;
}

.auto-post-select {
    gap: 1rem;
}

.interval-number-field {
    width: 3rem;
}
</style>
