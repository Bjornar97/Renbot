<script setup lang="ts">
import { AutoPost } from "@/types/AutoPost";
import { useForm } from "@inertiajs/vue3";
import { mdiContentSave } from "@mdi/js";
import { computed } from "vue";

const props = defineProps<{
    autoPost: AutoPost;
}>();

const form = useForm({
    title: props.autoPost.title,
    interval: props.autoPost.interval,
    interval_type: props.autoPost.interval_type,
    min_posts_between: props.autoPost.min_posts_between,
});

const intervalOptions = computed(() => {
    return [
        // {
        //     title: `Second${form.interval > 1 ? "s" : ""}`,
        //     value: `seconds`,
        // },
        {
            title: `Minute${form.interval > 1 ? "s" : ""}`,
            value: `minutes`,
        },
        {
            title: `Hour${form.interval > 1 ? "s" : ""}`,
            value: `hours`,
        },
        {
            title: `Day${form.interval > 1 ? "s" : ""}`,
            value: `days`,
        },
    ];
});

const submit = () => {
    form.put(route("auto-posts.update", { auto_post: props.autoPost.id }));
};
</script>

<template>
    <div>
        <VTextField
            label="Title"
            v-model="form.title"
            :error-messages="form.errors.title"
        ></VTextField>

        <VLabel>Interval</VLabel>
        <div class="d-flex interval mb-6">
            <VTextField
                class="interval-number-field"
                v-model="form.interval"
                type="number"
                :hint="`Posts a command from the queue in chat every ${form.interval} ${form.interval_type}`"
                :error-messages="form.errors.interval"
            >
            </VTextField>

            <VSelect
                :items="intervalOptions"
                v-model="form.interval_type"
                :error-messages="form.errors.interval_type"
            ></VSelect>
        </div>

        <VTextField
            v-model="form.min_posts_between"
            :error-messages="form.errors.min_posts_between"
            label="Minimum number of chats between each auto post"
            type="number"
        ></VTextField>

        <VBtn color="success" @click="submit" :prepend-icon="mdiContentSave"
            >Save</VBtn
        >
    </div>
</template>
